<?php

namespace App\Http\Controllers;

use App\Models\Konsultasi;
use App\Models\KonsultasiKomentar;
use App\Models\Karyawan;
use App\Models\SekarPengurus;
use App\Mail\KonsultasiNotification;
use App\Jobs\SendKonsultasiNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class KonsultasiController extends Controller
{
    /**
     * Display a listing of konsultasi
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Konsultasi::with(['karyawan', 'komentar']);
        
        // Filter berdasarkan role dan akses
        if ($this->isAdmin($user)) {
            // Admin dapat melihat semua konsultasi sesuai level mereka
            $adminLevel = $this->getAdminLevel($user);
            $query = $this->filterByAdminLevel($query, $adminLevel);
        } else {
            // User biasa hanya melihat konsultasi miliknya
            $query->where('N_NIK', $user->nik);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('JUDUL', 'LIKE', "%{$search}%")
                  ->orWhere('DESKRIPSI', 'LIKE', "%{$search}%")
                  ->orWhere('KATEGORI_ADVOKASI', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('STATUS', $request->status);
        }
        
        // Filter by jenis
        if ($request->filled('jenis')) {
            $query->where('JENIS', $request->jenis);
        }
        
        // Filter by tujuan
        if ($request->filled('tujuan')) {
            $query->where('TUJUAN', $request->tujuan);
        }
        
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
        
        // Get available targets for both Advokasi and Aspirasi
        $availableTargets = $this->getAvailableTargets($karyawan);
        $kategoriAdvokasi = $this->getKategoriAdvokasi();
        $dpwOptions = $this->getDpwOptions();
        $dpdOptions = $this->getDpdOptions();
        
        return view('konsultasi.create', compact(
            'karyawan', 'availableTargets', 'kategoriAdvokasi', 'dpwOptions', 'dpdOptions'));
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
            $konsultasi = null;
            
            DB::transaction(function () use ($validated, $user, &$konsultasi) {
                $konsultasi = $this->createKonsultasi($user, $validated);
            });
            
            // Send notifications
            try {
                $this->sendEmailNotification($konsultasi, 'new');
                Log::info('Email notification sent for new konsultasi', [
                    'konsultasi_id' => $konsultasi->ID
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send email notification for new konsultasi (non-critical)', [
                    'konsultasi_id' => $konsultasi->ID,
                    'error' => $e->getMessage()
                ]);
            }
            
            return redirect()->route('konsultasi.index')
                           ->with('success', ucfirst($validated['jenis']) . ' berhasil diajukan dan akan ditindaklanjuti.');
        } catch (\Exception $e) {
            Log::error('Error creating konsultasi: ' . $e->getMessage());
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Terjadi kesalahan saat menyimpan ' . strtolower($validated['jenis']) . '. Silakan coba lagi.');
        }
    }
    
    /**
     * Display the specified konsultasi
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $konsultasi = Konsultasi::with(['karyawan', 'komentar.karyawan', 'komentar.user'])->findOrFail($id);
        
        // Check access permission
        if (!$this->canAccessKonsultasi($user, $konsultasi)) {
            return redirect()->route('konsultasi.index')
                           ->with('error', 'Anda tidak memiliki akses untuk melihat konsultasi ini.');
        }
        
        return view('konsultasi.show', compact('konsultasi'));
    }
    
    /**
     * Add comment to konsultasi
     */
    public function comment(Request $request, string $id)
    {
        $validated = $request->validate([
            'komentar' => 'required|string|max:1000'
        ]);
        
        $user = Auth::user();
        $konsultasi = Konsultasi::findOrFail($id);
        
        // Check comment permission
        if (!$this->canCommentOnKonsultasi($user, $konsultasi)) {
            return redirect()->back()
                           ->with('error', 'Anda tidak dapat menambahkan komentar pada konsultasi ini.');
        }
        
        try {
            DB::transaction(function () use ($konsultasi, $user, $validated) {
                // Determine comment role
                $jenisKomentar = $this->isAdmin($user) ? 'ADMIN' : 'USER';
                
                // Create comment
                $this->createKomentar($konsultasi->ID, $user->nik, $validated['komentar'], $jenisKomentar);
                
                // Update konsultasi status if needed
                if ($konsultasi->STATUS === 'OPEN') {
                    $konsultasi->STATUS = 'IN_PROGRESS';
                    $konsultasi->UPDATED_BY = $user->nik;
                    $konsultasi->UPDATED_AT = now();
                    $konsultasi->save();
                }
            });
            
            // Send email notification
            try {
                $this->sendEmailNotification($konsultasi, 'comment');
            } catch (\Exception $e) {
                Log::error('Failed to send comment email notification', [
                    'konsultasi_id' => $konsultasi->ID,
                    'error' => $e->getMessage()
                ]);
            }
            
            return redirect()->back()
                           ->with('success', 'Komentar berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error adding comment: ' . $e->getMessage());
            
            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat menambahkan komentar.');
        }
    }
    
    /**
     * Close konsultasi (Admin only)
     */
    public function close(string $id)
    {
        $user = Auth::user();
        
        if (!$this->isAdmin($user)) {
            return redirect()->back()
                           ->with('error', 'Akses ditolak. Hanya admin yang dapat menutup konsultasi.');
        }
        
        $konsultasi = Konsultasi::findOrFail($id);
        
        if ($konsultasi->STATUS === 'CLOSED') {
            return redirect()->back()
                           ->with('warning', 'Konsultasi sudah dalam status ditutup.');
        }
        
        try {
            $konsultasi->STATUS = 'CLOSED';
            $konsultasi->CLOSED_BY = $user->nik;
            $konsultasi->CLOSED_AT = now();
            $konsultasi->UPDATED_BY = $user->nik;
            $konsultasi->UPDATED_AT = now();
            $konsultasi->save();
            
            // Send notification
            try {
                $this->sendEmailNotification($konsultasi, 'closed');
            } catch (\Exception $e) {
                Log::error('Failed to send close email notification', [
                    'konsultasi_id' => $konsultasi->ID,
                    'error' => $e->getMessage()
                ]);
            }
            
            return redirect()->back()
                           ->with('success', 'Konsultasi berhasil ditutup.');
        } catch (\Exception $e) {
            Log::error('Error closing konsultasi: ' . $e->getMessage());
            
            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat menutup konsultasi.');
        }
    }
    
    /**
     * Escalate konsultasi to higher level (Admin only)
     */
    public function escalate(Request $request, string $id)
    {
        $validated = $request->validate([
            'escalate_to' => 'required|in:DPW,DPP',
            'escalation_reason' => 'required|string|max:500'
        ]);
        
        $user = Auth::user();
        
        if (!$this->isAdmin($user)) {
            return redirect()->back()
                           ->with('error', 'Akses ditolak. Hanya admin yang dapat melakukan eskalasi.');
        }
        
        $konsultasi = Konsultasi::findOrFail($id);
        
        // Validate escalation path
        $validTargets = $this->getValidEscalationTargets($konsultasi->TUJUAN);
        if (!array_key_exists($validated['escalate_to'], $validTargets)) {
            return redirect()->back()
                           ->with('error', 'Eskalasi tidak valid untuk tujuan saat ini.');
        }
        
        try {
            DB::transaction(function () use ($konsultasi, $validated, $user) {
                // Update konsultasi target
                $konsultasi->TUJUAN = $validated['escalate_to'];
                $konsultasi->UPDATED_BY = $user->nik;
                $konsultasi->UPDATED_AT = now();
                $konsultasi->save();
                
                // Add escalation comment
                $escalationComment = "Konsultasi dieskalasi ke " . $validated['escalate_to'] . ". Alasan: " . $validated['escalation_reason'];
                $this->createKomentar($konsultasi->ID, $user->nik, $escalationComment, 'ADMIN');
            });
            
            // Send notification
            try {
                $this->sendEmailNotification($konsultasi, 'escalate');
            } catch (\Exception $e) {
                Log::error('Failed to send escalation email notification', [
                    'konsultasi_id' => $konsultasi->ID,
                    'error' => $e->getMessage()
                ]);
            }
            
            return redirect()->back()
                           ->with('success', 'Konsultasi berhasil dieskalasi ke ' . $validated['escalate_to'] . '.');
        } catch (\Exception $e) {
            Log::error('Error escalating konsultasi: ' . $e->getMessage());
            
            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat melakukan eskalasi.');
        }
    }
    
    /**
     * Get escalation options for AJAX
     */
    public function getEscalationOptions(string $id)
    {
        $konsultasi = Konsultasi::findOrFail($id);
        $validTargets = $this->getValidEscalationTargets($konsultasi->TUJUAN);
        
        return response()->json([
            'success' => true,
            'options' => $validTargets
        ]);
    }
    
    /**
     * Get konsultasi statistics
     */
    public function getStats(Request $request)
    {
        $user = Auth::user();
        $nik = $this->isAdmin($user) ? null : $user->nik;
        
        $stats = Konsultasi::getStats($nik);
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Get target options based on jenis (API endpoint)
     */
    public function getTargetOptions(string $jenis)
    {
        $user = Auth::user();
        $karyawan = Karyawan::where('N_NIK', $user->nik)->first();
        $targets = $this->getAvailableTargets($karyawan);
        
        $jenisLower = strtolower($jenis);
        $options = $targets[$jenisLower] ?? [];
        
        return response()->json([
            'success' => true,
            'targets' => $options,
            'auto_mapped' => $this->getAutoMappedTarget($karyawan)
        ]);
    }
    
    /**
     * Get auto-mapped target for current user (API endpoint)
     */
    public function getAutoTarget()
    {
        $user = Auth::user();
        $karyawan = Karyawan::where('N_NIK', $user->nik)->first();
        
        return response()->json([
            'success' => true,
            'auto_mapped' => $this->getAutoMappedTarget($karyawan),
            'dpw_options' => $this->getDpwOptions(),
            'dpd_options' => $this->getDpdOptions()
        ]);
    }
    
    /**
     * Get available targets based on user location and jenis
     */
    private function getAvailableTargets($karyawan): array
    {
        // Return all options, will be filtered by JavaScript based on jenis
        return [
            'advokasi' => [
                'DPD' => 'DPD (Dewan Pengurus Daerah)',
                'DPW' => 'DPW (Dewan Pengurus Wilayah)'
                // DPP removed from initial options, only via escalation
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
     * Get DPW options for dropdown
     */
    private function getDpwOptions(): array
    {
        return DB::table('t_sekar_pengurus')
            ->whereNotNull('DPW')
            ->where('DPW', '!=', '')
            ->distinct()
            ->pluck('DPW')
            ->sort()
            ->values()
            ->toArray();
    }
    
    /**
     * Get DPD options for dropdown
     */
    private function getDpdOptions(): array
    {
        return DB::table('t_sekar_pengurus')
            ->whereNotNull('DPD')
            ->where('DPD', '!=', '')
            ->distinct()
            ->pluck('DPD')
            ->sort()
            ->values()
            ->toArray();
    }
    
    /**
     * Get auto-mapped target based on user location
     */
    private function getAutoMappedTarget($karyawan): array
    {
        if (!$karyawan) {
            return [
                'dpw' => 'DPW Jakarta',
                'dpd' => 'DPD Jakarta Pusat'
            ];
        }
        
        // Map based on location
        $location = strtoupper($karyawan->V_KOTA_GEDUNG ?? '');
        
        if (strpos($location, 'JAKARTA') !== false) {
            return [
                'dpw' => 'DPW Jakarta',
                'dpd' => 'DPD Jakarta Pusat'
            ];
        } elseif (strpos($location, 'BANDUNG') !== false) {
            return [
                'dpw' => 'DPW Jabar', 
                'dpd' => 'DPD Bandung'
            ];
        } elseif (strpos($location, 'SURABAYA') !== false) {
            return [
                'dpw' => 'DPW Jatim',
                'dpd' => 'DPD Surabaya'
            ];
        } elseif (strpos($location, 'MEDAN') !== false) {
            return [
                'dpw' => 'DPW Sumut',
                'dpd' => 'DPD Medan'
            ];
        } elseif (strpos($location, 'MAKASSAR') !== false) {
            return [
                'dpw' => 'DPW Sulsel',
                'dpd' => 'DPD Makassar'
            ];
        } else {
            return [
                'dpw' => 'DPW Jakarta',
                'dpd' => 'DPD Jakarta Pusat'
            ];
        }
    }
    
    /**
     * Get valid escalation targets
     */
    private function getValidEscalationTargets(string $currentTarget): array
    {
        switch ($currentTarget) {
            case 'DPD':
                return [
                    'DPW' => 'DPW (Dewan Pengurus Wilayah)',
                    'DPP' => 'DPP (Dewan Pengurus Pusat)'
                ];
            case 'DPW':
                return [
                    'DPP' => 'DPP (Dewan Pengurus Pusat)'
                ];
            case 'GENERAL':
                return [
                    'DPP' => 'DPP (Dewan Pengurus Pusat)'
                ];
            default:
                return []; // DPP is highest level
        }
    }
    
    /**
     * Send email notification
     */
    private function sendEmailNotification($konsultasi, string $actionType)
    {
        try {
            // Get recipient email based on target
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
        switch ($target) {
            case 'DPD':
                return env('DPD_ADMIN_EMAIL');
            case 'DPW':
                return env('DPW_ADMIN_EMAIL');
            case 'DPP':
                return env('DPP_ADMIN_EMAIL');
            case 'GENERAL':
                return env('GENERAL_ADMIN_EMAIL');
            default:
                return env('ADMIN_EMAIL', 'admin@sekar.telkom.co.id');
        }
    }
    
    /**
     * Check if user is admin
     */
    private function isAdmin($user): bool
    {
        return $user->pengurus && 
               $user->pengurus->role && 
               in_array($user->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']);
    }
    
    /**
     * Get admin level for user
     */
    private function getAdminLevel($user): int
    {
        if (!$user->pengurus || !$user->pengurus->role) {
            return 0;
        }
        
        switch ($user->pengurus->role->NAME) {
            case 'ADM':
                return 4;
            case 'ADMIN_DPP':
                return 3;
            case 'ADMIN_DPW':
                return 2;
            case 'ADMIN_DPD':
                return 1;
            default:
                return 0;
        }
    }
    
    /**
     * Filter query by admin level
     */
    private function filterByAdminLevel($query, int $adminLevel)
    {
        switch($adminLevel) {
            case 4: // ADM - can see all
                break;
            case 3: // ADMIN_DPP - can see DPP and GENERAL
                $query->whereIn('TUJUAN', ['DPP', 'GENERAL']);
                break;
            case 2: // ADMIN_DPW - can see DPW, DPP, and GENERAL
                $query->whereIn('TUJUAN', ['DPW', 'DPP', 'GENERAL']);
                break;
            case 1: // ADMIN_DPD - can see DPD, DPW, DPP, and GENERAL
                $query->whereIn('TUJUAN', ['DPD', 'DPW', 'DPP', 'GENERAL']);
                break;
            default:
                // No admin access, return empty
                $query->where('ID', 0);
        }
        
        return $query;
    }
    
    /**
     * Check if user can access konsultasi
     */
    private function canAccessKonsultasi($user, $konsultasi): bool
    {
        // User can access own konsultasi or admin can access all
        return $konsultasi->N_NIK === $user->nik || $this->isAdmin($user);
    }
    
    /**
     * Check if user can comment on konsultasi
     */
    private function canCommentOnKonsultasi($user, $konsultasi): bool
    {
        // Cannot comment on closed konsultasi
        if ($konsultasi->STATUS === 'CLOSED') {
            return false;
        }
        
        return $konsultasi->N_NIK === $user->nik || $this->isAdmin($user);
    }
    
    /**
     * Create new konsultasi record
     */
    private function createKonsultasi($user, array $validated): Konsultasi
    {
        return Konsultasi::create([
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
    }
    
    /**
     * Create new comment record
     */
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
}