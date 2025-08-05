<?php

namespace App\Http\Controllers;

use App\Models\Konsultasi;
use App\Models\KonsultasiKomentar;
use App\Models\Karyawan;
use App\Services\NotificationService;
use App\Jobs\SendKonsultasiNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KonsultasiController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of konsultasi
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Konsultasi::with(['karyawan', 'komentar']);
        
        // Filter berdasarkan role dan akses
        if ($this->isAdmin($user)) {
            $adminLevel = $this->getAdminLevel($user);
            $query = $this->filterByAdminLevel($query, $adminLevel);
        } else {
            $query->where('N_NIK', $user->nik);
        }
        
        // Apply filters
        $this->applyFilters($query, $request);
        
        $konsultasi = $query->orderBy('CREATED_AT', 'desc')->paginate(10);
        
        return view('konsultasi.index', compact('konsultasi'));
    }
    
    /**
     * Show the form for creating a new konsultasi
     */
    public function create()
    {
        $user = Auth::user();
        $karyawan = Karyawan::where('N_NIK', $user->nik)->first();
        
        return view('konsultasi.create', [
            'karyawan' => $karyawan,
            'availableTargets' => $this->getAvailableTargets(),
            'kategoriAdvokasi' => $this->getKategoriAdvokasi(),
            'dpwOptions' => $this->getDropdownOptions('DPW'),
            'dpdOptions' => $this->getDropdownOptions('DPD')
        ]);
    }
    
    /**
     * Store a newly created konsultasi
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis' => 'required|in:ADVOKASI,ASPIRASI',
            'kategori_advokasi' => 'nullable|string|max:100',
            'tujuan' => 'required|string|max:50',
            'tujuan_spesifik' => 'nullable|string|max:100',
            'judul' => 'required|string|max:200',
            'deskripsi' => 'required|string|max:2000'
        ]);
        
        $user = Auth::user();
        
        try {
            $konsultasi = DB::transaction(function () use ($validated, $user) {
                return $this->createKonsultasi($user, $validated);
            });
            
            // Send notifications (non-blocking)
            $this->sendNotifications($konsultasi, 'new');
            
            return redirect()->route('konsultasi.index')
                           ->with('success', ucfirst($validated['jenis']) . ' berhasil diajukan dan akan ditindaklanjuti.');
        } catch (\Exception $e) {
            Log::error('Error creating konsultasi', [
                'user_nik' => $user->nik,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Terjadi kesalahan saat mengajukan ' . strtolower($validated['jenis']) . '. Silakan coba lagi.');
        }
    }
    
    /**
     * Display the specified konsultasi
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $konsultasi = Konsultasi::with(['karyawan', 'komentar.karyawan', 'komentar.user'])->findOrFail($id);
        
        if (!$this->canAccessKonsultasi($user, $konsultasi)) {
            return redirect()->route('konsultasi.index')
                           ->with('error', 'Anda tidak memiliki akses untuk melihat konsultasi ini.');
        }
        
        return view('konsultasi.show', compact('konsultasi'));
    }
    
    /**
     * Add comment to konsultasi
     */
    public function comment(Request $request, $id)
    {
        $validated = $request->validate([
            'komentar' => 'required|string|max:1000'
        ]);
        
        $user = Auth::user();
        $konsultasi = Konsultasi::findOrFail($id);
        
        if (!$this->canCommentOnKonsultasi($user, $konsultasi)) {
            return redirect()->back()->with('error', 'Anda tidak dapat menambahkan komentar pada konsultasi ini.');
        }
        
        try {
            DB::transaction(function () use ($validated, $id, $user, $konsultasi) {
                $jenisKomentar = $this->isAdmin($user) ? 'ADMIN' : 'USER';
                $this->createKomentar($id, $user->nik, $validated['komentar'], $jenisKomentar);
                
                // Update status if admin responds
                if ($jenisKomentar === 'ADMIN' && $konsultasi->STATUS === 'OPEN') {
                    $konsultasi->update([
                        'STATUS' => 'IN_PROGRESS',
                        'UPDATED_BY' => $user->nik,
                        'UPDATED_AT' => now()
                    ]);
                }
            });
            
            // Send notifications (non-blocking)
            $this->sendNotifications($konsultasi, 'comment', [
                'is_admin_comment' => $this->isAdmin($user),
                'comment_by' => $this->getCommentByData($user)
            ]);
            
            return redirect()->back()->with('success', 'Komentar berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error adding comment', [
                'user_nik' => $user->nik,
                'konsultasi_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan komentar.');
        }
    }
    
    /**
     * Close konsultasi (admin only)
     */
    public function close($id)
    {
        if (!$this->isAdmin(Auth::user())) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya admin yang dapat menutup konsultasi.');
        }
        
        $user = Auth::user();
        
        try {
            $konsultasi = DB::transaction(function () use ($id, $user) {
                $konsultasi = Konsultasi::findOrFail($id);
                
                $konsultasi->update([
                    'STATUS' => 'CLOSED',
                    'CLOSED_BY' => $user->nik,
                    'CLOSED_AT' => now(),
                    'UPDATED_BY' => $user->nik,
                    'UPDATED_AT' => now()
                ]);
                
                $this->createKomentar($id, $user->nik, 'Konsultasi telah ditutup dan diselesaikan.', 'ADMIN');
                
                return $konsultasi;
            });
            
            // Send notifications (non-blocking)
            $this->sendNotifications($konsultasi, 'closed');
            
            return redirect()->back()->with('success', 'Konsultasi berhasil ditutup.');
        } catch (\Exception $e) {
            Log::error('Error closing konsultasi', [
                'user_nik' => $user->nik,
                'konsultasi_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menutup konsultasi.');
        }
    }
    
    /**
     * Escalate konsultasi to higher level (admin only)
     */
    public function escalate(Request $request, $id)
    {
        if (!$this->isAdmin(Auth::user())) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya admin yang dapat melakukan eskalasi.');
        }
        
        $validated = $request->validate([
            'escalate_to' => 'required|in:DPW,DPP',
            'escalation_note' => 'nullable|string|max:500'
        ]);
        
        $user = Auth::user();
        
        try {
            $konsultasi = DB::transaction(function () use ($validated, $id, $user) {
                $konsultasi = Konsultasi::findOrFail($id);
                
                // Validate escalation path
                $validTargets = $this->getValidEscalationTargets($konsultasi->TUJUAN);
                if (!array_key_exists($validated['escalate_to'], $validTargets)) {
                    throw new \Exception('Target eskalasi tidak valid.');
                }
                
                // Update konsultasi
                $konsultasi->update([
                    'TUJUAN' => $validated['escalate_to'],
                    'STATUS' => 'OPEN',
                    'UPDATED_BY' => $user->nik,
                    'UPDATED_AT' => now()
                ]);
                
                // Add escalation comment
                $note = $validated['escalation_note'] ? ": {$validated['escalation_note']}" : ': follow up!';
                $adminLevel = $this->getTargetLabel($validated['escalate_to']);
                $comment = "ESKALASI KE {$adminLevel}{$note}";
                
                $this->createKomentar($id, $user->nik, $comment, 'ADMIN');
                
                return $konsultasi;
            });
            
            // Send notifications (non-blocking)
            $this->sendNotifications($konsultasi, 'escalate', [
                'escalate_to' => $validated['escalate_to']
            ]);
            
            return redirect()->back()->with('success', 'Konsultasi berhasil dieskalasi ke level yang lebih tinggi.');
        } catch (\Exception $e) {
            Log::error('Error escalating konsultasi', [
                'user_nik' => $user->nik,
                'konsultasi_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat melakukan eskalasi.');
        }
    }

    // =====================================================
    // API ENDPOINTS
    // =====================================================

    public function getEscalationOptions(string $id)
    {
        $konsultasi = Konsultasi::findOrFail($id);
        return response()->json([
            'success' => true,
            'options' => $this->getValidEscalationTargets($konsultasi->TUJUAN)
        ]);
    }
    
    public function getStats(Request $request)
    {
        $user = Auth::user();
        $nik = $this->isAdmin($user) ? null : $user->nik;
        
        return response()->json([
            'success' => true,
            'stats' => Konsultasi::getStats($nik)
        ]);
    }

    // =====================================================
    // PRIVATE HELPER METHODS
    // =====================================================

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('JUDUL', 'LIKE', "%{$search}%")
                  ->orWhere('DESKRIPSI', 'LIKE', "%{$search}%")
                  ->orWhere('KATEGORI_ADVOKASI', 'LIKE', "%{$search}%");
            });
        }
        
        foreach (['status', 'jenis', 'tujuan'] as $filter) {
            if ($request->filled($filter)) {
                $query->where(strtoupper($filter), $request->$filter);
            }
        }
    }

    /**
     * Send notifications (consolidated method)
     */
    private function sendNotifications($konsultasi, string $type, array $context = [])
    {
        try {
            $konsultasi->load('karyawan');
            
            // Web notification
            switch ($type) {
                case 'new':
                    $this->notificationService->notifyNewKonsultasi($konsultasi);
                    break;
                case 'comment':
                    $this->notificationService->notifyNewComment(
                        $konsultasi, 
                        $context['comment_by'], 
                        $context['is_admin_comment']
                    );
                    break;
                case 'closed':
                    $this->notificationService->notifyKonsultasiClosed($konsultasi);
                    break;
                case 'escalate':
                    $this->notificationService->notifyEscalation($konsultasi, $context['escalate_to']);
                    break;
            }
            
            // Email notification (async)
            $this->sendEmailNotification($konsultasi, $type);
            
        } catch (\Exception $e) {
            Log::error("Failed to send {$type} notifications", [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get available targets
     */
    private function getAvailableTargets(): array
    {
        return [
            'advokasi' => [
                'DPD' => 'DPD (Dewan Pengurus Daerah)',
                'DPW' => 'DPW (Dewan Pengurus Wilayah)'
            ],
            'aspirasi' => [
                'DPD' => 'DPD (Dewan Pengurus Daerah)', 
                'DPW' => 'DPW (Dewan Pengurus Wilayah)',
                'GENERAL' => 'SEKAR Pusat (General/Umum)'
            ]
        ];
    }
    
    /**
     * Get kategori advokasi options
     */
    private function getKategoriAdvokasi(): array
    {
        return [
            'Pelecehan di Tempat Kerja',
            'Diskriminasi',
            'Pelanggaran Hak Karyawan',
            'Masalah Gaji/Tunjangan',
            'Lingkungan Kerja Tidak Aman',
            'Pelanggaran K3',
            'Bullying/Intimidasi',
            'Lainnya'
        ];
    }
    
    /**
     * Get dropdown options (DPW/DPD)
     */
    private function getDropdownOptions(string $type): array
    {
        return DB::table('t_sekar_pengurus')
            ->whereNotNull($type)
            ->where($type, '!=', '')
            ->distinct()
            ->pluck($type)
            ->sort()
            ->values()
            ->toArray();
    }
    
    /**
     * Get valid escalation targets
     */
    private function getValidEscalationTargets(string $currentTarget): array
    {
        return match ($currentTarget) {
            'DPD' => [
                'DPW' => 'DPW (Dewan Pengurus Wilayah)',
                'DPP' => 'DPP (Dewan Pengurus Pusat)'
            ],
            'DPW', 'GENERAL' => [
                'DPP' => 'DPP (Dewan Pengurus Pusat)'
            ],
            default => []
        };
    }

    /**
     * Get target label
     */
    private function getTargetLabel($target): string
    {
        return match ($target) {
            'DPD' => 'DPD (Dewan Pengurus Daerah)',
            'DPW' => 'DPW (Dewan Pengurus Wilayah)', 
            'DPP' => 'DPP (Dewan Pengurus Pusat)',
            'GENERAL' => 'Admin SEKAR',
            default => 'Admin SEKAR'
        };
    }
    
    /**
     * Send email notification
     */
    private function sendEmailNotification($konsultasi, string $actionType)
    {
        try {
            $recipientEmail = $this->getAdminEmailByTarget($konsultasi->TUJUAN);
            
            if ($recipientEmail) {
                SendKonsultasiNotificationJob::dispatch($konsultasi, $actionType, [$recipientEmail])
                                             ->onQueue('emails')
                                             ->delay(now()->addSeconds(5));
            }
        } catch (\Exception $e) {
            Log::error('Error dispatching email notification job', [
                'konsultasi_id' => $konsultasi->ID,
                'action_type' => $actionType,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get admin email by target level
     */
    private function getAdminEmailByTarget(string $target): ?string
    {
        return match ($target) {
            'DPD' => env('DPD_ADMIN_EMAIL'),
            'DPW' => env('DPW_ADMIN_EMAIL'),
            'DPP' => env('DPP_ADMIN_EMAIL'),
            'GENERAL' => env('GENERAL_ADMIN_EMAIL'),
            default => env('ADMIN_EMAIL', 'admin@sekar.telkom.co.id')
        };
    }

    // =====================================================
    // ACCESS CONTROL METHODS
    // =====================================================
    
    private function isAdmin($user): bool
    {
        return $user->pengurus?->role && 
               in_array($user->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']);
    }
    
    private function getAdminLevel($user): int
    {
        if (!$user->pengurus?->role) return 0;
        
        return match ($user->pengurus->role->NAME) {
            'ADM' => 4,
            'ADMIN_DPP' => 3,
            'ADMIN_DPW' => 2,
            'ADMIN_DPD' => 1,
            default => 0
        };
    }
    
    private function filterByAdminLevel($query, int $adminLevel)
    {
        $allowedTargets = match ($adminLevel) {
            4 => null, // ADM can see all
            3 => ['DPP', 'GENERAL'],
            2 => ['DPW', 'DPP', 'GENERAL'],
            1 => ['DPD', 'DPW', 'DPP', 'GENERAL'],
            default => []
        };
        
        if ($allowedTargets === null) return $query;
        if (empty($allowedTargets)) return $query->where('ID', 0);
        
        return $query->whereIn('TUJUAN', $allowedTargets);
    }
    
    private function canAccessKonsultasi($user, $konsultasi): bool
    {
        return $konsultasi->N_NIK === $user->nik || $this->isAdmin($user);
    }
    
    private function canCommentOnKonsultasi($user, $konsultasi): bool
    {
        if ($konsultasi->STATUS === 'CLOSED') return false;
        return $konsultasi->N_NIK === $user->nik || $this->isAdmin($user);
    }

    // =====================================================
    // DATABASE OPERATIONS
    // =====================================================
    
    private function createKonsultasi($user, array $validated): Konsultasi
    {
        $konsultasi = Konsultasi::create([
            'N_NIK' => $user->nik,
            'JENIS' => $validated['jenis'],
            'KATEGORI_ADVOKASI' => $validated['kategori_advokasi'] ?? null,
            'TUJUAN' => $validated['tujuan'],
            'TUJUAN_SPESIFIK' => $validated['tujuan_spesifik'] ?? null,
            'JUDUL' => $validated['judul'],
            'DESKRIPSI' => $validated['deskripsi'],
            'STATUS' => 'OPEN',
            'CREATED_BY' => $user->nik,
            'CREATED_AT' => now(),
            'UPDATED_BY' => $user->nik,
            'UPDATED_AT' => now()
        ]);

        return $konsultasi->load('karyawan');
    }
    
    private function createKomentar($konsultasiId, $nik, $komentar, $jenisKomentar): KonsultasiKomentar
    {
        return KonsultasiKomentar::create([
            'ID_KONSULTASI' => $konsultasiId,
            'N_NIK' => $nik,
            'KOMENTAR' => $komentar,
            'PENGIRIM_ROLE' => $jenisKomentar,
            'CREATED_BY' => $nik,
            'CREATED_AT' => now()
        ]);
    }

    private function getCommentByData($user)
    {
        if ($user->karyawan) {
            return $user->karyawan;
        }
        
        $commentBy = new \stdClass();
        $commentBy->N_NIK = $user->nik;
        $commentBy->V_NAMA_KARYAWAN = $user->name ?? 'Unknown User';
        
        return $commentBy;
    }
}