<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan; // Diubah untuk menggunakan model Karyawan yang baru
use App\Models\Iuran;
use App\Models\IuranHistory;
use App\Models\IuranBulanan;
use App\Models\Params;
use App\Models\ExAnggota;
use App\Models\SekarPengurus;
use App\Models\SekarKaryawan;
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
     * Menampilkan profil pengguna dengan informasi iuran
     */
    public function index()
    {
        $user = Auth::user();
        $profileData = $this->getProfileData($user);

        return view('profile.index', $profileData);
    }

    /**
     * Memperbarui foto profil pengguna
     */
    public function updateProfilePicture(Request $request)
    {
        $validated = $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Maks 2MB
        ], [
            'profile_picture.required' => 'Foto profil wajib dipilih.',
            'profile_picture.image' => 'File harus berupa gambar.',
            'profile_picture.mimes' => 'Format gambar harus JPEG, PNG, atau JPG.',
            'profile_picture.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user, $request) {
                // Hapus foto profil lama jika ada
                if ($user->profile_picture && Storage::disk('public')->exists('profile-pictures/' . $user->profile_picture)) {
                    Storage::disk('public')->delete('profile-pictures/' . $user->profile_picture);
                }

                // Simpan foto profil baru
                $file = $request->file('profile_picture');
                $fileName = time() . '_' . $user->nik . '.' . $file->getClientOriginalExtension();
                $file->storeAs('profile-pictures', $fileName, 'public');

                // Perbarui record pengguna
                $user->update([
                    'profile_picture' => $fileName
                ]);

                Log::info('Foto profil diperbarui', [
                    'nik' => $user->nik,
                    'name' => $user->name,
                    'filename' => $fileName,
                    'timestamp' => now()
                ]);
            });

            return redirect()->route('profile.index')
                           ->with('success', 'Foto profil berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Gagal memperbarui foto profil', [
                'nik' => $user->nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('profile.index')
                           ->with('error', 'Terjadi kesalahan saat memperbarui foto profil. Silakan coba lagi.');
        }
    }

    /**
     * Menghapus foto profil pengguna
     */
    public function deleteProfilePicture()
    {
        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                // Hapus file dari storage
                if ($user->profile_picture && Storage::disk('public')->exists('profile-pictures/' . $user->profile_picture)) {
                    Storage::disk('public')->delete('profile-pictures/' . $user->profile_picture);
                }

                // Perbarui record pengguna
                $user->update([
                    'profile_picture' => null
                ]);

                Log::info('Foto profil dihapus', [
                    'nik' => $user->nik,
                    'name' => $user->name,
                    'timestamp' => now()
                ]);
            });

            return redirect()->route('profile.index')
                           ->with('success', 'Foto profil berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Gagal menghapus foto profil', [
                'nik' => $user->nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('profile.index')
                           ->with('error', 'Terjadi kesalahan saat menghapus foto profil. Silakan coba lagi.');
        }
    }

    /**
     * Memperbarui iuran sukarela
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
     * Membatalkan perubahan iuran yang pending
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
                $pendingChange->update([
                    'STATUS_PROSES' => 'DIBATALKAN',
                    'KETERANGAN' => 'Dibatalkan oleh anggota - ' . $pendingChange->KETERANGAN
                ]);

                Log::info('Perubahan iuran dibatalkan oleh pengguna', [
                    'nik' => $user->nik,
                    'name' => $user->name,
                    'cancelled_amount' => $pendingChange->NOMINAL_BARU,
                    'timestamp' => now()
                ]);
            });

            return redirect()->route('profile.index')
                           ->with('success', 'Perubahan iuran sukarela berhasil dibatalkan dan disimpan ke riwayat.');

        } catch (\Exception $e) {
            Log::error('Gagal membatalkan perubahan iuran', [
                'nik' => $user->nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('profile.index')
                           ->with('error', 'Terjadi kesalahan saat membatalkan perubahan iuran.');
        }
    }

    /**
     * Memperbarui email pengguna
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

        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->route('profile.index')
                           ->withErrors(['current_password' => 'Password saat ini tidak benar.'])
                           ->withInput();
        }

        try {
            DB::transaction(function () use ($user, $validated) {
                $oldEmail = $user->email;

                $user->update([
                    'email' => $validated['email']
                ]);

                Log::info('Email pengguna diperbarui', [
                    'nik' => $user->nik,
                    'name' => $user->name,
                    'old_email' => $oldEmail,
                    'new_email' => $validated['email'],
                    'timestamp' => now()
                ]);

                $this->sendEmailChangeConfirmation($user, $oldEmail);
            });

            return redirect()->route('profile.index')
                           ->with('success', 'Email berhasil diperbarui. Email konfirmasi telah dikirim ke alamat baru Anda.');

        } catch (\Exception $e) {
            Log::error('Gagal memperbarui email', [
                'nik' => $user->nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('profile.index')
                           ->with('error', 'Terjadi kesalahan saat memperbarui email. Silakan coba lagi.');
        }
    }

    /**
     * Mengambil semua data profil untuk pengguna
     */
    private function getProfileData(User $user)
    {
        $karyawan = $this->getKaryawanData($user->nik);
        $iuran = $this->getIuranData($user->nik);
        $iuranWajib = $this->getIuranWajib();
        $iuranSukarela = $iuran ? (int)$iuran->IURAN_SUKARELA : 0;

        $pendingChange = $this->getPendingIuranChange($user->nik);
        $effectiveIuranSukarela = $iuranSukarela;

        $totalIuranPerBulan = $iuranWajib + $effectiveIuranSukarela;

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
        ];
    }

    /**
     * Mengambil data karyawan
     */
    private function getKaryawanData(string $nik)
    {
        return Karyawan::where('N_NIK', $nik)->first();
    }

    /**
     * Mengambil data iuran
     */
    private function getIuranData(string $nik)
    {
        return Iuran::where('N_NIK', $nik)
                   ->orderByRaw('CAST(IURAN_SUKARELA AS UNSIGNED) DESC')
                   ->orderBy('CREATED_AT', 'DESC')
                   ->first();
    }

    /**
     * Mengambil nominal iuran wajib saat ini
     */
    private function getIuranWajib()
    {
        $params = Params::where('IS_AKTIF', '1')
                       ->where('TAHUN', date('Y'))
                       ->first();

        return $params ? (int)$params->NOMINAL_IURAN_WAJIB : 25000;
    }

    /**
     * Mengambil perubahan iuran yang pending
     */
    private function getPendingIuranChange(string $nik)
    {
        return IuranHistory::where('N_NIK', $nik)
                          ->where('STATUS_PROSES', 'PENDING')
                          ->latest('CREATED_AT')
                          ->first();
    }

    /**
     * Mengambil total iuran yang sudah terbayar
     */
    private function getTotalIuranTerbayar(string $nik)
    {
        return (int) IuranBulanan::where('N_NIK', $nik)
                                ->where('STATUS', 'LUNAS')
                                ->sum('TOTAL_IURAN');
    }

    /**
     * Mengambil jumlah bulan yang sudah terbayar
     */
    private function getBulanTerbayar(string $nik)
    {
        return IuranBulanan::where('N_NIK', $nik)
                          ->where('STATUS', 'LUNAS')
                          ->count();
    }

    /**
     * Mengambil jumlah bulan yang belum terbayar/tunggakan
     */
    private function getBulanTunggakan(string $nik)
    {
        return IuranBulanan::where('N_NIK', $nik)
                          ->whereIn('STATUS', ['BELUM_BAYAR', 'TERLAMBAT'])
                          ->count();
    }

    /**
     * Memproses pembaruan iuran sukarela
     */
    private function processIuranSukarelaUpdate(User $user, int $newAmount)
    {
        $currentIuran = $this->getIuranData($user->nik);
        $currentAmount = $currentIuran ? (int)$currentIuran->IURAN_SUKARELA : 0;

        $pendingChange = $this->getPendingIuranChange($user->nik);

        if ($pendingChange) {
            if ($newAmount == (int)$pendingChange->NOMINAL_BARU) {
                return [
                    'status' => 'info',
                    'message' => 'Nominal iuran sukarela tidak berubah dari yang sedang pending.'
                ];
            }
            return $this->updatePendingIuranChange($user, $pendingChange, $newAmount);
        }

        if ($newAmount == $currentAmount) {
            return [
                'status' => 'info',
                'message' => 'Nominal iuran sukarela tidak berubah.'
            ];
        }

        return $this->createNewIuranChange($user, $newAmount, $currentAmount);
    }

    /**
     * Memperbarui perubahan iuran yang pending
     */
    private function updatePendingIuranChange(User $user, IuranHistory $pendingChange, int $newAmount)
    {
        return DB::transaction(function () use ($user, $pendingChange, $newAmount) {
            $oldPendingAmount = (int)$pendingChange->NOMINAL_BARU;

            $pendingChange->update([
                'STATUS_PROSES' => 'DIBATALKAN',
                'KETERANGAN' => 'Dibatalkan karena ada perubahan baru - ' . $pendingChange->KETERANGAN
            ]);

            $tglPerubahan = now();
            $tglProses = $tglPerubahan->copy()->addMonth()->day(20);
            $tglImplementasi = $tglPerubahan->copy()->addMonths(2)->day(1);

            IuranHistory::create([
                'N_NIK' => $user->nik,
                'JENIS' => 'SUKARELA',
                'NOMINAL_LAMA' => $oldPendingAmount,
                'NOMINAL_BARU' => $newAmount,
                'STATUS_PROSES' => 'PENDING',
                'TGL_PERUBAHAN' => $tglPerubahan,
                'TGL_PROSES' => $tglProses,
                'TGL_IMPLEMENTASI' => $tglImplementasi,
                'KETERANGAN' => 'Perubahan nominal iuran sukarela (mengganti pengajuan sebelumnya)',
                'CREATED_BY' => $user->nik,
                'CREATED_AT' => now()
            ]);

            Log::info('Perubahan iuran pending diperbarui', [
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
     * Membuat pengajuan perubahan iuran baru
     */
    private function createNewIuranChange(User $user, int $newAmount, int $currentAmount)
    {
        return DB::transaction(function () use ($user, $newAmount, $currentAmount) {
            $tglPerubahan = now();
            $tglProses = $tglPerubahan->copy()->addMonth()->day(20);
            $tglImplementasi = $tglPerubahan->copy()->addMonths(2)->day(1);

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

            Log::info('Pengajuan perubahan iuran baru', [
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
     * Mengambil riwayat iuran melalui AJAX
     */
    public function getIuranHistory(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 1);

        $iuranHistory = IuranHistory::where('N_NIK', $user->nik)
                                  ->orderBy('CREATED_AT', 'DESC')
                                  ->paginate(5, ['*'], 'page', $page);

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
     * Mengambil riwayat pembayaran melalui AJAX
     */
    public function getPaymentHistory(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 1);

        $paymentHistory = IuranBulanan::where('N_NIK', $user->nik)
                                    ->orderBy('TAHUN', 'DESC')
                                    ->orderBy('BULAN', 'DESC')
                                    ->paginate(12, ['*'], 'page', $page);

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
     * Helper untuk mendapatkan nama bulan
     */
    private function getBulanNama(string $bulan)
    {
        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        return $months[$bulan] ?? 'Unknown';
    }

    /**
     * Helper untuk mendapatkan teks status
     */
    private function getStatusText(string $status)
    {
        switch ($status) {
            case 'LUNAS': return 'Lunas';
            case 'BELUM_BAYAR': return 'Belum Bayar';
            case 'TERLAMBAT': return 'Terlambat';
            default: return 'Unknown';
        }
    }

    /**
     * Helper untuk mendapatkan warna status
     */
    private function getStatusColor(string $status)
    {
        switch ($status) {
            case 'LUNAS': return 'green';
            case 'BELUM_BAYAR': return 'yellow';
            case 'TERLAMBAT': return 'red';
            default: return 'gray';
        }
    }

    /**
     * Mengirim konfirmasi perubahan email
     */
    private function sendEmailChangeConfirmation(User $user, string $oldEmail)
    {
        try {
            Log::info('Konfirmasi perubahan email akan dikirim', [
                'nik' => $user->nik,
                'old_email' => $oldEmail,
                'new_email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim konfirmasi perubahan email', [
                'nik' => $user->nik,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Proses pengunduran diri anggota
     */
    public function resign(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login')->with('error', 'Sesi tidak valid.');
        }

        try {
            DB::transaction(function () use ($user) {
                
                $karyawan = Karyawan::where('N_NIK', $user->nik)->first();
                $iuran = Iuran::where('N_NIK', $user->nik)->first();
                $pengurus = SekarPengurus::where('N_NIK', $user->nik)->first();

                ExAnggota::create([
                    'N_NIK'                     => $user->nik,
                    'V_NAMA_KARYAWAN'           => $karyawan->V_NAMA_KARYAWAN ?? $user->name,
                    'V_SHORT_POSISI'            => $karyawan->V_SHORT_POSISI ?? null,
                    'V_SHORT_DIVISI'            => $karyawan->V_SHORT_DIVISI ?? null,
                    'TGL_KELUAR'                => now(),
                    'ALASAN_KELUAR'             => 'Pengunduran Diri Mandiri',
                    'IURAN_WAJIB_TERAKHIR'      => $iuran->IURAN_WAJIB ?? 0,
                    'IURAN_SUKARELA_TERAKHIR'   => $iuran->IURAN_SUKARELA ?? 0,
                    'DPW'                       => $karyawan->DPW ?? null, // Diambil dari data karyawan
                    'DPD'                       => $karyawan->DPD ?? null, // Diambil dari data karyawan
                    'V_KOTA_GEDUNG'             => $karyawan->V_KOTA_GEDUNG ?? null,
                    'CREATED_BY'                => $user->nik,
                    'CREATED_AT'                => now(),
                ]);

                if ($iuran) $iuran->delete();
                if ($pengurus) $pengurus->delete();
                
                User::where('id', $user->id)->delete();

            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses pengunduran diri. Silakan coba lagi. Error: ' . $e->getMessage());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil mengundurkan diri. Terima kasih atas kontribusi Anda selama ini.');
    }
}