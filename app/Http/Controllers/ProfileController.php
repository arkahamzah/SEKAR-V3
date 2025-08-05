<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\Iuran;
use App\Models\IuranHistory;
use App\Models\IuranBulanan;
use App\Models\Params;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProfileController extends Controller
{
    /**
     * Display user profile with iuran information
     */
    public function index()
    {
        $user = Auth::user();
        $profileData = $this->getProfileData($user);

        return view('profile.index', $profileData);
    }

    /**
     * Update user profile picture
     */
    public function updateProfilePicture(Request $request)
    {
        $validated = $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
        ], [
            'profile_picture.required' => 'Foto profil wajib dipilih.',
            'profile_picture.image' => 'File harus berupa gambar.',
            'profile_picture.mimes' => 'Format gambar harus JPEG, PNG, atau JPG.',
            'profile_picture.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user, $request) {
                // Delete old profile picture if exists
                if ($user->profile_picture && Storage::disk('public')->exists('profile-pictures/' . $user->profile_picture)) {
                    Storage::disk('public')->delete('profile-pictures/' . $user->profile_picture);
                }

                // Store new profile picture
                $file = $request->file('profile_picture');
                $fileName = time() . '_' . $user->nik . '.' . $file->getClientOriginalExtension();
                $file->storeAs('profile-pictures', $fileName, 'public');

                // Update user record
                $user->update([
                    'profile_picture' => $fileName
                ]);

                Log::info('Profile picture updated', [
                    'nik' => $user->nik,
                    'name' => $user->name,
                    'filename' => $fileName,
                    'timestamp' => now()
                ]);
            });

            return redirect()->route('profile.index')
                           ->with('success', 'Foto profil berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Profile picture update failed', [
                'nik' => $user->nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('profile.index')
                           ->with('error', 'Terjadi kesalahan saat memperbarui foto profil. Silakan coba lagi.');
        }
    }

    /**
     * Delete user profile picture
     */
    public function deleteProfilePicture()
    {
        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                // Delete file from storage
                if ($user->profile_picture && Storage::disk('public')->exists('profile-pictures/' . $user->profile_picture)) {
                    Storage::disk('public')->delete('profile-pictures/' . $user->profile_picture);
                }

                // Update user record
                $user->update([
                    'profile_picture' => null
                ]);

                Log::info('Profile picture deleted', [
                    'nik' => $user->nik,
                    'name' => $user->name,
                    'timestamp' => now()
                ]);
            });

            return redirect()->route('profile.index')
                           ->with('success', 'Foto profil berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Profile picture deletion failed', [
                'nik' => $user->nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('profile.index')
                           ->with('error', 'Terjadi kesalahan saat menghapus foto profil. Silakan coba lagi.');
        }
    }

    /**
     * Update iuran sukarela for authenticated user - UPDATED: Allow multiple edits
     */
    public function updateIuranSukarela(Request $request)
    {
        $validated = $request->validate([
            'iuran_sukarela' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $newAmount = (int)$validated['iuran_sukarela'];

        try {
            $result = $this->processIuranSukarelaUpdate($user, $newAmount);

            return redirect()->route('profile.index')
                           ->with($result['status'], $result['message']);
        } catch (\Exception $e) {
            return redirect()->route('profile.index')
                           ->with('error', 'Terjadi kesalahan saat memproses perubahan iuran.');
        }
    }

    /**
     * Cancel pending iuran change - UPDATED: Mark as cancelled instead of delete
     */
    public function cancelIuranChange()
    {
        $user = Auth::user();

        try {
            $pendingChange = $this->getPendingIuranChange($user->nik);

            if (!$pendingChange) {
                return redirect()->route('profile.index')
                               ->with('warning', 'Tidak ada perubahan iuran yang sedang pending.');
            }

            DB::transaction(function () use ($pendingChange, $user) {
                // Mark as cancelled instead of deleting to preserve history
                $pendingChange->update([
                    'STATUS_PROSES' => 'DIBATALKAN',
                    'KETERANGAN' => 'Dibatalkan oleh anggota - ' . $pendingChange->KETERANGAN
                ]);

                Log::info('Iuran change cancelled by user', [
                    'nik' => $user->nik,
                    'name' => $user->name,
                    'cancelled_amount' => $pendingChange->NOMINAL_BARU,
                    'timestamp' => now()
                ]);
            });

            return redirect()->route('profile.index')
                           ->with('success', 'Perubahan iuran sukarela berhasil dibatalkan dan disimpan ke riwayat.');

        } catch (\Exception $e) {
            Log::error('Cancel iuran change failed', [
                'nik' => $user->nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('profile.index')
                           ->with('error', 'Terjadi kesalahan saat membatalkan perubahan iuran.');
        }
    }

    /**
     * Update user email
     */
    public function updateEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'current_password' => 'required|string',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'current_password.required' => 'Password saat ini wajib diisi untuk konfirmasi.',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->route('profile.index')
                           ->withErrors(['current_password' => 'Password saat ini tidak benar.'])
                           ->withInput();
        }

        try {
            DB::transaction(function () use ($user, $validated) {
                $oldEmail = $user->email;

                // Update email
                $user->update([
                    'email' => $validated['email']
                ]);

                // Log email change
                Log::info('User email updated', [
                    'nik' => $user->nik,
                    'name' => $user->name,
                    'old_email' => $oldEmail,
                    'new_email' => $validated['email'],
                    'timestamp' => now()
                ]);

                // Send confirmation email to new email
                $this->sendEmailChangeConfirmation($user, $oldEmail);
            });

            return redirect()->route('profile.index')
                           ->with('success', 'Email berhasil diperbarui. Email konfirmasi telah dikirim ke alamat baru Anda.');

        } catch (\Exception $e) {
            Log::error('Email update failed', [
                'nik' => $user->nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('profile.index')
                           ->with('error', 'Terjadi kesalahan saat memperbarui email. Silakan coba lagi.');
        }
    }

    /**
     * Get all profile data for user
     */
    private function getProfileData(User $user)
    {
        $karyawan = $this->getKaryawanData($user->nik);
        $iuran = $this->getIuranData($user->nik);
        $iuranWajib = $this->getIuranWajib();
        $iuranSukarela = $iuran ? (int)$iuran->IURAN_SUKARELA : 0;

        $pendingChange = $this->getPendingIuranChange($user->nik);
        // FIXED: effectiveIuranSukarela should show current active amount, not pending
        $effectiveIuranSukarela = $iuranSukarela; // Always show current active amount

        // Calculate total per month
        $totalIuranPerBulan = $iuranWajib + $effectiveIuranSukarela;

        // NEW: Calculate total paid iuran
        $totalIuranTerbayar = $this->getTotalIuranTerbayar($user->nik);
        $bulanTerbayar = $this->getBulanTerbayar($user->nik);
        $bulanTunggakan = $this->getBulanTunggakan($user->nik);

        return [
            'user' => $user,
            'karyawan' => $karyawan,
            'iuran' => $iuran,
            'iuranWajib' => $iuranWajib,
            'iuranSukarela' => $iuranSukarela,
            'effectiveIuranSukarela' => $effectiveIuranSukarela,
            'totalIuranPerBulan' => $totalIuranPerBulan,
            'totalIuranTerbayar' => $totalIuranTerbayar,
            'bulanTerbayar' => $bulanTerbayar,
            'bulanTunggakan' => $bulanTunggakan,
            'joinDate' => $user->created_at,
            'pendingChange' => $pendingChange,
            'isDummyEmail' => $this->isDummyEmail($user->email),
        ];
    }

    /**
     * Get karyawan data
     */
    private function getKaryawanData(string $nik)
    {
        return Karyawan::where('N_NIK', $nik)->first();
    }

    /**
     * Get iuran data - HANDLE DUPLICATES by getting the latest with highest value
     */
    private function getIuranData(string $nik)
    {
        // Get the record with highest IURAN_SUKARELA value, then latest created
        return Iuran::where('N_NIK', $nik)
                   ->orderByRaw('CAST(IURAN_SUKARELA AS UNSIGNED) DESC')
                   ->orderBy('CREATED_AT', 'DESC')
                   ->first();
    }

    /**
     * Get current iuran wajib amount
     */
    private function getIuranWajib()
    {
        $params = Params::where('IS_AKTIF', '1')
                       ->where('TAHUN', date('Y'))
                       ->first();

        return $params ? (int)$params->NOMINAL_IURAN_WAJIB : 25000; // Default fallback sesuai database
    }

    /**
     * Get pending iuran change
     */
    private function getPendingIuranChange(string $nik)
    {
        return IuranHistory::where('N_NIK', $nik)
                          ->where('STATUS_PROSES', 'PENDING')
                          ->latest('CREATED_AT')
                          ->first();
    }

    /**
     * NEW: Get total iuran yang sudah terbayar
     */
    private function getTotalIuranTerbayar(string $nik)
    {
        return (int) IuranBulanan::where('N_NIK', $nik)
                                ->where('STATUS', 'LUNAS')
                                ->sum('TOTAL_IURAN');
    }

    /**
     * NEW: Get jumlah bulan yang sudah terbayar
     */
    private function getBulanTerbayar(string $nik)
    {
        return IuranBulanan::where('N_NIK', $nik)
                          ->where('STATUS', 'LUNAS')
                          ->count();
    }

    /**
     * NEW: Get jumlah bulan yang belum terbayar/tunggakan
     */
    private function getBulanTunggakan(string $nik)
    {
        return IuranBulanan::where('N_NIK', $nik)
                          ->whereIn('STATUS', ['BELUM_BAYAR', 'TERLAMBAT'])
                          ->count();
    }

    /**
     * Process iuran sukarela update - UPDATED: Allow multiple edits by updating existing pending record
     */
    private function processIuranSukarelaUpdate(User $user, int $newAmount)
    {
        $currentIuran = $this->getIuranData($user->nik);
        $currentAmount = $currentIuran ? (int)$currentIuran->IURAN_SUKARELA : 0;

        // Check if there's already a pending change
        $pendingChange = $this->getPendingIuranChange($user->nik);

        if ($pendingChange) {
            // If new amount is same as current pending amount, no change needed
            if ($newAmount == (int)$pendingChange->NOMINAL_BARU) {
                return [
                    'status' => 'info',
                    'message' => 'Nominal iuran sukarela tidak berubah dari yang sedang pending.'
                ];
            }

            // Update existing pending record instead of creating new one
            return $this->updatePendingIuranChange($user, $pendingChange, $newAmount);
        }

        // Check if amount is the same as current
        if ($newAmount == $currentAmount) {
            return [
                'status' => 'info',
                'message' => 'Nominal iuran sukarela tidak berubah.'
            ];
        }

        // Create new pending change
        return $this->createNewIuranChange($user, $newAmount, $currentAmount);
    }

    /**
     * Update existing pending iuran change - UPDATED: Save previous change to history
     */
    private function updatePendingIuranChange(User $user, IuranHistory $pendingChange, int $newAmount)
    {
        return DB::transaction(function () use ($user, $pendingChange, $newAmount) {
            $oldPendingAmount = (int)$pendingChange->NOMINAL_BARU;

            // First, mark the current pending record as "DIBATALKAN" to preserve history
            $pendingChange->update([
                'STATUS_PROSES' => 'DIBATALKAN',
                'KETERANGAN' => 'Dibatalkan karena ada perubahan baru - ' . $pendingChange->KETERANGAN
            ]);

            // Calculate new dates based on current time
            $tglPerubahan = now();
            $tglProses = $tglPerubahan->copy()->addMonth()->day(20);
            $tglImplementasi = $tglPerubahan->copy()->addMonths(2)->day(1);

            // Create new pending record
            IuranHistory::create([
                'N_NIK' => $user->nik,
                'JENIS' => 'SUKARELA',
                'NOMINAL_LAMA' => $oldPendingAmount, // Previous pending amount becomes the "old" amount
                'NOMINAL_BARU' => $newAmount,
                'STATUS_PROSES' => 'PENDING',
                'TGL_PERUBAHAN' => $tglPerubahan,
                'TGL_PROSES' => $tglProses,
                'TGL_IMPLEMENTASI' => $tglImplementasi,
                'KETERANGAN' => 'Perubahan nominal iuran sukarela (mengganti pengajuan sebelumnya)',
                'CREATED_BY' => $user->nik,
                'CREATED_AT' => now()
            ]);

            Log::info('Pending iuran change updated with history preserved', [
                'nik' => $user->nik,
                'name' => $user->name,
                'old_pending_amount' => $oldPendingAmount,
                'new_pending_amount' => $newAmount,
                'timestamp' => now()
            ]);

            return [
                'status' => 'success',
                'message' => 'Perubahan iuran sukarela berhasil diperbarui. Pengajuan sebelumnya telah disimpan ke riwayat.'
            ];
        });
    }

    /**
     * Create new iuran change - NEW METHOD
     */
    private function createNewIuranChange(User $user, int $newAmount, int $currentAmount)
    {
        return DB::transaction(function () use ($user, $newAmount, $currentAmount) {
            // Calculate dates
            $tglPerubahan = now();
            $tglProses = $tglPerubahan->copy()->addMonth()->day(20);
            $tglImplementasi = $tglPerubahan->copy()->addMonths(2)->day(1);

            // Create history record
            IuranHistory::create([
                'N_NIK' => $user->nik,
                'JENIS' => 'SUKARELA',
                'NOMINAL_LAMA' => $currentAmount,
                'NOMINAL_BARU' => $newAmount,
                'STATUS_PROSES' => 'PENDING',
                'TGL_PERUBAHAN' => $tglPerubahan,
                'TGL_PROSES' => $tglProses,
                'TGL_IMPLEMENTASI' => $tglImplementasi,
                'KETERANGAN' => 'Pengajuan perubahan iuran sukarela melalui portal anggota',
                'CREATED_BY' => $user->nik,
                'CREATED_AT' => now()
            ]);

            Log::info('New iuran change requested', [
                'nik' => $user->nik,
                'name' => $user->name,
                'old_amount' => $currentAmount,
                'new_amount' => $newAmount,
                'timestamp' => now()
            ]);

            return [
                'status' => 'success',
                'message' => 'Perubahan iuran sukarela berhasil diajukan dan akan diproses oleh admin.'
            ];
        });
    }

    /**
     * Get iuran history via AJAX for modal
     */
    public function getIuranHistory(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 1);

        $iuranHistory = IuranHistory::where('N_NIK', $user->nik)
                                  ->orderBy('CREATED_AT', 'DESC')
                                  ->paginate(5, ['*'], 'page', $page);

        // Transform data for JSON response
        $historyData = $iuranHistory->map(function ($history) {
            return [
                'id' => $history->ID,
                'jenis' => $history->JENIS,
                'nominalLama' => (int)$history->NOMINAL_LAMA,
                'nominalBaru' => (int)$history->NOMINAL_BARU,
                'status' => $history->STATUS_PROSES,
                'statusColor' => $history->status_color,
                'statusText' => $history->status_text,
                'keterangan' => $history->KETERANGAN,
                'tglPerubahan' => $history->TGL_PERUBAHAN,
                'tglProses' => $history->TGL_PROSES,
                'tglImplementasi' => $history->TGL_IMPLEMENTASI,
                'createdAt' => $history->CREATED_AT,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $historyData,
                'currentPage' => $iuranHistory->currentPage(),
                'totalPages' => $iuranHistory->lastPage(),
                'totalItems' => $iuranHistory->total(),
                'firstItem' => $iuranHistory->firstItem(),
                'lastItem' => $iuranHistory->lastItem(),
                'hasMorePages' => $iuranHistory->hasMorePages(),
                'onFirstPage' => $iuranHistory->onFirstPage(),
            ]
        ]);
    }

    /**
     * NEW: Get payment history via AJAX for modal
     */
    public function getPaymentHistory(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 1);

        $paymentHistory = IuranBulanan::where('N_NIK', $user->nik)
                                    ->orderBy('TAHUN', 'DESC')
                                    ->orderBy('BULAN', 'DESC')
                                    ->paginate(12, ['*'], 'page', $page);

        // Transform data for JSON response
        $historyData = $paymentHistory->map(function ($payment) {
            return [
                'id' => $payment->ID,
                'tahun' => $payment->TAHUN,
                'bulan' => $payment->BULAN,
                'bulanNama' => $this->getBulanNama($payment->BULAN),
                'iuranWajib' => (int)$payment->IURAN_WAJIB,
                'iuranSukarela' => (int)$payment->IURAN_SUKARELA,
                'totalIuran' => (int)$payment->TOTAL_IURAN,
                'status' => $payment->STATUS,
                'statusText' => $this->getStatusText($payment->STATUS),
                'statusColor' => $this->getStatusColor($payment->STATUS),
                'tglBayar' => $payment->TGL_BAYAR,
                'createdAt' => $payment->CREATED_AT,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $historyData,
                'currentPage' => $paymentHistory->currentPage(),
                'totalPages' => $paymentHistory->lastPage(),
                'totalItems' => $paymentHistory->total(),
                'firstItem' => $paymentHistory->firstItem(),
                'lastItem' => $paymentHistory->lastItem(),
                'hasMorePages' => $paymentHistory->hasMorePages(),
                'onFirstPage' => $paymentHistory->onFirstPage(),
            ]
        ]);
    }

    /**
     * Helper method to get month name in Indonesian
     */
    private function getBulanNama(string $bulan)
    {
        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        return isset($months[$bulan]) ? $months[$bulan] : 'Unknown';
    }

    /**
     * Helper method to get status text
     */
    private function getStatusText(string $status)
    {
        switch ($status) {
            case 'LUNAS':
                return 'Lunas';
            case 'BELUM_BAYAR':
                return 'Belum Bayar';
            case 'TERLAMBAT':
                return 'Terlambat';
            default:
                return 'Unknown';
        }
    }

    /**
     * Helper method to get status color
     */
    private function getStatusColor(string $status)
    {
        switch ($status) {
            case 'LUNAS':
                return 'green';
            case 'BELUM_BAYAR':
                return 'yellow';
            case 'TERLAMBAT':
                return 'red';
            default:
                return 'gray';
        }
    }

    /**
     * Check if email is dummy email
     */
    private function isDummyEmail(string $email)
    {
        return str_ends_with($email, '@sekar.local') || strpos($email, '@sekar.local') !== false;
    }

    /**
     * Send email change confirmation
     */
    private function sendEmailChangeConfirmation(User $user, string $oldEmail)
    {
        try {
            // Implementation would depend on your mail configuration
            // This is a placeholder for actual email sending logic
            Log::info('Email change confirmation should be sent', [
                'nik' => $user->nik,
                'old_email' => $oldEmail,
                'new_email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email change confirmation', [
                'nik' => $user->nik,
                'error' => $e->getMessage()
            ]);
        }
    }
}