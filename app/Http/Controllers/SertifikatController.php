<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\Setting;
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

        if (!$karyawan) {
            return redirect()->route('profile.index')
                           ->with('error', 'Data karyawan tidak ditemukan.');
        }

        // Check if signature period is active
        $isSignaturePeriodActive = $this->isSignaturePeriodActive();

        // Mengambil nama pejabat dari tabel settings untuk membuatnya dinamis
        $settings = Setting::getValues(['ketum_name', 'sekjen_name']);

        $certificateData = [
            'user' => $user,
            'karyawan' => $karyawan,
            'joinDate' => $user->created_at,
            'isSignaturePeriodActive' => $isSignaturePeriodActive,
            'signatures' => $this->getSignatures(),
            'periode' => $this->getSignaturePeriode(),
            'settings' => $settings // <-- Menambahkan nama pejabat ke data view
        ];

        return view('sertifikat.show', $certificateData);
    }

    /**
     * Download certificate as PDF (placeholder)
     */
    public function download()
    {
        // TODO: Implement PDF generation
        // This would require a PDF library like DomPDF or TCPDF

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
            // DIUBAH: Mengambil tanda tangan Ketua Umum
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