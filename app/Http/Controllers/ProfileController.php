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
            $user->email = $validated['email'];
            $user->save();

            return redirect()->route('profile.index')
                           ->with('success', 'Email berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->route('profile.index')
                           ->with('error', 'Terjadi kesalahan saat memperbarui email. Silakan coba lagi.');
        }
    }

    /**
     * Check if email is dummy email
     */
    private function isDummyEmail(string $email): bool
    {
        return str_ends_with($email, '@sekar.local');
    }

    /**
     * Get all profile data for user
     */
    private function getProfileData(User $user): array
    {
        $karyawan = $this->getKaryawanData($user->nik);
        $iuran = $this->getIuranData($user->nik);
        $iuranWajib = $this->getIuranWajib();
        $iuranSukarela = $iuran ? (int)$iuran->IURAN_SUKARELA : 0;

        $pendingChange = $this->getPendingIuranChange($user->nik);
        $effectiveIuranSukarela = $pendingChange ? (int)$pendingChange->NOMINAL_BARU : $iuranSukarela;

        // PENTING: Total iuran terbayar SELALU pakai iuran aktual, bukan pending
        $iuranCalculations = $this->calculateIuranTotals($user, $iuranWajib, $iuranSukarela);
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

        return $params ? (int)$params->NOMINAL_IURAN_WAJIB : 25000;
    }

    /**
     * Get pending iuran change
     */
    private function getPendingIuranChange(string $nik): ?IuranHistory
    {
        return IuranHistory::where('N_NIK', $nik)
                          ->where('JENIS', 'SUKARELA')
                          ->where('STATUS_PROSES', 'PENDING')
                          ->orderBy('CREATED_AT', 'desc')
                          ->first();
    }

    /**
     * Calculate iuran totals - SELALU PAKAI IURAN AKTUAL
     */
    private function calculateIuranTotals(User $user, int $iuranWajib, int $actualIuranSukarela): array
    {
        // PENTING: Pakai iuran sukarela aktual, BUKAN pending
        $totalPerBulan = $iuranWajib + $actualIuranSukarela;

        // Calculate months since joining
        $joinDate = $user->created_at;
        $currentDate = now();

        $yearDiff = $currentDate->year - $joinDate->year;
        $monthDiff = $currentDate->month - $joinDate->month;

        $monthsSinceJoin = max(1, ($yearDiff * 12) + $monthDiff + 1);
        $totalPaid = $totalPerBulan * $monthsSinceJoin;

        return [
            'totalPerBulan' => $totalPerBulan,
            'totalPaid' => $totalPaid,
        ];
    }

    /**
     * Get iuran history
     */
    private function getIuranHistory(string $nik)
    {
        return IuranHistory::where('N_NIK', $nik)
                          ->orderBy('CREATED_AT', 'desc')
                          ->get();
    }

    /**
     * Process iuran sukarela update - HANYA BUAT HISTORY, JANGAN UPDATE IURAN
     */
    private function processIuranSukarelaUpdate(User $user, int $newAmount): array
    {
        $currentAmount = $this->getCurrentIuranSukarela($user->nik);

        // Check if there's any change
        if ($currentAmount === $newAmount) {
            return [
                'status' => 'info',
                'message' => 'Tidak ada perubahan pada iuran sukarela.'
            ];
        }

        DB::transaction(function () use ($user, $currentAmount, $newAmount) {
            // Cancel any existing pending changes first
            $this->cancelPendingChanges($user->nik);

            // HANYA buat history record - JANGAN update tabel iuran
            $this->createIuranHistory($user->nik, $currentAmount, $newAmount);

            // REMOVED: updateOrCreateIuranRecord - ini yang bikin total berubah!
            // Iuran record akan diupdate nanti oleh HC saat implementasi
        });

        return [
            'status' => 'success',
            'message' => 'Iuran sukarela berhasil diperbarui. Perubahan akan diproses dalam 1 bulan dan diterapkan dalam 2 bulan sesuai kebijakan HC.'
        ];
    }

    /**
     * Get current iuran sukarela amount - HANDLE DUPLICATES
     */
    private function getCurrentIuranSukarela(string $nik): int
    {
        $iuran = $this->getIuranData($nik); // This already handles duplicates
        return $iuran ? (int)$iuran->IURAN_SUKARELA : 0;
    }

    /**
     * Cancel existing pending changes when new change is made
     */
    private function cancelPendingChanges(string $nik): void
    {
        IuranHistory::where('N_NIK', $nik)
                   ->where('JENIS', 'SUKARELA')
                   ->where('STATUS_PROSES', 'PENDING')
                   ->update([
                       'STATUS_PROSES' => 'CANCELLED',
                       'KETERANGAN' => 'Dibatalkan karena ada perubahan baru',
                       'UPDATED_AT' => now()
                   ]);
    }

    /**
     * Check if user has pending changes - KEPT FOR REFERENCE BUT NOT USED
     * This method is now only used for informational purposes in the view
     */
    private function hasPendingChanges(string $nik): bool
    {
        return IuranHistory::where('N_NIK', $nik)
                          ->where('JENIS', 'SUKARELA')
                          ->whereIn('STATUS_PROSES', ['PENDING', 'PROCESSED'])
                          ->exists();
    }

    /**
     * Create iuran history record
     */
    private function createIuranHistory(string $nik, int $oldAmount, int $newAmount): void
    {
        IuranHistory::createWithDates([
            'N_NIK' => $nik,
            'JENIS' => 'SUKARELA',
            'NOMINAL_LAMA' => $oldAmount,
            'NOMINAL_BARU' => $newAmount,
            'STATUS_PROSES' => 'PENDING',
            'TGL_PERUBAHAN' => now(),
            'KETERANGAN' => 'Perubahan iuran sukarela oleh anggota',
            'CREATED_BY' => $nik,
            'CREATED_AT' => now()
        ]);
    }

    /**
     * Update or create iuran record - PREVENT DUPLICATES
     * NOTE: Method ini hanya dipanggil saat implementasi oleh HC, bukan saat user edit
     */
    private function updateOrCreateIuranRecord(string $nik, int $newAmount): void
    {
        // Delete any duplicate records first, keep the latest one
        $this->cleanupDuplicateIuran($nik);

        $iuran = $this->getIuranData($nik);

        if ($iuran) {
            $iuran->update([
                'IURAN_SUKARELA' => (string)$newAmount,
                'UPDATE_BY' => $nik,
                'UPDATED_AT' => now()
            ]);
        } else {
            $iuranWajib = $this->getIuranWajib();

            Iuran::create([
                'N_NIK' => $nik,
                'IURAN_WAJIB' => (string)$iuranWajib,
                'IURAN_SUKARELA' => (string)$newAmount,
                'CREATED_BY' => $nik,
                'CREATED_AT' => now()
            ]);
        }
    }

    /**
     * Clean up duplicate iuran records
     */
    private function cleanupDuplicateIuran(string $nik): void
    {
        $records = Iuran::where('N_NIK', $nik)->get();

        if ($records->count() > 1) {
            // Keep the record with highest IURAN_SUKARELA and latest CREATED_AT
            $keepRecord = $records->sortByDesc(function ($item) {
                return [(int)$item->IURAN_SUKARELA, $item->CREATED_AT];
            })->first();

            // Delete other records
            Iuran::where('N_NIK', $nik)
                  ->where('ID', '!=', $keepRecord->ID)
                  ->delete();
        }
    }
}