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
            // Admin hanya bisa melihat konsultasi yang ditujukan ke level mereka
            $adminRole = $user->pengurus->role->NAME;
            $userDPW = $user->pengurus->DPW ?? null;
            $userDPD = $user->pengurus->DPD ?? null;
            
            switch ($adminRole) {
                case 'ADMIN_DPD':
                    $konsultasiQuery->where('TUJUAN', 'DPD')
                                    ->where('TUJUAN_SPESIFIK', $userDPD);
                    break;
                    
                case 'ADMIN_DPW':
                    $konsultasiQuery->where('TUJUAN', 'DPW')
                                    ->where('TUJUAN_SPESIFIK', $userDPW);
                    break;
                
                case 'ADMIN_DPP':
                    $konsultasiQuery->where('TUJUAN', 'DPP');
                    break;
                    
                case 'ADM':
                    $konsultasiQuery->whereIn('TUJUAN', ['DPP', 'GENERAL']);
                    break;
            }
            
            $konsultasi = $konsultasiQuery->with('karyawan')->orderBy('CREATED_AT', 'desc')->paginate(10);
        } else {
            // User biasa hanya bisa melihat konsultasi mereka sendiri
            $konsultasi = $konsultasiQuery->where('N_NIK', $user->nik)
                                          ->with('karyawan')
                                          ->orderBy('CREATED_AT', 'desc')
                                          ->paginate(10);
        }

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
            
            $this->sendNotifications($konsultasi, 'new');

         
            if ($validated['jenis'] === 'ASPIRASI' && $validated['tujuan'] === 'DPP') {
                
              
                $dppAdmins = \App\Models\User::whereHas('pengurus.role', function ($query) {
                    $query->whereIn('NAME', ['ADMIN_DPP', 'ADM']);
                })->get();

                foreach ($dppAdmins as $admin) {
               
                    $existingNotification = \App\Models\Notification::where('notifiable_id', $admin->nik)
                        ->where('type', 'new') // Cari tipe 'new'
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

       
        // Check access permission
        $user = Auth::user();
        $isAdmin = $user->pengurus && $user->pengurus->role && 
                  in_array($user->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']);
                  
        if (!$isAdmin && $konsultasi->N_NIK !== $user->nik) {
            abort(403, 'Anda tidak memiliki akses untuk melihat konsultasi ini.');
        }

        // Get escalation options based on current user role and konsultasi level
        $escalationOptions = $this->getSmartEscalationOptions($konsultasi, $user);
        
        return view('konsultasi.show', compact('konsultasi', 'escalationOptions'));
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

        $konsultasi = Konsultasi::findOrFail($id);
        $user = Auth::user();
        
        // Validate escalation permission using smart rules
        $validationResult = $this->validateSmartEscalation($konsultasi, $user, $request->escalate_to, $request->escalate_to_specific);
        
        if (!$validationResult['allowed']) {
            return redirect()->back()
                ->with('error', $validationResult['message'])
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Store old values for notification context
            $oldTarget = $konsultasi->TUJUAN;
            $oldSpecific = $konsultasi->TUJUAN_SPESIFIK;

            // Update konsultasi
            $konsultasi->update([
                'TUJUAN' => $request->escalate_to,
                'TUJUAN_SPESIFIK' => $request->escalate_to_specific,
                'STATUS' => 'IN_PROGRESS',
                'UPDATED_BY' => $user->nik,
                'UPDATED_AT' => now()
            ]);

            // Add escalation comment
            KonsultasiKomentar::create([
                'ID_KONSULTASI' => $konsultasi->ID,
                'N_NIK' => $user->nik,
                'KOMENTAR' => "ESKALASI KE {$this->getEscalationLabel($request->escalate_to, $request->escalate_to_specific)}: {$request->komentar}",
                'PENGIRIM_ROLE' => 'ADMIN',
                'CREATED_AT' => now(),
                'CREATED_BY' => $user->nik
            ]);

            DB::commit();

            // Send notifications with proper context
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

            // 1. Notify the original submitter about escalation
            $this->notifyOriginalSubmitter($konsultasi, $context);

            // 2. Notify new target admins about the escalated konsultasi
            $this->notifyNewTargetAdmins($konsultasi, $context);

            /*
            // 3. Send email notifications
            $this->sendEscalationEmailNotifications($konsultasi, $context);
            */
            
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
            // Create in-app notification for the original submitter
            if ($this->notificationService) {
                $this->notificationService->notifyEscalation($konsultasi, [
                    'type' => 'escalation_to_submitter',
                    'escalated_to' => $this->getEscalationLabel($konsultasi->TUJUAN, $konsultasi->TUJUAN_SPESIFIK),
                    'escalation_reason' => $context['escalation_comment'] ?? 'Konsultasi dieskalasi'
                ]);
            }

            Log::info('Notified original submitter', [
                'konsultasi_id' => $konsultasi->ID,
                'submitter_nik' => $konsultasi->N_NIK
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify original submitter', [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function notifyNewTargetAdmins($konsultasi, $context)
    {
        try {
            // Create notification for new target admins
            if ($this->notificationService) {
                $this->notificationService->notifyEscalation($konsultasi, [
                    'type' => 'escalation_to_admins',
                    'escalated_from' => $this->getEscalationLabel(
                        $context['old_target'] ?? 'Unknown', 
                        $context['old_specific'] ?? null
                    ),
                    'escalated_to' => $this->getEscalationLabel($konsultasi->TUJUAN, $konsultasi->TUJUAN_SPESIFIK),
                    'escalation_reason' => $context['escalation_comment'] ?? 'Konsultasi dieskalasi'
                ]);
            }

            Log::info('Notified new target admins', [
                'konsultasi_id' => $konsultasi->ID,
                'new_target' => $konsultasi->TUJUAN,
                'new_specific' => $konsultasi->TUJUAN_SPESIFIK
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify new target admins', [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage()
            ]);
        }
    }

    /*
    private function sendEscalationEmailNotifications($konsultasi, $context)
    {
        try {
            // Send email to original submitter
            $this->sendEmailNotification($konsultasi, 'escalation_to_submitter');

            // Send email to new target admins  
            $this->sendEmailNotification($konsultasi, 'escalation_to_admin');

            Log::info('Escalation email notifications dispatched', [
                'konsultasi_id' => $konsultasi->ID
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send escalation email notifications', [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage()
            ]);
        }
    }
    */

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
                'DPP' => 'DPP (Dewan Pengurus Pusat)',
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
    private function getSmartEscalationOptions($konsultasi, $user): array
    {
        if (!$user->pengurus || !$user->pengurus->role) {
            return [];
        }

        $userRole = $user->pengurus->role->NAME;
        $userDPW = $user->pengurus->DPW ?? null;
        $userDPD = $user->pengurus->DPD ?? null;
        $currentLevel = $konsultasi->TUJUAN;
        $currentSpecific = $konsultasi->TUJUAN_SPESIFIK;

        $options = [];

        switch ($userRole) {
            case 'ADMIN_DPD':
                $options = $this->getDPDEscalationOptions($konsultasi, $userDPW, $userDPD);
                break;
                
            case 'ADMIN_DPW':
                $options = $this->getDPWEscalationOptions($konsultasi, $userDPW);
                break;
        }

        return $options;
    }

    /**
     * Get escalation options for DPD Admin
     */
     private function getDPDEscalationOptions($konsultasi, $userDPW, $userDPD): array
    {
        $currentLevel = $konsultasi->TUJUAN;
        $currentSpecific = $konsultasi->TUJUAN_SPESIFIK;
        
        if ($currentLevel !== 'DPD') {
            return []; // DPD admin can only escalate from DPD level
        }

        $options = [];
        if ($userDPW && $currentSpecific === $userDPD) {
            $dpdInSameDPW = $this->getDPDInSameDPW($userDPW);
            $otherDPDs = array_diff($dpdInSameDPW, [$userDPD]); // Exclude current user's DPD
            
            if (!empty($otherDPDs)) {
                $dpdOptions = [];
                foreach ($otherDPDs as $dpd) {
                    $dpdOptions[$dpd] = $dpd;
                }
                $options['DPD'] = [
                    'label' => 'DPD Lain di Wilayah yang Sama',
                    'specific_options' => $dpdOptions
                ];
            }
        }

        // 2. Eskalasi ke DPW mereka sendiri (step up)
        if ($userDPW) {
            $options['DPW'] = [
                'label' => 'DPW (Dewan Pengurus Wilayah)',
                'specific_options' => [$userDPW => $userDPW]
            ];
        }

        // TIDAK BISA LANGSUNG KE DPP ATAU GENERAL
        // Harus melalui DPW dulu

        return $options;
    }

    /**
     * Get escalation options for DPW Admin with smart rules
     */
    private function getDPWEscalationOptions($konsultasi, $userDPW): array
    {
        $currentLevel = $konsultasi->TUJUAN;
        $currentSpecific = $konsultasi->TUJUAN_SPESIFIK;
        
        $options = [];

        switch ($currentLevel) {
            case 'DPD':
                // DPW bisa eskalasi DPD yang ada di wilayahnya
                $dpdInSameDPW = $this->getDPDInSameDPW($userDPW);
                if (in_array($currentSpecific, $dpdInSameDPW)) {
                    // 1. Eskalasi ke DPD lain di DPW yang sama (lateral)
                    $otherDPDs = array_diff($dpdInSameDPW, [$currentSpecific]);
                    if (!empty($otherDPDs)) {
                        $dpdOptions = [];
                        foreach ($otherDPDs as $dpd) {
                            $dpdOptions[$dpd] = $dpd;
                        }
                        $options['DPD'] = [
                            'label' => 'DPD Lain di Wilayah yang Sama',
                            'specific_options' => $dpdOptions
                        ];
                    }

                    // 2. Eskalasi ke DPW sendiri (step up to own level)
                    $options['DPW'] = [
                        'label' => 'DPW (Dewan Pengurus Wilayah)',
                        'specific_options' => [$userDPW => $userDPW]
                    ];
                }
                break;

            case 'DPW':
                // DPW hanya bisa eskalasi dari DPW sendiri
                if ($currentSpecific === $userDPW) {
                    // 1. Eskalasi ke DPW lain (lateral)
                    $otherDPWs = $this->getOtherDPWs($userDPW);
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

                    // 2. Eskalasi ke DPP (step up)
                    $options['DPP'] = [
                        'label' => 'DPP (Dewan Pengurus Pusat)',
                        'specific_options' => ['DPP' => 'DPP Pusat']
                    ];

                    // 3. Bisa juga ke DPD di wilayah sendiri (step down)
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
                }
                break;

            case 'DPP':
                // DPW bisa handle escalation dari DPP jika diperlukan
                // Tapi biasanya DPP tidak dieskalasi ke DPW
                // Hanya untuk kasus khusus
                break;
        }

        return $options;
    }

    /*
     * Get escalation options for DPP Admin
     
    private function getDPPEscalationOptions($konsultasi): array
    {
        $currentLevel = $konsultasi->TUJUAN;
        
        if ($currentLevel === 'GENERAL') {
            return []; // Already at highest level
        }

        $options = [];

        switch ($currentLevel) {
            case 'DPD':
            case 'DPW':
            case 'DPP':
                // DPP bisa eskalasi ke GENERAL (SEKAR Pusat)
                $options['GENERAL'] = [
                    'label' => 'SEKAR Pusat',
                    'specific_options' => ['SEKAR Pusat' => 'SEKAR Pusat']
                ];
                break;
        }

        return $options;
    }

    /**
     * Validate smart escalation rules
     */
    private function validateSmartEscalation($konsultasi, $user, $escalateTo, $escalateToSpecific): array
    {
        $userRole = $user->pengurus->role->NAME;
        $userDPW = $user->pengurus->DPW ?? null;
        $userDPD = $user->pengurus->DPD ?? null;
        $currentLevel = $konsultasi->TUJUAN;
        $currentSpecific = $konsultasi->TUJUAN_SPESIFIK;

        // Validation for step-by-step escalation
        
        if ($userRole === 'ADMIN_DPD') {
            if ($currentLevel !== 'DPD' || $currentSpecific !== $userDPD) {
                return [
                    'allowed' => false,
                    'message' => 'Anda hanya dapat mengeskalasi konsultasi yang ditujukan ke DPD Anda sendiri.'
                ];
            }

            // DPD hanya bisa ke DPD lain atau DPW sendiri
            if (!in_array($escalateTo, ['DPD', 'DPW'])) {
                return [
                    'allowed' => false,
                    'message' => 'DPD hanya dapat mengeskalasi ke DPD lain di wilayah yang sama atau ke DPW sendiri. Untuk eskalasi ke DPP/SEKAR Pusat, harus melalui DPW terlebih dahulu.'
                ];
            }

            if ($escalateTo === 'DPD') {
                $dpdInSameDPW = $this->getDPDInSameDPW($userDPW);
                if (!in_array($escalateToSpecific, $dpdInSameDPW)) {
                    return [
                        'allowed' => false,
                        'message' => 'DPD hanya dapat mengeskalasi ke DPD lain yang berada di wilayah DPW yang sama.'
                    ];
                }
                if ($escalateToSpecific === $userDPD) {
                    return [
                        'allowed' => false,
                        'message' => 'Tidak dapat mengeskalasi ke DPD sendiri.'
                    ];
                }
            }

            // Validasi DPD → DPW
            if ($escalateTo === 'DPW' && $escalateToSpecific !== $userDPW) {
                return [
                    'allowed' => false,
                    'message' => 'DPD hanya dapat mengeskalasi ke DPW sendiri.'
                ];
            }
        }

        if ($userRole === 'ADMIN_DPW') {
            if ($currentLevel === 'DPD') {
                $dpdInSameDPW = $this->getDPDInSameDPW($userDPW);
                if (!in_array($currentSpecific, $dpdInSameDPW)) {
                    return [
                        'allowed' => false,
                        'message' => 'Anda hanya dapat mengeskalasi konsultasi DPD yang berada di wilayah DPW Anda.'
                    ];
                }
            } elseif ($currentLevel === 'DPW' && $currentSpecific !== $userDPW) {
                return [
                    'allowed' => false,
                    'message' => 'Anda hanya dapat mengeskalasi konsultasi yang ditujukan ke DPW Anda sendiri.'
                ];
            }

            if ($currentLevel === 'DPD' && $escalateTo === 'GENERAL') {
                return [
                    'allowed' => false,
                    'message' => 'Untuk eskalasi ke SEKAR Pusat dari DPD, harus melalui DPP terlebih dahulu.'
                ];
            }

            if ($escalateTo === 'DPD') {
                $targetDPDsInSameDPW = $this->getDPDInSameDPW($userDPW);
                if (!in_array($escalateToSpecific, $targetDPDsInSameDPW)) {
                    return [
                        'allowed' => false,
                        'message' => 'DPW tidak dapat mengeskalasi ke DPD yang berada di wilayah DPW lain.'
                    ];
                }
            }
        }
        if ($escalateTo === $currentLevel && $escalateToSpecific === $currentSpecific) {
            return [
                'allowed' => false,
                'message' => 'Tidak dapat mengeskalasi ke tujuan yang sama.'
            ];
        }

        return ['allowed' => true, 'message' => ''];
    }

    /**
     * Get DPDs that are in the same DPW
     */
    private function getDPDInSameDPW($dpw): array
    {
        if (!$dpw) return [];

        return DB::table('t_sekar_pengurus')
            ->where('DPW', $dpw)
            ->whereNotNull('DPD')
            ->where('DPD', '!=', '')
            ->distinct()
            ->pluck('DPD')
            ->toArray();
    }

    /**
     * Get other DPWs (excluding current user's DPW)
     */
    private function getOtherDPWs($currentDPW): array
    {
        $query = DB::table('t_sekar_pengurus')
            ->whereNotNull('DPW')
            ->where('DPW', '!=', '')
            ->distinct();

        if ($currentDPW) {
            $query->where('DPW', '!=', $currentDPW);
        }

        return $query->pluck('DPW')->toArray();
    }

    /**
     * Get escalation label for display
     */
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