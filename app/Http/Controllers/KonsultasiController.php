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
        
        return view('konsultasi.create', compact(
            'karyawan', 'availableTargets', 'kategoriAdvokasi'));
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
            
            // ===== SETELAH DB TRANSACTION SELESAI =====
            // Jalankan notifications di luar transaction
            
            try {
                // 1. Web notification dulu (prioritas untuk UI)
                $notificationService = new \App\Services\NotificationService();
                $notificationService->notifyNewKonsultasi($konsultasi);
                
                Log::info('Web notification sent for new konsultasi', [
                    'konsultasi_id' => $konsultasi->ID,
                    'user_nik' => $user->nik
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send web notification for new konsultasi (non-critical)', [
                    'konsultasi_id' => $konsultasi->ID,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            try {
                // 2. Email notification terakhir (bisa fail tanpa mengganggu)
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
            });
            
            // ===== SETELAH DB TRANSACTION SELESAI =====
            // Baru jalankan notifications di luar transaction
            
            try {
                // 1. Send web notification DULU (lebih penting untuk UI)
                $notificationService = new \App\Services\NotificationService();
                $isAdminComment = $this->isAdmin($user);
                $commentBy = $this->getCommentByData($user);
                $notificationService->notifyNewComment($konsultasi, $commentBy, $isAdminComment);
                
                Log::info('Web notification sent successfully for comment', [
                    'konsultasi_id' => $konsultasi->ID,
                    'comment_by' => $user->nik,
                    'is_admin' => $isAdminComment
                ]);
            } catch (\Exception $e) {
                // Jangan biarkan error web notification mengganggu flow
                Log::error('Failed to send web notification for comment (non-critical)', [
                    'konsultasi_id' => $konsultasi->ID,
                    'user_nik' => $user->nik,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            try {
                // 2. Send email notification TERAKHIR (bisa fail tanpa mengganggu)
                $this->sendEmailNotification($konsultasi, 'comment');
                
                Log::info('Email notification sent successfully for comment', [
                    'konsultasi_id' => $konsultasi->ID
                ]);
            } catch (\Exception $e) {
                // Email gagal tidak apa-apa, yang penting web notification sukses
                Log::error('Failed to send email notification for comment (non-critical)', [
                    'konsultasi_id' => $konsultasi->ID,
                    'error' => $e->getMessage()
                ]);
            }
            
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
            $konsultasi = null;
            
            DB::transaction(function () use ($id, $user, &$konsultasi) {
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
            });
            
            // ===== SETELAH DB TRANSACTION SELESAI =====
            
            try {
                // 1. Web notification dulu
                $notificationService = new \App\Services\NotificationService();
                $notificationService->notifyKonsultasiClosed($konsultasi);
                
                Log::info('Web notification sent for closed konsultasi', [
                    'konsultasi_id' => $konsultasi->ID,
                    'closed_by' => $user->nik
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send web notification for closed konsultasi (non-critical)', [
                    'konsultasi_id' => $konsultasi->ID,
                    'error' => $e->getMessage()
                ]);
            }
            
            try {
                // 2. Email notification terakhir
                $this->sendEmailNotification($konsultasi, 'closed');
                
                Log::info('Email notification sent for closed konsultasi', [
                    'konsultasi_id' => $konsultasi->ID
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send email notification for closed konsultasi (non-critical)', [
                    'konsultasi_id' => $konsultasi->ID,
                    'error' => $e->getMessage()
                ]);
            }
            
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
            DB::transaction(function () use ($validated, $id, $user, $validEscalationTargets, &$konsultasi) {
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
            });
            
            // ===== SETELAH DB TRANSACTION SELESAI =====
            
            try {
                // 1. Web notification dulu
                $notificationService = new \App\Services\NotificationService();
                $notificationService->notifyEscalation($konsultasi, $validated['escalate_to']);
                
                Log::info('Web notification sent for escalated konsultasi', [
                    'konsultasi_id' => $konsultasi->ID,
                    'escalated_by' => $user->nik,
                    'escalate_to' => $validated['escalate_to']
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send web notification for escalated konsultasi (non-critical)', [
                    'konsultasi_id' => $konsultasi->ID,
                    'error' => $e->getMessage()
                ]);
            }
            
            try {
                // 2. Email notification terakhir
                $this->sendEmailNotification($konsultasi, 'escalate');
                
                Log::info('Email notification sent for escalated konsultasi', [
                    'konsultasi_id' => $konsultasi->ID
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send email notification for escalated konsultasi (non-critical)', [
                    'konsultasi_id' => $konsultasi->ID,
                    'error' => $e->getMessage()
                ]);
            }
            
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
     * Bulk action for konsultasi (future enhancement)
     */
    public function bulkAction(Request $request)
    {
        $user = Auth::user();
        
        if (!$this->isAdmin($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $validated = $request->validate([
            'action' => 'required|in:close,escalate,delete',
            'konsultasi_ids' => 'required|array',
            'konsultasi_ids.*' => 'exists:t_konsultasi,ID'
        ]);
        
        // Implementation for bulk actions
        // This is a placeholder for future enhancement
        
        return response()->json([
            'success' => true,
            'message' => 'Bulk action completed'
        ]);
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
     * Get comment by data (replaces missing getKaryawanData method)
     */
    private function getCommentByData($user)
    {
        try {
            // Try to get karyawan data
            if ($user->karyawan) {
                return $user->karyawan;
            }
            
            // If no karyawan relationship, create a dummy object
            $commentBy = new \stdClass();
            $commentBy->N_NIK = $user->nik;
            $commentBy->V_NAMA_KARYAWAN = $user->name;
            
            return $commentBy;
            
        } catch (\Exception $e) {
            Log::warning('Could not get karyawan data, using user data', [
                'user_nik' => $user->nik,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to basic user data
            $commentBy = new \stdClass();
            $commentBy->N_NIK = $user->nik;
            $commentBy->V_NAMA_KARYAWAN = $user->name ?? 'Unknown User';
            
            return $commentBy;
        }
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
                            usleep(500000); // 0.5 second delay
                        }
                        
                    } catch (\Exception $e) {
                        $failCount++;
                        Log::error('❌ EMAIL FAILED', [
                            'to' => $email,
                            'konsultasi_id' => $konsultasi->ID,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                Log::info('✅ EMAIL DIRECT SENDING COMPLETED', [
                    'konsultasi_id' => $konsultasi->ID,
                    'total_recipients' => count($recipients),
                    'success_count' => $successCount,
                    'fail_count' => $failCount
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('❌ EMAIL NOTIFICATION FAILED', [
                'konsultasi_id' => $konsultasi->ID,
                'action_type' => $actionType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't re-throw the exception - email failure shouldn't break the flow
        }
    }
    
    /**
     * Get notification recipients based on konsultasi and action type
     */
    private function getNotificationRecipients(Konsultasi $konsultasi, string $actionType): array
    {
        $recipients = [];
        
        try {
            Log::info('Getting notification recipients', [
                'konsultasi_id' => $konsultasi->ID,
                'action_type' => $actionType,
                'target' => $konsultasi->TUJUAN
            ]);
            
            // 1. Add user email (konsultasi owner)
            if ($actionType !== 'new') {
                $userEmail = \App\Models\User::where('nik', $konsultasi->N_NIK)->value('email');
                if ($userEmail && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                    $recipients[] = $userEmail;
                    Log::info('Added user email', [
                        'email' => $userEmail,
                        'konsultasi_id' => $konsultasi->ID
                    ]);
                }
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
            
        } catch (\Exception $e) {
            Log::error('Error getting notification recipients', [
                'konsultasi_id' => $konsultasi->ID,
                'action_type' => $actionType,
                'error' => $e->getMessage()
            ]);
            
            // Return fallback recipient
            return [env('MAIL_FROM_ADDRESS', 'arkhamzahs@gmail.com')];
        }
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
     * Get admin level for user
     */
    private function getAdminLevel($user): int
    {
        if (!$user->pengurus || !$user->pengurus->role) {
            return 0;
        }
        
        return match($user->pengurus->role->NAME) {
            'ADM' => 4,
            'ADMIN_DPP' => 3,
            'ADMIN_DPW' => 2,
            'ADMIN_DPD' => 1,
            default => 0
        };
    }
    
    /**
     * Filter query by admin level
     */
    private function filterByAdminLevel($query, int $adminLevel)
    {
        // Implementation depends on your business logic
        // This is a placeholder
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
        // Owner can comment, admin can comment, but not on closed konsultasi
        if ($konsultasi->STATUS === 'CLOSED') {
            return false;
        }
        
        return $konsultasi->N_NIK === $user->nik || $this->isAdmin($user);
    }
    
    /**
     * Get available targets for konsultasi
     */
    private function getAvailableTargets($karyawan): array
    {
        // Implementation depends on your business logic
        return [
            'DPD' => 'DPD (Dewan Pengurus Daerah)',
            'DPW' => 'DPW (Dewan Pengurus Wilayah)',
            'DPP' => 'DPP (Dewan Pengurus Pusat)',
            'GENERAL' => 'General Admin'
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
            'Lainnya'
        ];
    }
    
    /**
     * Get valid escalation targets
     */
    private function getValidEscalationTargets(string $currentTarget): array
    {
        return match($currentTarget) {
            'DPD' => [
                'DPW' => 'DPW (Dewan Pengurus Wilayah)',
                'DPP' => 'DPP (Dewan Pengurus Pusat)'
            ],
            'DPW' => [
                'DPP' => 'DPP (Dewan Pengurus Pusat)'
            ],
            'GENERAL' => [
                'DPP' => 'DPP (Dewan Pengurus Pusat)'
            ],
            default => [] // DPP is highest level
        };
    }
}