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
        $cacheKey = 'dashboard_statistics';
        
        $data = Cache::remember($cacheKey, 10, function () {
            \Log::info('getDashboardData() dipanggil pada: ' . now());
            return $this->getDashboardData();
        });
        
        $greeting = $this->getGreetingData();
        
        return view('dashboard', array_merge($data, ['greeting' => $greeting]));
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

    private function getDashboardData(): array
    {
        $statistics = $this->getStatistics();
        $mappingWithStats = $this->getDpwMappingWithStats();

        return array_merge($statistics, [
            'mappingWithStats' => $mappingWithStats
        ]);
    }

    /**
     * ## FUNGSI INI TELAH DIPERBARUI ##
     * Menambahkan logika untuk menghitung pertumbuhan bulanan untuk setiap metrik.
     * Asumsi: tabel 'users', 't_sekar_pengurus', dan 't_ex_anggota'
     * memiliki kolom timestamp 'created_at'.
     */
    private function getStatistics(): array
    {
        // --- TOTAL DATA (LOGIC ASLI) ---
        $anggotaAktif = DB::table('users as u')
            ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
            ->where('k.V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->count();

        $totalPengurus = DB::table('t_sekar_pengurus as sp')
            ->join('t_karyawan as k', 'sp.N_NIK', '=', 'k.N_NIK')
            ->count();

        $anggotaKeluar = ExAnggota::count();

        $totalKaryawanNonGPTP = Karyawan::where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')->count();
        $nonAnggota = max(0, $totalKaryawanNonGPTP - $anggotaAktif);

        // --- PERTUMBUHAN BULANAN (LOGIC BARU) ---
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Pertumbuhan Anggota Aktif (member baru bulan ini)
        $pertumbuhanAnggotaAktif = DB::table('users as u')
            ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
            ->where('k.V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->whereBetween('u.created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Pertumbuhan Pengurus (pengurus baru bulan ini)
        $pertumbuhanPengurus = DB::table('t_sekar_pengurus as sp')
            ->whereBetween('sp.created_at', [$startOfMonth, $endOfMonth])
            ->count();
        
        // Pertumbuhan Anggota Keluar (yang keluar bulan ini)
        $pertumbuhanAnggotaKeluar = ExAnggota::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();
        
        // ## DIHAPUS ##
        // Logika untuk pertumbuhan non-anggota dihapus sesuai permintaan
        // $pertumbuhanNonAnggota = $pertumbuhanAnggotaKeluar;

        return [
            // Data total
            'anggotaAktif' => $anggotaAktif,
            'totalPengurus' => $totalPengurus,
            'anggotaKeluar' => $anggotaKeluar,
            'nonAnggota' => $nonAnggota,
            // Data pertumbuhan bulanan
            'pertumbuhanAnggotaAktif' => $pertumbuhanAnggotaAktif,
            'pertumbuhanPengurus' => $pertumbuhanPengurus,
            'pertumbuhanAnggotaKeluar' => $pertumbuhanAnggotaKeluar,
            // 'pertumbuhanNonAnggota' => $pertumbuhanNonAnggota, // DIHAPUS
        ];
    }


    private function getDpwMappingWithStats()
    {
        $karyawanMappings = DB::table('t_karyawan')
            ->select('DPW', 'DPD')
            ->whereNotNull('DPW')
            ->where('DPW', '!=', '')
            ->whereNotNull('DPD')
            ->where('DPD', '!=', '');

        $allMappings = DB::table('t_sekar_pengurus')
            ->select('DPW', 'DPD')
            ->whereNotNull('DPW')
            ->where('DPW', '!=', '')
            ->whereNotNull('DPD')
            ->where('DPD', '!=', '')
            ->union($karyawanMappings)
            ->distinct()
            ->get();
            
        return $allMappings->map(function ($mapping) {
            return $this->enrichMappingWithStats($mapping);
        });
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
        return DB::table('users as u')
            ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
            ->where('k.DPW', $dpw)
            ->where('k.DPD', $dpd)
            ->where('k.V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->count();
    }
    
    private function getPengurusByArea($dpw, $dpd)
    {
        return DB::table('t_sekar_pengurus as sp')
            ->where('sp.DPW', $dpw)
            ->where('sp.DPD', $dpd)
            ->count();
    }
    
    private function getAnggotaKeluarByArea($dpw, $dpd)
    {
        return DB::table('t_ex_anggota as ea')
            ->where('ea.DPW', $dpw)
            ->where('ea.DPD', $dpd)
            ->count();
    }

    private function getNonAnggotaByArea($dpw, $dpd)
    {
        $totalKaryawan = Karyawan::where('DPW', $dpw)
            ->where('DPD', $dpd)
            ->where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->count();
        
        $anggotaAktif = $this->getAnggotaAktifByArea($dpw, $dpd);
        
        return max(0, $totalKaryawan - $anggotaAktif);
    }
}