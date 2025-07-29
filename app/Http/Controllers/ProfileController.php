<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\Iuran;
use App\Models\IuranHistory;
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
     * Update iuran sukarela for authenticated user
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
    private function getProfileData(User $user): array
    {
        $karyawan = $this->getKaryawanData($user->nik);
        $iuran = $this->getIuranData($user->nik); // This will get the latest/highest value
        $iuranWajib = $this->getIuranWajib();
        $iuranSukarela = $iuran ? (int)$iuran->IURAN_SUKARELA : 0;
        
        $pendingChange = $this->getPendingIuranChange($user->nik);
        $effectiveIuranSukarela = $pendingChange ? (int)$pendingChange->NOMINAL_BARU : $iuranSukarela;
        
        $iuranCalculations = $this->calculateIuranTotals($user, $iuranWajib, $effectiveIuranSukarela);
        $iuranHistory = $this->getIuranHistory($user->nik);

        return [
            'user' => $user,
            'karyawan' => $karyawan,
            'iuran' => $iuran,
            'iuranWajib' => $iuranWajib,
            'iuranSukarela' => $iuranSukarela,
            'effectiveIuranSukarela' => $effectiveIuranSukarela,
            'totalIuranPerBulan' => $iuranCalculations['totalPerBulan'],
            'totalIuran' => $iuranCalculations['totalPaid'],
            'joinDate' => $user->created_at,
            'iuranHistory' => $iuranHistory,
            'pendingChange' => $pendingChange,
            'isDummyEmail' => $this->isDummyEmail($user->email),
        ];
    }

    /**
     * Get karyawan data
     */
    private function getKaryawanData(string $nik): ?Karyawan
    {
        return Karyawan::where('N_NIK', $nik)->first();
    }

    /**
     * Get iuran data - HANDLE DUPLICATES by getting the latest with highest value
     */
    private function getIuranData(string $nik): ?Iuran
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
    private function getIuranWajib(): int
    {
        $params = Params::where('IS_AKTIF', '1')
                       ->where('TAHUN', date('Y'))
                       ->first();
        
        return $params ? (int)$params->NOMINAL_IURAN_WAJIB : 25000; // Default fallback sesuai database
    }

    /**
     * Get pending iuran change - FIXED: gunakan STATUS_PROSES bukan STATUS
     */
    private function getPendingIuranChange(string $nik): ?IuranHistory
    {
        return IuranHistory::where('N_NIK', $nik)
                          ->where('STATUS_PROSES', 'PENDING') // FIXED: gunakan STATUS_PROSES
                          ->latest('CREATED_AT')
                          ->first();
    }

    /**
     * Process iuran sukarela update
     */
    private function processIuranSukarelaUpdate(User $user, int $newAmount): array
    {
        $currentIuran = $this->getIuranData($user->nik);
        $currentAmount = $currentIuran ? (int)$currentIuran->IURAN_SUKARELA : 0;

        // Check if there's already a pending change
        $pendingChange = $this->getPendingIuranChange($user->nik);
        if ($pendingChange) {
            return [
                'status' => 'warning',
                'message' => 'Anda masih memiliki perubahan iuran yang sedang menunggu persetujuan. Silakan tunggu hingga diproses.'
            ];
        }

        // Check if amount is the same
        if ($newAmount == $currentAmount) {
            return [
                'status' => 'info',
                'message' => 'Nominal iuran sukarela tidak berubah.'
            ];
        }

        return DB::transaction(function () use ($user, $newAmount, $currentAmount) {
            // Calculate dates
            $tglPerubahan = now();
            $tglProses = $tglPerubahan->copy()->addMonth()->day(20);
            $tglImplementasi = $tglPerubahan->copy()->addMonths(2)->day(1);

            // Create history record dengan struktur yang benar
            IuranHistory::create([
                'N_NIK' => $user->nik,
                'JENIS' => 'SUKARELA', // Tambahkan JENIS
                'NOMINAL_LAMA' => $currentAmount,
                'NOMINAL_BARU' => $newAmount,
                'STATUS_PROSES' => 'PENDING', // FIXED: gunakan STATUS_PROSES
                'TGL_PERUBAHAN' => $tglPerubahan,
                'TGL_PROSES' => $tglProses,
                'TGL_IMPLEMENTASI' => $tglImplementasi,
                'KETERANGAN' => 'Pengajuan perubahan iuran sukarela melalui portal anggota',
                'CREATED_BY' => $user->nik,
                'CREATED_AT' => now()
            ]);

            Log::info('Iuran sukarela change requested', [
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
     * Calculate iuran totals
     */
    private function calculateIuranTotals(User $user, int $iuranWajib, int $iuranSukarela): array
    {
        $totalPerBulan = $iuranWajib + $iuranSukarela;
        
        // Calculate months since joining
        $joinDate = Carbon::parse($user->created_at);
        $monthsSinceJoining = $joinDate->diffInMonths(now()) + 1; // Include current month
        
        $totalPaid = $totalPerBulan * $monthsSinceJoining;

        return [
            'totalPerBulan' => $totalPerBulan,
            'totalPaid' => $totalPaid
        ];
    }

    /**
     * Get iuran history for user
     */
    private function getIuranHistory(string $nik): \Illuminate\Database\Eloquent\Collection
    {
        return IuranHistory::where('N_NIK', $nik)
                          ->orderBy('CREATED_AT', 'DESC')
                          ->get();
    }

    /**
     * Check if email is dummy email
     */
    private function isDummyEmail(string $email): bool
    {
        return str_ends_with($email, '@sekar.local'); // FIXED: sesuai dengan database
    }

    /**
     * Send email change confirmation
     */
    private function sendEmailChangeConfirmation(User $user, string $oldEmail): void
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