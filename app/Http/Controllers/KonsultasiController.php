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
use Illuminate\Support\Facades\Validator;

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
     public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->pengurus && $user->pengurus->role && 
                in_array($user->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']);

        $konsultasiQuery = Konsultasi::query();

        if ($isAdmin) {
            $adminRole = $user->pengurus->role->NAME;


            if ($adminRole === 'ADM') {

            } else {
                $userDPW = $user->pengurus->DPW ?? null;
                $userDPD = $user->pengurus->DPD ?? null;
                
                $konsultasiQuery->where(function ($query) use ($adminRole, $userDPW, $userDPD, $user) {
                    $query->where(function ($q) use ($adminRole, $userDPW, $userDPD) {
                        if ($adminRole === 'ADMIN_DPD') {
                            $q->where('TUJUAN', 'DPD')->whereRaw('UPPER(TUJUAN_SPESIFIK) = ?', [strtoupper($userDPD)]);
                        } elseif ($adminRole === 'ADMIN_DPW') {
                            $q->where('TUJUAN', 'DPW')->whereRaw('UPPER(TUJUAN_SPESIFIK) = ?', [strtoupper($userDPW)]);
                        } elseif ($adminRole === 'ADMIN_DPP') {
                            $q->where('TUJUAN', 'DPP');
                        }
                    });

                    $query->orWhereHas('komentar', function ($q) use ($user) {
                        $q->where('N_NIK', $user->nik);
                    });
                });
            }

        } else {
            $konsultasiQuery->where('N_NIK', $user->nik);
        }

        $konsultasi = $konsultasiQuery->with('karyawan')
                                    ->orderBy('CREATED_AT', 'desc')
                                    ->paginate(10);

        return view('konsultasi.index', compact('konsultasi'));
    }
    
    /**
     * Show the form for creating a new konsultasi
     */
    public function create()
    {
        $user = Auth::user();
        $karyawan = Karyawan::where('N_NIK', $user->nik)->first();
        
        $userDPD = $karyawan->DPD ?? null;
        $userDPW = $karyawan->DPW ?? null;
        
        return view('konsultasi.create', [
            'karyawan' => $karyawan,
            'userDPD' => $userDPD,
            'userDPW' => $userDPW,
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
            
            $this->sendNotifications($konsultasi, 'new');

            if ($validated['jenis'] === 'ASPIRASI' && $validated['tujuan'] === 'DPP') {
                $dppAdmins = \App\Models\User::whereHas('pengurus.role', function ($query) {
                    $query->whereIn('NAME', ['ADMIN_DPP', 'ADM']);
                })->get();

                foreach ($dppAdmins as $admin) {
                    $existingNotification = \App\Models\Notification::where('notifiable_id', $admin->nik)
                        ->where('type', 'new')
                        ->whereJsonContains('data->konsultasi_id', $konsultasi->ID)
                        ->first();
            
                    if (!$existingNotification) {
                        \App\Models\Notification::create([
                            'type' => 'new', 
                            'notifiable_type' => \App\Models\User::class,
                            'notifiable_id' => $admin->nik,
                            'data' => [
                                'konsultasi_id' => $konsultasi->ID,
                                'jenis' => strtolower($konsultasi->JENIS),
                                'judul' => $konsultasi->JUDUL,
                                'from_user' => $user->name,
                                'target_level' => 'DPP',
                            ],
                        ]);
                    }
                }
            }
            
            return redirect()->route('konsultasi.index')
                            ->with('success', ucfirst(strtolower($validated['jenis'])) . ' berhasil diajukan dan akan ditindaklanjuti.');

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
   public function show($id)
    {
        $konsultasi = Konsultasi::with(['komentar' => function($query) {
            $query->orderBy('CREATED_AT', 'asc');
        }])->findOrFail($id);

        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
                  
        if (!$isAdmin && $konsultasi->N_NIK !== $user->nik) {
            abort(403, 'Anda tidak memiliki akses untuk melihat konsultasi ini.');
        }
        
        $escalationOptions = $this->getSmartEscalationOptions($konsultasi, $user);
        $isCurrentUserActiveHandler = $this->isTicketHandler($user, $konsultasi);
        
        return view('konsultasi.show', compact('konsultasi', 'escalationOptions', 'isCurrentUserActiveHandler'));
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
                
                if ($jenisKomentar === 'ADMIN' && $konsultasi->STATUS === 'OPEN') {
                    $konsultasi->update([
                        'STATUS' => 'IN_PROGRESS',
                        'UPDATED_BY' => $user->nik,
                        'UPDATED_AT' => now()
                    ]);
                }
            });
            
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
        $user = Auth::user();
        $konsultasi = Konsultasi::findOrFail($id);

        if (!$this->isTicketHandler($user, $konsultasi)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak untuk menutup konsultasi ini.');
        }
        
        try {
            DB::transaction(function () use ($id, $user, $konsultasi) {
                $konsultasi->update([
                    'STATUS' => 'CLOSED',
                    'CLOSED_BY' => $user->nik,
                    'CLOSED_AT' => now(),
                    'UPDATED_BY' => $user->nik,
                    'UPDATED_AT' => now()
                ]);
                
                $this->createKomentar($id, $user->nik, 'Konsultasi telah ditutup dan diselesaikan.', 'ADMIN');
            });
            
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
        $user = Auth::user();
        $konsultasi = Konsultasi::findOrFail($id);

        // Otorisasi dipindahkan ke middleware, namun pengecekan di sini tetap baik sebagai garda kedua.
        if (!$this->isTicketHandler($user, $konsultasi)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak untuk melakukan eskalasi pada konsultasi ini.');
        }

        $validator = Validator::make($request->all(), [
            'escalate_to' => 'required|string',
            'escalate_to_specific' => 'nullable|string',
            'komentar' => 'required|string|min:10'
        ], [
            'escalate_to.required' => 'Tujuan eskalasi wajib dipilih',
            'komentar.required' => 'Komentar eskalasi wajib diisi',
            'komentar.min' => 'Komentar minimal 10 karakter'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $validationResult = $this->validateSmartEscalation($konsultasi, $user, $request->escalate_to, $request->escalate_to_specific);
        
        if (!$validationResult['allowed']) {
            return redirect()->back()
                ->with('error', $validationResult['message'])
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $oldTarget = $konsultasi->TUJUAN;
            $oldSpecific = $konsultasi->TUJUAN_SPESIFIK;

            $konsultasi->update([
                'TUJUAN' => $request->escalate_to,
                'TUJUAN_SPESIFIK' => $request->escalate_to_specific,
                'STATUS' => 'IN_PROGRESS',
                'UPDATED_BY' => $user->nik,
                'UPDATED_AT' => now()
            ]);

            KonsultasiKomentar::create([
                'ID_KONSULTASI' => $konsultasi->ID,
                'N_NIK' => $user->nik,
                'KOMENTAR' => "ESKALASI KE {$this->getEscalationLabel($request->escalate_to, $request->escalate_to_specific)}: {$request->komentar}",
                'PENGIRIM_ROLE' => 'ADMIN',
                'CREATED_AT' => now(),
                'CREATED_BY' => $user->nik
            ]);

            DB::commit();

            $this->sendEscalationNotifications($konsultasi, $user, [
                'old_target' => $oldTarget,
                'old_specific' => $oldSpecific,
                'escalation_comment' => $request->komentar
            ]);

            return redirect()->back()
                ->with('success', 'Konsultasi berhasil dieskalasi ke ' . 
                       $this->getEscalationLabel($request->escalate_to, $request->escalate_to_specific));

        } catch (\Exception $e) {
            DB::rollBack();
            
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

    private function isTicketHandler($user, $konsultasi): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        $adminRole = $user->pengurus->role->NAME;
        $userDPW = $user->pengurus->DPW ?? null;
        $userDPD = $user->pengurus->DPD ?? null;

        if ($adminRole === 'ADM') return true;
        if ($adminRole === 'ADMIN_DPP' && $konsultasi->TUJUAN === 'DPP') return true;
        
        if ($adminRole === 'ADMIN_DPW' && $konsultasi->TUJUAN === 'DPW') {
            return strcasecmp($konsultasi->TUJUAN_SPESIFIK, $userDPW) === 0;
        }
        
        if ($adminRole === 'ADMIN_DPD' && $konsultasi->TUJUAN === 'DPD') {
            return strcasecmp($konsultasi->TUJUAN_SPESIFIK, $userDPD) === 0;
        }

        return false;
    }

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

    private function sendNotifications($konsultasi, string $type, array $context = [])
    {
        try {
            $konsultasi->load('karyawan');
            
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
            
            $this->sendEmailNotification($konsultasi, $type);
            
        } catch (\Exception $e) {
            Log::error("Failed to send {$type} notifications", [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage()
            ]);
        }
    }

   private function sendEscalationNotifications($konsultasi, $escalatedBy, $context = [])
    {
        try {
            Log::info('Sending escalation notifications', [
                'konsultasi_id' => $konsultasi->ID,
                'escalated_by' => $escalatedBy->nik,
                'new_target' => $konsultasi->TUJUAN,
                'new_specific' => $konsultasi->TUJUAN_SPESIFIK,
                'context' => $context
            ]);

            $this->notifyOriginalSubmitter($konsultasi, $context);
            $this->notifyNewTargetAdmins($konsultasi, $context);
            
        } catch (\Exception $e) {
            Log::error('Failed to send escalation notifications', [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function notifyOriginalSubmitter($konsultasi, $context)
    {
        try {
            if ($this->notificationService) {
                $this->notificationService->notifyEscalation($konsultasi, [
                    'type' => 'escalation_to_submitter',
                    'escalated_to' => $this->getEscalationLabel($konsultasi->TUJUAN, $konsultasi->TUJUAN_SPESIFIK),
                    'escalation_reason' => $context['escalation_comment'] ?? 'Konsultasi dieskalasi'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify original submitter', ['konsultasi_id' => $konsultasi->ID, 'error' => $e->getMessage()]);
        }
    }

    private function notifyNewTargetAdmins($konsultasi, $context)
    {
        try {
            if ($this->notificationService) {
                $this->notificationService->notifyEscalation($konsultasi, [
                    'type' => 'escalation_to_admins',
                    'escalated_from' => $this->getEscalationLabel($context['old_target'] ?? 'Unknown', $context['old_specific'] ?? null),
                    'escalated_to' => $this->getEscalationLabel($konsultasi->TUJUAN, $konsultasi->TUJUAN_SPESIFIK),
                    'escalation_reason' => $context['escalation_comment'] ?? 'Konsultasi dieskalasi'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify new target admins', ['konsultasi_id' => $konsultasi->ID, 'error' => $e->getMessage()]);
        }
    }

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
                'DPP' => 'DPP (Dewan Pengurus Pusat)',
            ]
        ];
    }
    
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
    
    private function getDropdownOptions(string $type): array
    {
        return DB::table('mapping_dpd')
            ->whereNotNull($type)
            ->where($type, '!=', '')
            ->distinct()
            ->pluck($type)
            ->sort()
            ->values()
            ->toArray();
    }
    
    private function getSmartEscalationOptions($konsultasi, $user): array
    {
        if (!$user->pengurus || !$user->pengurus->role) {
            return [];
        }

        $userRole = $user->pengurus->role->NAME;
        $options = [];

        switch ($userRole) {
            case 'ADMIN_DPD':
                $options = $this->getDPDEscalationOptions($user);
                break;
                
            case 'ADMIN_DPW':
                $options = $this->getDPWEscalationOptions($user);
                break;
        }

        return $options;
    }

    private function getDPDEscalationOptions($user): array
    {
        $userDPW = $user->pengurus->DPW ?? null;
        if (!$userDPW) {
            return [];
        }

        return [
            'DPW' => [
                'label' => 'DPW (Dewan Pengurus Wilayah)',
                'specific_options' => [$userDPW => $userDPW]
            ]
        ];
    }

    private function getDPWEscalationOptions($user): array
    {
        $userDPW = $user->pengurus->DPW ?? null;
        if (!$userDPW) {
            return [];
        }
    
        $options = [];
    
        $allDPWs = $this->getDropdownOptions('DPW');
        $otherDPWs = array_diff($allDPWs, [$userDPW]);
        if (!empty($otherDPWs)) {
            $dpwOptions = [];
            foreach ($otherDPWs as $dpw) {
                $dpwOptions[$dpw] = $dpw;
            }
            $options['DPW'] = [
                'label' => 'DPW Lain',
                'specific_options' => $dpwOptions
            ];
        }
    
        $dpdInSameDPW = $this->getDPDInSameDPW($userDPW);
        if (!empty($dpdInSameDPW)) {
            $dpdOptions = [];
            foreach ($dpdInSameDPW as $dpd) {
                $dpdOptions[$dpd] = $dpd;
            }
            $options['DPD'] = [
                'label' => 'DPD di Wilayah Sendiri',
                'specific_options' => $dpdOptions
            ];
        }
    
        $options['DPP'] = [
            'label' => 'DPP (Dewan Pengurus Pusat)',
            'specific_options' => ['DPP' => 'DPP Pusat']
        ];
    
        return $options;
    }

    private function validateSmartEscalation($konsultasi, $user, $escalateTo, $escalateToSpecific): array
    {
        if (strcasecmp($escalateTo, $konsultasi->TUJUAN) === 0 && strcasecmp($escalateToSpecific, $konsultasi->TUJUAN_SPESIFIK) === 0) {
            return [
                'allowed' => false,
                'message' => 'Tidak dapat mengeskalasi ke tujuan yang sama.'
            ];
        }

        return ['allowed' => true, 'message' => ''];
    }

    private function getDPDInSameDPW($dpw): array
    {
        if (!$dpw) return [];

        return DB::table('mapping_dpd')
            ->where('DPW', $dpw)
            ->whereNotNull('DPD')
            ->where('DPD', '!=', '')
            ->distinct()
            ->pluck('DPD')
            ->toArray();
    }

    private function getEscalationLabel($level, $specific): string
    {
        $labels = [
            'DPD' => 'DPD',
            'DPW' => 'DPW',
            'DPP' => 'DPP',
            'GENERAL' => 'SEKAR Pusat'
        ];

        $label = $labels[$level] ?? $level;
        
        if ($specific && $specific !== $level) {
            $label .= " ({$specific})";
        }

        return $label;
    }

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

    private function isAdmin($user): bool
    {
        return $user->pengurus?->role && 
               in_array($user->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']);
    }
    
    private function canCommentOnKonsultasi($user, $konsultasi): bool
    {
        if ($konsultasi->STATUS === 'CLOSED') return false;
        return $konsultasi->N_NIK === $user->nik || $this->isAdmin($user);
    }
    
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