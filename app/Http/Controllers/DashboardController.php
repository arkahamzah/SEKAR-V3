<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\SekarPengurus;
use App\Models\ExAnggota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $statistics = Cache::remember('dashboard_main_statistics', 10, function () {
            return $this->getStatistics();
        });

        $mappingWithStats = $this->getDpwMappingWithStats();
        $greeting = $this->getGreetingData();

        return view('dashboard', array_merge($statistics, [
            'greeting' => $greeting,
            'mappingWithStats' => $mappingWithStats
        ]));
    }

    private function getGreetingData()
    {
        $user = Auth::user();
        $hour = (int) now()->format('H');

        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Selamat Pagi';
            $icon = '🌅';
        } elseif ($hour >= 12 && $hour < 15) {
            $greeting = 'Selamat Siang';
            $icon = '☀️';
        } elseif ($hour >= 15 && $hour < 19) {
            $greeting = 'Selamat Sore';
            $icon = '🌇';
        } else {
            $greeting = 'Selamat Malam';
            $icon = '🌙';
        }

        if ($user->is_gptp_preorder && !$user->isMembershipActive()) {
            $statusMessage = 'Membership GPTP Anda akan segera aktif. Terima kasih atas kesabaran Anda.';
        } else {
            $statusMessage = 'Selamat datang di portal SEKAR Telkom!';
        }

        return [
            'time_greeting' => $greeting,
            'icon' => $icon,
            'user_name' => explode(' ', $user->name)[0],
            'status_message' => $statusMessage,
            'current_date' => now()->format('d M Y'),
            'current_time' => now()->format('H:i'),
        ];
    }

    private function getStatistics(): array
    {
        // --- TOTAL DATA ---
        // Diperbarui: Menggunakan model Karyawan dan view v_karyawan_base yang sudah memiliki status anggota
        $anggotaAktif = Karyawan::where('STATUS_ANGGOTA', 'Terdaftar')
            ->where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->count();

        // Cukup hitung dari tabel pengurus
        $totalPengurus = SekarPengurus::count();

        $anggotaKeluar = ExAnggota::count();

        // Menggunakan model Karyawan yang sudah mengarah ke view
        $totalKaryawanNonGPTP = Karyawan::where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')->count();
        $nonAnggota = max(0, $totalKaryawanNonGPTP - $anggotaAktif);

        // --- PERTUMBUHAN BULANAN ---
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Diperbarui: Menggunakan model Karyawan dan view v_karyawan_base
        $pertumbuhanAnggotaAktif = Karyawan::where('STATUS_ANGGOTA', 'Terdaftar')
            ->where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->whereBetween('TGL_TERDAFTAR', [$startOfMonth, $endOfMonth])
            ->count();

        $pertumbuhanPengurus = SekarPengurus::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $pertumbuhanAnggotaKeluar = ExAnggota::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        return [
            'anggotaAktif' => $anggotaAktif,
            'totalPengurus' => $totalPengurus,
            'anggotaKeluar' => $anggotaKeluar,
            'nonAnggota' => $nonAnggota,
            'pertumbuhanAnggotaAktif' => $pertumbuhanAnggotaAktif,
            'pertumbuhanPengurus' => $pertumbuhanPengurus,
            'pertumbuhanAnggotaKeluar' => $pertumbuhanAnggotaKeluar,
        ];
    }

    private function getDpwMappingWithStats()
    {
        // Diperbarui: Sumber data karyawan diubah dari t_karyawan ke v_karyawan_base
        // karena t_karyawan tidak punya kolom DPD/DPW.
        $karyawanMappings = DB::table('v_karyawan_base')
            ->select('DPW', 'DPD')
            ->whereNotNull('DPW')->where('DPW', '!=', '')
            ->whereNotNull('DPD')->where('DPD', '!=', '');

        $paginatedMappings = DB::table('t_sekar_pengurus')
            ->select('DPW', 'DPD')
            ->whereNotNull('DPW')->where('DPW', '!=', '')
            ->whereNotNull('DPD')->where('DPD', '!=', '')
            ->union($karyawanMappings)
            ->groupBy('DPW', 'DPD')
            ->orderBy('DPW')
            ->orderBy('DPD')
            ->paginate(10);

        $paginatedMappings->getCollection()->transform(function ($mapping) {
            return $this->enrichMappingWithStats($mapping);
        });

        return $paginatedMappings;
    }

    private function enrichMappingWithStats($mapping)
    {
        return (object)[
            'dpw' => $mapping->DPW,
            'dpd' => $mapping->DPD,
            'anggota_aktif' => $this->getAnggotaAktifByArea($mapping->DPW, $mapping->DPD),
            'pengurus' => $this->getPengurusByArea($mapping->DPW, $mapping->DPD),
            'anggota_keluar' => $this->getAnggotaKeluarByArea($mapping->DPW, $mapping->DPD),
            'non_anggota' => $this->getNonAnggotaByArea($mapping->DPW, $mapping->DPD)
        ];
    }

    private function getAnggotaAktifByArea($dpw, $dpd)
    {
        // Diperbarui: Menggunakan model Karyawan untuk konsistensi dan kemudahan membaca
        return Karyawan::where('DPW', $dpw)
            ->where('DPD', $dpd)
            ->where('STATUS_ANGGOTA', 'Terdaftar')
            ->where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->count();
    }

    private function getPengurusByArea($dpw, $dpd)
    {
        return SekarPengurus::where('DPW', $dpw)
            ->where('DPD', $dpd)
            ->count();
    }

    private function getAnggotaKeluarByArea($dpw, $dpd)
    {
        return ExAnggota::where('DPW', $dpw)
            ->where('DPD', $dpd)
            ->count();
    }

    private function getNonAnggotaByArea($dpw, $dpd)
    {
        // Query ini sudah benar karena menggunakan model Karyawan
        $totalKaryawan = Karyawan::where('DPW', $dpw)
            ->where('DPD', $dpd)
            ->where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->count();

        $anggotaAktif = $this->getAnggotaAktifByArea($dpw, $dpd);

        return max(0, $totalKaryawan - $anggotaAktif);
    }
}