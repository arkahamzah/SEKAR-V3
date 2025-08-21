<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\Setting;
use App\Models\SertifikatSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SertifikatController extends Controller
{
    /**
     * Display user certificate/ID card
     */
  public function show()
    {
        $user = Auth::user();
        $karyawan = $user->karyawan;
        $joinDate = $user->created_at;

        if (!$karyawan) {
            return redirect()->route('profile.index')
                            ->with('error', 'Data karyawan tidak ditemukan.');
        }


        $ketuaUmum = SertifikatSignature::where('jabatan', 'LIKE', '%Ketua Umum%')
                                        ->where('start_date', '<=', $joinDate)
                                        ->where('end_date', '>=', $joinDate)
                                        ->orderBy('start_date', 'desc') 
                                        ->first();

        $sekjen = SertifikatSignature::where(function ($query) {

                                            $query->where('jabatan', 'LIKE', '%Sekjen%')
                                                ->orWhere('jabatan', 'LIKE', '%Sekretaris Jendral%');
                                        })
                                        ->where('start_date', '<=', $joinDate)
                                        ->where('end_date', '>=', $joinDate)
                                        ->orderBy('start_date', 'desc') 
                                        ->first();

        return view('sertifikat.show', [
            'user' => $user,
            'karyawan' => $karyawan,
            'joinDate' => $joinDate,
            'ketuaUmum' => $ketuaUmum,
            'sekjen' => $sekjen,
        ]);
    }

    /**
     * Download certificate as PDF (placeholder)
     */
    public function download()
    {

        return redirect()->route('sertifikat.show')
                       ->with('info', 'Fitur download PDF sedang dalam pengembangan.');
    }

    /**
     * Get signature images
     */
    private function getSignatures(): array
    {
        $signatures = [
            'sekjen' => null,
            'ketum' => null 
        ];

        $sekjenSetting = Setting::where('SETTING_KEY', 'sekjen_signature')->first();
        if ($sekjenSetting && $sekjenSetting->SETTING_VALUE) {
            $signatures['sekjen'] = asset('storage/signatures/' . $sekjenSetting->SETTING_VALUE);
        }

        // DIUBAH: Mengambil 'ketum_signature' dari database
        $ketumSetting = Setting::where('SETTING_KEY', 'ketum_signature')->first();
        if ($ketumSetting && $ketumSetting->SETTING_VALUE) {
            $signatures['ketum'] = asset('storage/signatures/' . $ketumSetting->SETTING_VALUE);
        }

        return $signatures;
    }

    /**
     * Get signature period
     */
    private function getSignaturePeriode(): array
    {
        return [
            'start' => Setting::getValue('signature_periode_start'),
            'end' => Setting::getValue('signature_periode_end')
        ];
    }

    /**
     * Check if current date is within signature period
     */
    private function isSignaturePeriodActive(): bool
    {
        $startDate = Setting::getValue('signature_periode_start');
        $endDate = Setting::getValue('signature_periode_end');

        if (!$startDate || !$endDate) {
            return false;
        }

        $today = now()->format('Y-m-d');

        return $today >= $startDate && $today <= $endDate;
    }

    /**
     * Generate certificate number
     */
    private function generateCertificateNumber(User $user): string
    {
        $year = $user->created_at->format('Y');
        $month = $user->created_at->format('m');

        return "SEKAR/{$year}/{$month}/{$user->nik}";
    }
}