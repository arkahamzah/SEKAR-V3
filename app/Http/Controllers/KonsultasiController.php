<?php

namespace App\Http\Controllers;

use App\Models\Konsultasi;
use App\Models\KonsultasiKomentar;
use App\Models\Karyawan;
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
        
        $availableTargets = $this->getAvailableTargets($karyawan);
        $kategoriAdvokasi = $this->getKategoriAdvokasi();
        
        return view('konsultasi.create', compact('karyawan', 'availableTargets', 'kategoriAdvokasi'));
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
            DB::transaction(function () use ($validated, $user) {
                $konsultasi = $this->createKonsultasi($user, $validated);
                
                // Send email notification - PERBAIKAN
                $this->sendEmailNotification($konsultasi, 'new');
            });
            
            return redirect()->route('konsultasi.index')
                           ->with('success', ucfirst($validated['jenis']) . ' berhasil diajukan dan akan ditindaklanjuti.');
        } catch (\Exception $e) {
            Log::error('Error creating konsultasi: ' . $e->getMessage(), [
                'user_nik' => $user->nik,
                'validated_data' => $validated,
                'trace' => $e->getTraceAsString()
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
        $user = Auth::user();
        $konsultasi = Konsultasi::with(['karyawan', 'komentar.karyawan'])->findOrFail($id);
        
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
    public function comment(Request $request, $id)
    {
        $validated = $request->validate([
            'komentar' => 'required|string|max:1000'
        ]);
        
        $user = Auth::user();
        $konsultasi = Konsultasi::findOrFail($id);
        
        // Check permission
        if (!$this->canCommentOnKonsultasi($user, $konsultasi)) {
            return redirect()->back()->with('error', 'Anda tidak dapat menambahkan komentar pada konsultasi ini.');
        }
        
        try {
            DB::transaction(function () use ($validated, $id, $user, $konsultasi) {
                // Determine comment type
                $jenisKomentar = $this->isAdmin($user) ? 'ADMIN' : 'USER';
                
                $this->createKomentar($id, $user->nik, $validated['komentar'], $jenisKomentar);
                
                // Update konsultasi status if admin responds
                if ($jenisKomentar === 'ADMIN' && $konsultasi->STATUS === 'OPEN') {
                    $konsultasi->update([
                        'STATUS' => 'IN_PROGRESS',
                        'UPDATED_BY' => $user->nik,
                        'UPDATED_AT' => now()
                    ]);
                }
                
                // Send email notification - PERBAIKAN
                $this->sendEmailNotification($konsultasi, 'comment');
            });
            
            return redirect()->back()->with('success', 'Komentar berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error adding comment: ' . $e->getMessage(), [
                'user_nik' => $user->nik,
                'konsultasi_id' => $id,
                'trace' => $e->getTraceAsString()
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
        
        // Verify admin access
        if (!$this->isAdmin($user)) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya admin yang dapat menutup konsultasi.');
        }
        
        try {
            DB::transaction(function () use ($id, $user) {
                $konsultasi = Konsultasi::findOrFail($id);
                
                $konsultasi->update([
                    'STATUS' => 'CLOSED',
                    'CLOSED_BY' => $user->nik,
                    'CLOSED_AT' => now(),
                    'UPDATED_BY' => $user->nik,
                    'UPDATED_AT' => now()
                ]);
                
                // Add closure comment
                $this->createKomentar(
                    $id, 
                    $user->nik, 
                    'Konsultasi telah ditutup dan diselesaikan.',
                    'ADMIN'
                );
                
                // Send email notification - PERBAIKAN
                $this->sendEmailNotification($konsultasi, 'closed');
            });
            
            return redirect()->back()->with('success', 'Konsultasi berhasil ditutup.');
        } catch (\Exception $e) {
            Log::error('Error closing konsultasi: ' . $e->getMessage(), [
                'user_nik' => $user->nik,
                'konsultasi_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menutup konsultasi.');
        }
    }
    
    /**
     * Escalate konsultasi to higher level (admin only)
     */
    public function escalate(Request $request, $id)
    {
        $konsultasi = Konsultasi::findOrFail($id);
        
        // Tentukan opsi eskalasi yang valid berdasarkan level saat ini
        $validEscalationTargets = $this->getValidEscalationTargets($konsultasi->TUJUAN);
        
        if (empty($validEscalationTargets)) {
            return redirect()->back()->with('error', 'Konsultasi ini sudah berada di level tertinggi dan tidak dapat dieskalasi.');
        }
        
        $validated = $request->validate([
            'escalate_to' => 'required|in:' . implode(',', array_keys($validEscalationTargets)),
            'escalation_note' => 'required|string|max:500'
        ]);
        
        $user = Auth::user();
        
        // Verify admin access
        if (!$this->isAdmin($user)) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya admin yang dapat melakukan eskalasi.');
        }
        
        try {
            DB::transaction(function () use ($validated, $id, $user, $validEscalationTargets) {
                $konsultasi = Konsultasi::findOrFail($id);
                
                // Update konsultasi target
                $konsultasi->update([
                    'TUJUAN' => $validated['escalate_to'],
                    'STATUS' => 'OPEN', // Reset to open for higher level
                    'UPDATED_BY' => $user->nik,
                    'UPDATED_AT' => now()
                ]);
                
                // Add escalation comment
                $targetLabel = $validEscalationTargets[$validated['escalate_to']];
                $this->createKomentar(
                    $id, 
                    $user->nik, 
                    "ESKALASI KE {$targetLabel}: {$validated['escalation_note']}",
                    'ADMIN'
                );
                
                // Send email notification - PERBAIKAN
                $this->sendEmailNotification($konsultasi, 'escalate');
            });
            
            return redirect()->back()->with('success', 'Konsultasi berhasil dieskalasi ke level yang lebih tinggi.');
        } catch (\Exception $e) {
            Log::error('Error escalating konsultasi: ' . $e->getMessage(), [
                'user_nik' => $user->nik,
                'konsultasi_id' => $id,
                'escalate_to' => $validated['escalate_to'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat melakukan eskalasi.');
        }
    }
    
    /**
     * Get escalation options for AJAX (API endpoint)
     */
    public function getEscalationOptions($id)
    {
        $konsultasi = Konsultasi::findOrFail($id);
        $options = $this->getValidEscalationTargets($konsultasi->TUJUAN);
        
        return response()->json([
            'success' => true,
            'current_level' => $konsultasi->TUJUAN,
            'options' => $options,
            'can_escalate' => !empty($options)
        ]);
    }
    
    /**
     * Get konsultasi statistics for dashboard
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
     * Bulk actions for konsultasi (future enhancement)
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:close,escalate,delete',
            'konsultasi_ids' => 'required|array',
            'konsultasi_ids.*' => 'exists:t_konsultasi,ID'
        ]);
        
        $user = Auth::user();
        
        if (!$this->isAdmin($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya admin yang dapat melakukan bulk action.'
            ], 403);
        }
        
        try {
            $count = 0;
            
            foreach ($validated['konsultasi_ids'] as $id) {
                switch ($validated['action']) {
                    case 'close':
                        $konsultasi = Konsultasi::find($id);
                        if ($konsultasi && $konsultasi->STATUS !== 'CLOSED') {
                            $konsultasi->close($user->nik);
                            $count++;
                        }
                        break;
                    // Add other bulk actions as needed
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil memproses {$count} konsultasi."
            ]);
        } catch (\Exception $e) {
            Log::error('Error in bulk action: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses bulk action.'
            ], 500);
        }
    }
    
    /**
     * Get valid escalation targets based on current level
     */
    private function getValidEscalationTargets(string $currentLevel): array
    {
        switch($currentLevel) {
            case 'DPD':
                return [
                    'DPW' => 'DPW (Dewan Pengurus Wilayah)',
                    'DPP' => 'DPP (Dewan Pengurus Pusat)',
                    'GENERAL' => 'SEKAR Pusat'
                ];
            case 'DPW':
                return [
                    'DPP' => 'DPP (Dewan Pengurus Pusat)',
                    'GENERAL' => 'SEKAR Pusat'
                ];
            case 'DPP':
                return [
                    'GENERAL' => 'SEKAR Pusat'
                ];
            case 'GENERAL':
                // Sudah di level tertinggi
                return [];
            default:
                // Untuk kasus lain, berikan semua opsi kecuali current level
                $allOptions = [
                    'DPD' => 'DPD (Dewan Pengurus Daerah)',
                    'DPW' => 'DPW (Dewan Pengurus Wilayah)',
                    'DPP' => 'DPP (Dewan Pengurus Pusat)',
                    'GENERAL' => 'SEKAR Pusat'
                ];
                
                // Hapus current level dari opsi
                unset($allOptions[$currentLevel]);
                
                return $allOptions;
        }
    }
    
    /**
     * Get available targets based on employee location
     */
    private function getAvailableTargets(?Karyawan $karyawan): array
    {
        $targets = [
            'GENERAL' => 'SEKAR Pusat'
        ];
        
        if (!$karyawan) {
            return $targets;
        }
        
        $city = $karyawan->V_KOTA_GEDUNG;
        $dpwMapping = $this->getDpwMapping();
        $dpdMapping = $this->getDpdMapping();
        
        if (isset($dpdMapping[$city])) {
            $targets['DPD'] = $dpdMapping[$city];
        }
        
        if (isset($dpwMapping[$city])) {
            $targets['DPW'] = $dpwMapping[$city];
        }
        
        $targets['DPP'] = 'DPP Pusat';
        
        return $targets;
    }
    
    /**
     * Get DPW mapping configuration
     */
    private function getDpwMapping(): array
    {
        return [
            'BANDUNG' => 'DPW Jabar',
            'JAKARTA' => 'DPW Jakarta',
            'SURABAYA' => 'DPW Jatim',
            'MEDAN' => 'DPW Sumut',
            'MAKASSAR' => 'DPW Sulsel',
            'DENPASAR' => 'DPW Bali',
            'BALIKPAPAN' => 'DPW Kaltim',
        ];
    }
    
    /**
     * Get DPD mapping configuration
     */
    private function getDpdMapping(): array
    {
        return [
            'BANDUNG' => 'DPD Bandung',
            'JAKARTA' => 'DPD Jakarta Pusat',
            'SURABAYA' => 'DPD Surabaya',
            'MEDAN' => 'DPD Medan',
            'MAKASSAR' => 'DPD Makassar',
            'DENPASAR' => 'DPD Denpasar',
            'BALIKPAPAN' => 'DPD Balikpapan',
        ];
    }
    
    /**
     * Get available advocacy categories
     */
    private function getKategoriAdvokasi(): array
    {
        return [
            'Pelanggaran Hak Pekerja',
            'Masalah Kesejahteraan',
            'Diskriminasi',
            'Keselamatan Kerja',
            'Kondisi Kerja',
            'Upah dan Tunjangan',
            'Pelecehan di Tempat Kerja',
            'Pemutusan Hubungan Kerja',
            'Lainnya'
        ];
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
    
    /**
     * Send email notification - DIPERBAIKI DENGAN OPSI JOB DAN DIRECT
     */
    private function sendEmailNotification(Konsultasi $konsultasi, string $actionType): void
    {
        Log::info('=== STARTING EMAIL NOTIFICATION ===', [
            'konsultasi_id' => $konsultasi->ID,
            'action_type' => $actionType,
            'judul' => $konsultasi->JUDUL
        ]);
        
        try {
            // Get recipients
            $recipients = $this->getNotificationRecipients($konsultasi, $actionType);
            
            if (empty($recipients)) {
                Log::warning('No email recipients found', [
                    'konsultasi_id' => $konsultasi->ID,
                    'action_type' => $actionType
                ]);
                return;
            }
            
            Log::info('Recipients found', [
                'recipients' => $recipients,
                'count' => count($recipients),
                'konsultasi_id' => $konsultasi->ID
            ]);
            
            // PILIHAN 1: Gunakan Job (Recommended untuk production)
            if (config('queue.default') !== 'sync' && class_exists('App\Jobs\SendKonsultasiNotificationJob')) {
                Log::info('Dispatching email job', [
                    'queue_connection' => config('queue.default'),
                    'konsultasi_id' => $konsultasi->ID
                ]);
                
                SendKonsultasiNotificationJob::dispatch($konsultasi, $actionType, $recipients);
                
                Log::info('✅ EMAIL JOB DISPATCHED', [
                    'konsultasi_id' => $konsultasi->ID,
                    'action_type' => $actionType,
                    'queue' => config('queue.default')
                ]);
            } 
            // PILIHAN 2: Kirim langsung (Fallback atau untuk development)
            else {
                Log::info('Sending emails directly (sync mode)', [
                    'konsultasi_id' => $konsultasi->ID
                ]);
                
                $successCount = 0;
                $failCount = 0;
                
                foreach ($recipients as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        Log::warning('Invalid email format', ['email' => $email]);
                        $failCount++;
                        continue;
                    }
                    
                    try {
                        Mail::to($email)->send(new KonsultasiNotification($konsultasi, $actionType));
                        $successCount++;
                        
                        Log::info('✅ EMAIL SENT DIRECTLY', [
                            'to' => $email,
                            'konsultasi_id' => $konsultasi->ID,
                            'action_type' => $actionType
                        ]);
                        
                        // Small delay to avoid rate limiting
                        if (count($recipients) > 1) {
                            usleep(500000); // 0.5 second
                        }
                        
                    } catch (\Exception $e) {
                        $failCount++;
                        Log::error('❌ EMAIL SEND FAILED', [
                            'to' => $email,
                            'konsultasi_id' => $konsultasi->ID,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                Log::info('=== EMAIL DIRECT SENDING COMPLETED ===', [
                    'konsultasi_id' => $konsultasi->ID,
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                    'total_recipients' => count($recipients)
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('=== EMAIL NOTIFICATION FAILED ===', [
                'konsultasi_id' => $konsultasi->ID,
                'action_type' => $actionType,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Get notification recipients - DIPERBAIKI
     */
    private function getNotificationRecipients(Konsultasi $konsultasi, string $actionType): array
    {
        $recipients = [];
        
        Log::info('Getting email recipients', [
            'konsultasi_id' => $konsultasi->ID,
            'konsultasi_nik' => $konsultasi->N_NIK,
            'action_type' => $actionType
        ]);
        
        // 1. Add creator's email if available
        if ($konsultasi->karyawan && !empty($konsultasi->karyawan->V_EMAIL)) {
            $recipients[] = $konsultasi->karyawan->V_EMAIL;
            Log::info('Added creator email', [
                'email' => $konsultasi->karyawan->V_EMAIL,
                'konsultasi_id' => $konsultasi->ID
            ]);
        } else {
            Log::warning('Creator email not available', [
                'has_karyawan' => !is_null($konsultasi->karyawan),
                'karyawan_email' => $konsultasi->karyawan->V_EMAIL ?? null,
                'konsultasi_id' => $konsultasi->ID
            ]);
        }
        
        // 2. Add admin emails based on target level
        $adminEmail = $this->getAdminEmailByTarget($konsultasi->TUJUAN);
        if ($adminEmail && !in_array($adminEmail, $recipients)) {
            $recipients[] = $adminEmail;
            Log::info('Added target admin email', [
                'email' => $adminEmail,
                'target' => $konsultasi->TUJUAN,
                'konsultasi_id' => $konsultasi->ID
            ]);
        }
        
        // 3. Add fallback admin email
        $defaultAdminEmail = env('ADMIN_EMAIL');
        if ($defaultAdminEmail && !in_array($defaultAdminEmail, $recipients)) {
            $recipients[] = $defaultAdminEmail;
            Log::info('Added default admin email', [
                'email' => $defaultAdminEmail,
                'konsultasi_id' => $konsultasi->ID
            ]);
        }
        
        // 4. Add test email for development
        $testEmail = env('MAIL_FROM_ADDRESS', 'arkhamzahs@gmail.com');
        if ($testEmail && !in_array($testEmail, $recipients)) {
            $recipients[] = $testEmail;
            Log::info('Added test email', [
                'email' => $testEmail,
                'konsultasi_id' => $konsultasi->ID
            ]);
        }
        
        // 5. Filter and validate emails
        $validRecipients = [];
        foreach ($recipients as $email) {
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validRecipients[] = $email;
            } else {
                Log::warning('Invalid email filtered out', [
                    'email' => $email,
                    'konsultasi_id' => $konsultasi->ID
                ]);
            }
        }
        
        $finalRecipients = array_unique($validRecipients);
        
        Log::info('Final recipients prepared', [
            'recipients' => $finalRecipients,
            'count' => count($finalRecipients),
            'konsultasi_id' => $konsultasi->ID
        ]);
        
        return $finalRecipients;
    }
    
    /**
     * Get admin email by target level
     */
    private function getAdminEmailByTarget(string $target): ?string
    {
        return match($target) {
            'DPD' => env('DPD_ADMIN_EMAIL'),
            'DPW' => env('DPW_ADMIN_EMAIL'),
            'DPP' => env('DPP_ADMIN_EMAIL'),
            'GENERAL' => env('GENERAL_ADMIN_EMAIL'),
            default => env('ADMIN_EMAIL', 'arkhamzahs@gmail.com')
        };
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
     * Get admin level for hierarchy
     */
    private function getAdminLevel($user): int
    {
        if (!$this->isAdmin($user)) {
            return 0;
        }
        
        $role = $user->pengurus->role->NAME;
        
        return match($role) {
            'ADM' => 4,           // Highest level
            'ADMIN_DPP' => 3,     // National level
            'ADMIN_DPW' => 2,     // Regional level
            'ADMIN_DPD' => 1,     // Local level
            default => 0
        };
    }
    
    /**
     * Filter konsultasi by admin level
     */
    private function filterByAdminLevel($query, int $adminLevel)
    {
        // Admin can see konsultasi based on their level
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
     * Check if user can access specific konsultasi
     */
    private function canAccessKonsultasi($user, Konsultasi $konsultasi): bool
    {
        // User can access their own konsultasi
        if ($user->nik === $konsultasi->N_NIK) {
            return true;
        }
        
        // Admin can access based on their level and konsultasi target
        if ($this->isAdmin($user)) {
            $adminLevel = $this->getAdminLevel($user);
            
            return match($adminLevel) {
                4 => true, // ADM can access all
                3 => in_array($konsultasi->TUJUAN, ['DPP', 'GENERAL']),
                2 => in_array($konsultasi->TUJUAN, ['DPW', 'DPP', 'GENERAL']),
                1 => in_array($konsultasi->TUJUAN, ['DPD', 'DPW', 'DPP', 'GENERAL']),
                default => false
            };
        }
        
        return false;
    }
    
    /**
     * Check if user can comment on konsultasi
     */
    private function canCommentOnKonsultasi($user, Konsultasi $konsultasi): bool
    {
        // Konsultasi must not be closed
        if ($konsultasi->STATUS === 'CLOSED') {
            return false;
        }
        
        // User can comment on their own konsultasi
        if ($user->nik === $konsultasi->N_NIK) {
            return true;
        }
        
        // Admin can comment if they can access the konsultasi
        return $this->canAccessKonsultasi($user, $konsultasi);
    }
}