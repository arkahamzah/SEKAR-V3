<?php

namespace App\Http\Controllers;

use App\Models\Konsultasi;
use App\Models\KonsultasiKomentar;
use App\Models\Karyawan;
use App\Models\KonsultasiFollower; // <-- DITAMBAHKAN
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
                // Super Admin can see all
            } else {
                $userDPW = $user->pengurus->DPW ?? null;
                $userDPD = $user->pengurus->DPD ?? null;

                $konsultasiQuery->where(function ($query) use ($adminRole, $userDPW, $userDPD, $user) {
                    // Condition 1: Konsultasi ditujukan ke level admin saat ini
                    $query->where(function ($q) use ($adminRole, $userDPW, $userDPD) {
                        if ($adminRole === 'ADMIN_DPD') {
                            $q->where('TUJUAN', 'DPD')->where('TUJUAN_SPESIFIK', $userDPD);
                        } elseif ($adminRole === 'ADMIN_DPW') {
                            $q->where('TUJUAN', 'DPW')->where('TUJUAN_SPESIFIK', $userDPW);
                        } elseif ($adminRole === 'ADMIN_DPP') {
                            $q->where('TUJUAN', 'DPP');
                        }
                    });

                    // Condition 2: Admin pernah berkomentar di konsultasi
                    $query->orWhereHas('komentar', function ($q) use ($user) {
                        $q->where('N_NIK', $user->nik);
                    });

                    // <-- PERUBAHAN DI SINI -->
                    // Condition 3: Admin adalah follower dari konsultasi ini
                    $query->orWhereHas('followers', function ($q) use ($user) {
                        $q->where('user_nik', $user->nik);
                    });
                });
            }

        } else {
            // Jika bukan admin, hanya lihat konsultasi milik sendiri
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
        }, 'followers.user.karyawan'])->findOrFail($id); // Eager load followers

        $user = Auth::user();
        $isAdmin = $user->pengurus && $user->pengurus->role &&
                  in_array($user->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']);

        // <-- PERUBAHAN UNTUK HAK AKSES -->
        $isFollower = $konsultasi->isFollowedBy($user);

        // User biasa hanya boleh lihat konsultasi miliknya
        if (!$isAdmin && $konsultasi->N_NIK !== $user->nik) {
            abort(403, 'Anda tidak memiliki akses untuk melihat konsultasi ini.');
        }

        // Cek akses untuk Admin (selain Super Admin)
        if ($isAdmin && $user->pengurus->role->NAME !== 'ADM') {
            $canView = false;
            $adminRole = $user->pengurus->role->NAME;
            $userDPW = $user->pengurus->DPW ?? null;
            $userDPD = $user->pengurus->DPD ?? null;

            // Cek apakah konsultasi ditujukan untuk admin ini
            if ($adminRole === 'ADMIN_DPD' && $konsultasi->TUJUAN === 'DPD' && $konsultasi->TUJUAN_SPESIFIK === $userDPD) $canView = true;
            if ($adminRole === 'ADMIN_DPW' && $konsultasi->TUJUAN === 'DPW' && $konsultasi->TUJUAN_SPESIFIK === $userDPW) $canView = true;
            if ($adminRole === 'ADMIN_DPP' && $konsultasi->TUJUAN === 'DPP') $canView = true;

            // Jika tidak ditujukan untuknya dan dia juga bukan follower, tolak akses
            if (!$canView && !$isFollower) {
                 abort(403, 'Anda tidak memiliki akses untuk melihat konsultasi ini.');
            }
        }

        $escalationOptions = $this->getSmartEscalationOptions($konsultasi, $user);

        // Logika untuk menentukan siapa handler aktif saat ini
        $isCurrentUserActiveHandler = false;
        if ($isAdmin) {
            $adminRole = $user->pengurus->role->NAME;
            $userDPW = $user->pengurus->DPW ?? null;
            $userDPD = $user->pengurus->DPD ?? null;

            if ($adminRole === 'ADM' && in_array($konsultasi->TUJUAN, ['DPP', 'GENERAL'])) $isCurrentUserActiveHandler = true;
            if ($adminRole === 'ADMIN_DPP' && $konsultasi->TUJUAN === 'DPP') $isCurrentUserActiveHandler = true;
            if ($adminRole === 'ADMIN_DPW' && $konsultasi->TUJUAN === 'DPW' && $konsultasi->TUJUAN_SPESIFIK === $userDPW) $isCurrentUserActiveHandler = true;
            if ($adminRole === 'ADMIN_DPD' && $konsultasi->TUJUAN === 'DPD' && $konsultasi->TUJUAN_SPESIFIK === $userDPD) $isCurrentUserActiveHandler = true;
        }

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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $konsultasi = Konsultasi::findOrFail($id);
        $user = Auth::user();

        $validationResult = $this->validateSmartEscalation($konsultasi, $user, $request->escalate_to, $request->escalate_to_specific);

        if (!$validationResult['allowed']) {
            return redirect()->back()
                ->with('error', $validationResult['message'])
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // <-- PERUBAHAN DI SINI: TAMBAHKAN ADMIN SEBAGAI FOLLOWER -->
            KonsultasiFollower::updateOrCreate(
                [
                    'konsultasi_id' => $konsultasi->ID,
                    'user_nik' => $user->nik
                ],
                [] // Tidak perlu update kolom lain jika sudah ada
            );

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
    // PRIVATE HELPER METHODS (TIDAK ADA PERUBAHAN DI BAWAH INI)
    // =====================================================

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
            $this->notifyOriginalSubmitter($konsultasi, $context);
            $this->notifyNewTargetAdmins($konsultasi, $context);
        } catch (\Exception $e) {
            Log::error('Failed to send escalation notifications', [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function notifyOriginalSubmitter($konsultasi, $context)
    {
        if ($this->notificationService) {
            $this->notificationService->notifyEscalation($konsultasi, [
                'type' => 'escalation_to_submitter',
                'escalated_to' => $this->getEscalationLabel($konsultasi->TUJUAN, $konsultasi->TUJUAN_SPESIFIK),
                'escalation_reason' => $context['escalation_comment'] ?? 'Konsultasi dieskalasi'
            ]);
        }
    }

    private function notifyNewTargetAdmins($konsultasi, $context)
    {
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
    }

    private function getAvailableTargets(): array
    {
        return [
            'advokasi' => ['DPD' => 'DPD', 'DPW' => 'DPW'],
            'aspirasi' => ['DPD' => 'DPD', 'DPW' => 'DPW', 'DPP' => 'DPP']
        ];
    }

    private function getKategoriAdvokasi(): array
    {
        return ['Pelecehan di Tempat Kerja', 'Diskriminasi', 'Pelanggaran Hak Karyawan', 'Masalah Gaji/Tunjangan', 'Lainnya'];
    }

    private function getDropdownOptions(string $type): array
    {
        return DB::table('mapping_dpd')->whereNotNull($type)->where($type, '!=', '')->distinct()->pluck($type)->sort()->values()->toArray();
    }

    private function getSmartEscalationOptions($konsultasi, $user): array
    {
        if (!$user->pengurus || !$user->pengurus->role) return [];
        $userRole = $user->pengurus->role->NAME;
        $userDPW = $user->pengurus->DPW ?? null;
        $options = [];
        if ($userRole === 'ADMIN_DPD') {
            $options = $this->getDPDEscalationOptions($konsultasi, $userDPW, $user->pengurus->DPD);
        } elseif ($userRole === 'ADMIN_DPW') {
            $options = $this->getDPWEscalationOptions($konsultasi, $userDPW);
        }
        return $options;
    }

    private function getDPDEscalationOptions($konsultasi, $userDPW, $userDPD): array
    {
        if ($konsultasi->TUJUAN !== 'DPD') return [];
        $options = [];
        if ($userDPW && $konsultasi->TUJUAN_SPESIFIK === $userDPD) {
            $otherDPDs = array_diff($this->getDPDInSameDPW($userDPW), [$userDPD]);
            if (!empty($otherDPDs)) {
                $options['DPD'] = ['label' => 'DPD Lain di Wilayah Sama', 'specific_options' => array_combine($otherDPDs, $otherDPDs)];
            }
        }
        if ($userDPW) {
            $options['DPW'] = ['label' => 'DPW', 'specific_options' => [$userDPW => $userDPW]];
        }
        return $options;
    }

    private function getDPWEscalationOptions($konsultasi, $userDPW): array
    {
        $options = [];
        if ($konsultasi->TUJUAN === 'DPD' && in_array($konsultasi->TUJUAN_SPESIFIK, $this->getDPDInSameDPW($userDPW))) {
            $otherDPDs = array_diff($this->getDPDInSameDPW($userDPW), [$konsultasi->TUJUAN_SPESIFIK]);
            if (!empty($otherDPDs)) {
                $options['DPD'] = ['label' => 'DPD Lain di Wilayah Sama', 'specific_options' => array_combine($otherDPDs, $otherDPDs)];
            }
            $options['DPW'] = ['label' => 'DPW', 'specific_options' => [$userDPW => $userDPW]];
        } elseif ($konsultasi->TUJUAN === 'DPW' && $konsultasi->TUJUAN_SPESIFIK === $userDPW) {
            $otherDPWs = $this->getOtherDPWs($userDPW);
            if (!empty($otherDPWs)) {
                $options['DPW'] = ['label' => 'DPW Lain', 'specific_options' => array_combine($otherDPWs, $otherDPWs)];
            }
            $options['DPP'] = ['label' => 'DPP', 'specific_options' => ['DPP' => 'DPP Pusat']];
            $dpdInSameDPW = $this->getDPDInSameDPW($userDPW);
            if (!empty($dpdInSameDPW)) {
                $options['DPD'] = ['label' => 'DPD di Wilayah Sendiri', 'specific_options' => array_combine($dpdInSameDPW, $dpdInSameDPW)];
            }
        }
        return $options;
    }

    private function validateSmartEscalation($konsultasi, $user, $escalateTo, $escalateToSpecific): array
    {
        $userRole = $user->pengurus->role->NAME;
        $userDPW = $user->pengurus->DPW ?? null;
        $userDPD = $user->pengurus->DPD ?? null;

        if ($escalateTo === $konsultasi->TUJUAN && $escalateToSpecific === $konsultasi->TUJUAN_SPESIFIK) {
            return ['allowed' => false, 'message' => 'Tidak dapat mengeskalasi ke tujuan yang sama.'];
        }

        if ($userRole === 'ADMIN_DPD') {
            if ($konsultasi->TUJUAN !== 'DPD' || $konsultasi->TUJUAN_SPESIFIK !== $userDPD) {
                return ['allowed' => false, 'message' => 'Anda hanya dapat mengeskalasi dari DPD Anda sendiri.'];
            }
            if (!in_array($escalateTo, ['DPD', 'DPW'])) {
                return ['allowed' => false, 'message' => 'DPD hanya bisa eskalasi ke DPD lain atau DPW sendiri.'];
            }
        }

        if ($userRole === 'ADMIN_DPW') {
            if ($konsultasi->TUJUAN === 'DPD' && !in_array($konsultasi->TUJUAN_SPESIFIK, $this->getDPDInSameDPW($userDPW))) {
                return ['allowed' => false, 'message' => 'Anda hanya dapat mengeskalasi DPD dari wilayah Anda.'];
            }
            if ($konsultasi->TUJUAN === 'DPW' && $konsultasi->TUJUAN_SPESIFIK !== $userDPW) {
                return ['allowed' => false, 'message' => 'Anda hanya dapat mengeskalasi dari DPW Anda sendiri.'];
            }
        }

        return ['allowed' => true, 'message' => ''];
    }

    private function getDPDInSameDPW($dpw): array
    {
        if (!$dpw) return [];
        return DB::table('t_sekar_pengurus')->where('DPW', $dpw)->whereNotNull('DPD')->where('DPD', '!=', '')->distinct()->pluck('DPD')->toArray();
    }

    private function getOtherDPWs($currentDPW): array
    {
        $query = DB::table('t_sekar_pengurus')->whereNotNull('DPW')->where('DPW', '!=', '')->distinct();
        if ($currentDPW) $query->where('DPW', '!=', $currentDPW);
        return $query->pluck('DPW')->toArray();
    }

    private function getEscalationLabel($level, $specific): string
    {
        $labels = ['DPD' => 'DPD', 'DPW' => 'DPW', 'DPP' => 'DPP', 'GENERAL' => 'SEKAR Pusat'];
        $label = $labels[$level] ?? $level;
        if ($specific && $specific !== $level) $label .= " ({$specific})";
        return $label;
    }

    private function isAdmin($user): bool
    {
        return $user->pengurus?->role && in_array($user->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']);
    }

    private function canCommentOnKonsultasi($user, $konsultasi): bool
    {
        if ($konsultasi->STATUS === 'CLOSED') return false;
        if ($konsultasi->N_NIK === $user->nik) return true;
        if ($this->isAdmin($user)) {
            // Cek apakah admin adalah handler aktif atau follower
            if ($konsultasi->isFollowedBy($user)) return true;
            $adminRole = $user->pengurus->role->NAME;
            if ($adminRole === 'ADM') return true;
            if ($adminRole === 'ADMIN_DPP' && $konsultasi->TUJUAN === 'DPP') return true;
            if ($adminRole === 'ADMIN_DPW' && $konsultasi->TUJUAN === 'DPW' && $konsultasi->TUJUAN_SPESIFIK === $user->pengurus->DPW) return true;
            if ($adminRole === 'ADMIN_DPD' && $konsultasi->TUJUAN === 'DPD' && $konsultasi->TUJUAN_SPESIFIK === $user->pengurus->DPD) return true;
        }
        return false;
    }

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
        if ($user->karyawan) return $user->karyawan;
        $commentBy = new \stdClass();
        $commentBy->N_NIK = $user->nik;
        $commentBy->V_NAMA_KARYAWAN = $user->name ?? 'Unknown User';
        return $commentBy;
    }
}