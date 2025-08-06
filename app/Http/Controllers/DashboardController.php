<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\SekarPengurus;
use App\Models\ExAnggota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{

    public function index()
    {
        $cacheKey = 'dashboard_statistics';
        
        $data = Cache::remember($cacheKey, 10, function () {
            \Log::info('getDashboardData() dipanggil pada: ' . now());
            return $this->getDashboardData();
        });

        return view('dashboard', $data);
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

        return $dpwMapping[$city] ?? 'DPW Jabar';
    }

    private function enrichMappingWithStats($mapping): object
    {
        $kota = $mapping->kota;
        
        return (object)[
            'dpw' => $mapping->dpw,
            'dpd' => $mapping->dpd,
            'anggota_aktif' => $this->getAnggotaAktifByArea($kota),
            'pengurus' => $this->getPengurusByArea($kota),
            'anggota_keluar' => $this->getAnggotaKeluarByArea($kota),
            'non_anggota' => $this->getNonAnggotaByArea($kota)
        ];
    }

    private function getAnggotaAktifByArea(string $kota): int
    {
        return DB::table('users as u')
            ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
            ->where('k.V_KOTA_GEDUNG', $kota)
            ->where('k.V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->count();
    }
 
    private function getPengurusByArea(string $kota): int
    {
        return DB::table('t_sekar_pengurus as sp')
            ->join('t_karyawan as k', 'sp.N_NIK', '=', 'k.N_NIK')
            ->where('k.V_KOTA_GEDUNG', $kota)
            ->count();
    }

    private function getAnggotaKeluarByArea(string $kota): int
    {
        return ExAnggota::where('V_KOTA_GEDUNG', $kota)->count();
    }


    private function getNonAnggotaByArea(string $kota): int
    {
        $totalKaryawan = Karyawan::where('V_KOTA_GEDUNG', $kota)
            ->where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->count();
            
        $anggotaAktif = $this->getAnggotaAktifByArea($kota);
        
        return max(0, $totalKaryawan - $anggotaAktif);
    }

    private function getGrowthData(array $statistics): array
    {
        $anggotaGrowth = $this->calculateGrowthIndicator($statistics['anggotaAktif'], 'anggota');
        $pengurusGrowth = $this->calculateGrowthIndicator($statistics['totalPengurus'], 'pengurus');
        $keluarGrowth = $this->calculateGrowthIndicator($statistics['anggotaKeluar'], 'keluar');
        $nonAnggotaGrowth = $this->calculateGrowthIndicator($statistics['nonAnggota'], 'non_anggota');

        return [
            'anggota_aktif_growth' => $anggotaGrowth,
            'pengurus_growth' => $pengurusGrowth,
            'anggota_keluar_growth' => $keluarGrowth,
            'non_anggota_growth' => $nonAnggotaGrowth
        ];
    }


    private function calculateGrowthIndicator(int $currentValue, string $type): string
    {

        switch ($type) {
            case 'anggota':
                $growth = round(($currentValue * 0.1), 0);
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