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
        $growthData = $this->getGrowthData($statistics);

        return array_merge($statistics, [
            'mappingWithStats' => $mappingWithStats,
            'growthData' => $growthData
        ]);
    }

    private function getStatistics(): array
    {
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

        return [
            'anggotaAktif' => $anggotaAktif,
            'totalPengurus' => $totalPengurus,
            'anggotaKeluar' => $anggotaKeluar,
            'nonAnggota' => $nonAnggota,
        ];
    }

    private function getDpwMappingWithStats()
    {
        $mappingQuery = DB::table('t_sekar_pengurus as sp')
            ->join('t_karyawan as k', 'sp.N_NIK', '=', 'k.N_NIK')
            ->select(
                DB::raw("COALESCE(NULLIF(TRIM(sp.DPW), ''), 'DPW Jabar') as dpw"),
                DB::raw("COALESCE(NULLIF(TRIM(sp.DPD), ''), CONCAT('DPD ', UPPER(k.V_KOTA_GEDUNG))) as dpd"),
                'k.V_KOTA_GEDUNG as kota'
            )
            ->distinct()
            ->get();

        if ($mappingQuery->isEmpty()) {
            return $this->getDefaultMapping();
        }

        return $mappingQuery->map(function ($mapping) {
            return $this->enrichMappingWithStats($mapping);
        });
    }

    private function getDefaultMapping()
    {
        $cities = Karyawan::select('V_KOTA_GEDUNG')
            ->whereNotNull('V_KOTA_GEDUNG')
            ->where('V_KOTA_GEDUNG', '!=', '')
            ->groupBy('V_KOTA_GEDUNG')
            ->get();
        
        $mappings = collect();
        
        foreach ($cities as $city) {
            $cityName = strtoupper($city->V_KOTA_GEDUNG);
            $dpw = $this->getDpwByCity($cityName);
            $dpd = 'DPD ' . $cityName;
            
            $mappings->push((object)[
                'dpw' => $dpw,
                'dpd' => $dpd,
                'anggota_aktif' => $this->getAnggotaAktifByArea($city->V_KOTA_GEDUNG),
                'pengurus' => $this->getPengurusByArea($city->V_KOTA_GEDUNG),
                'anggota_keluar' => $this->getAnggotaKeluarByArea($city->V_KOTA_GEDUNG),
                'non_anggota' => $this->getNonAnggotaByArea($city->V_KOTA_GEDUNG)
            ]);
        }
        
        return $mappings;
    }

    private function getDpwByCity(string $city): string
    {
        $dpwMapping = [
            'BANDUNG' => 'DPW Jabar',
            'JAKARTA' => 'DPW Jakarta', 
            'SURABAYA' => 'DPW Jatim',
            'MEDAN' => 'DPW Sumut',
            'MAKASSAR' => 'DPW Sulsel',
            'SEMARANG' => 'DPW Jateng',
            'YOGYAKARTA' => 'DPW DIY',
            'DENPASAR' => 'DPW Bali',
        ];

        return $dpwMapping[$city] ?? 'DPW Lainnya';
    }

    private function enrichMappingWithStats($mapping)
    {
        return (object)[
            'dpw' => $mapping->dpw,
            'dpd' => $mapping->dpd,
            'anggota_aktif' => $this->getAnggotaAktifByArea($mapping->kota),
            'pengurus' => $this->getPengurusByArea($mapping->kota),
            'anggota_keluar' => $this->getAnggotaKeluarByArea($mapping->kota),
            'non_anggota' => $this->getNonAnggotaByArea($mapping->kota)
        ];
    }

    private function getAnggotaAktifByArea($kota)
    {
        return DB::table('users as u')
            ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
            ->where('k.V_KOTA_GEDUNG', $kota)
            ->where('k.V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->count();
    }

    private function getPengurusByArea($kota)
    {
        return DB::table('t_sekar_pengurus as sp')
            ->join('t_karyawan as k', 'sp.N_NIK', '=', 'k.N_NIK')
            ->where('k.V_KOTA_GEDUNG', $kota)
            ->count();
    }

    private function getAnggotaKeluarByArea($kota)
    {
        return DB::table('t_ex_anggota as ea')
            ->join('t_karyawan as k', 'ea.N_NIK', '=', 'k.N_NIK')
            ->where('k.V_KOTA_GEDUNG', $kota)
            ->count();
    }

    private function getNonAnggotaByArea($kota)
    {
        $totalKaryawan = Karyawan::where('V_KOTA_GEDUNG', $kota)
            ->where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->count();
        
        $anggotaAktif = $this->getAnggotaAktifByArea($kota);
        
        return max(0, $totalKaryawan - $anggotaAktif);
    }

    private function getGrowthData(array $statistics): array
    {
        return [
            'anggota_aktif_growth' => $this->calculateGrowth('aktif', $statistics['anggotaAktif']),
            'pengurus_growth' => $this->calculateGrowth('pengurus', $statistics['totalPengurus']),
            'anggota_keluar_growth' => $this->calculateGrowth('keluar', $statistics['anggotaKeluar']),
            'non_anggota_growth' => $this->calculateGrowth('non_anggota', $statistics['nonAnggota']),
        ];
    }

    private function calculateGrowth(string $type, int $currentValue): string
    {
        switch ($type) {
            case 'aktif':
                $growth = round(($currentValue * 0.12), 0);
                return $growth > 0 ? "+{$growth}" : "0";
                
            case 'pengurus':
                $growth = round(($currentValue * 0.05), 0);
                return $growth > 0 ? "+{$growth}" : "0";
                
            case 'keluar':
                return $currentValue > 0 ? "+{$currentValue}" : "0";
                
            case 'non_anggota':
                $decrease = round(($currentValue * 0.02), 0);
                return $decrease > 0 ? "-{$decrease}" : "0";
                
            default:
                return "0";
        }
    }
}