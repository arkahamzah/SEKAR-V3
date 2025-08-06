<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\Params;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class BanpersController extends Controller
{
    public function index()
    {
        $banpersData = $this->getBanpersData();
        
        return view('banpers.index', $banpersData);
    }

    private function getBanpersData(): array
    {
        $params = Params::where('IS_AKTIF', '1')
                       ->where('TAHUN', date('Y'))
                       ->first();
        
        $nominalBanpers = $params ? (int)$params->NOMINAL_BANPERS : 20000;
        
        $totalAnggotaAktif = DB::table('users as u')
            ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
            ->where('k.V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->count();
        
        $totalBanpers = $totalAnggotaAktif * $nominalBanpers;
        
        $banpersByWilayah = $this->getBanpersByWilayahSimple($nominalBanpers);
        
        return [
            'nominalBanpers' => $nominalBanpers,
            'totalAnggotaAktif' => $totalAnggotaAktif,
            'totalBanpers' => $totalBanpers,
            'banpersByWilayah' => $banpersByWilayah,
            'tahun' => date('Y')
        ];
    }

    private function getBanpersByWilayahSimple(int $nominalBanpers): object
    {
        $query = "
            SELECT 
                CASE 
                    WHEN sp.DPW IS NOT NULL AND TRIM(sp.DPW) != '' 
                    THEN sp.DPW 
                    ELSE 'DPW Jabar' 
                END as dpw,
                CASE 
                    WHEN sp.DPD IS NOT NULL AND TRIM(sp.DPD) != '' 
                    THEN sp.DPD 
                    ELSE CONCAT('DPD ', UPPER(k.V_KOTA_GEDUNG)) 
                END as dpd,
                COUNT(*) as jumlah_anggota,
                (COUNT(*) * ?) as total_banpers
            FROM users u
            INNER JOIN t_karyawan k ON u.nik = k.N_NIK
            LEFT JOIN t_sekar_pengurus sp ON u.nik = sp.N_NIK
            WHERE k.V_SHORT_POSISI NOT LIKE '%GPTP%'
            GROUP BY 1, 2
            ORDER BY 1, 2
        ";
        
        return collect(DB::select($query, [$nominalBanpers]));
    }

    private function getBanpersByWilayah(int $nominalBanpers): object
    {
        $users = DB::table('users as u')
            ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
            ->leftJoin('t_sekar_pengurus as sp', 'u.nik', '=', 'sp.N_NIK')
            ->select(
                'u.nik',
                'sp.DPW',
                'sp.DPD', 
                'k.V_KOTA_GEDUNG'
            )
            ->where('k.V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->get();

        $grouped = $users->groupBy(function ($user) {
            $dpw = !empty(trim($user->DPW)) ? $user->DPW : 'DPW Jabar';
            $dpd = !empty(trim($user->DPD)) ? $user->DPD : 'DPD ' . strtoupper($user->V_KOTA_GEDUNG);
            return $dpw . '|' . $dpd;
        });

        return $grouped->map(function ($items, $key) use ($nominalBanpers) {
            [$dpw, $dpd] = explode('|', $key);
            $count = $items->count();
            
            return (object) [
                'dpw' => $dpw,
                'dpd' => $dpd,
                'jumlah_anggota' => $count,
                'total_banpers' => $count * $nominalBanpers
            ];
        })->values()->sortBy(['dpw', 'dpd']);
    }

    public function export(Request $request)
    {
        $banpersData = $this->getBanpersData();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="banpers_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($banpersData) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Write headers
            fputcsv($file, ['DPW', 'DPD', 'Jumlah Anggota', 'Nominal per Orang', 'Total Banpers']);
            
            // Write data
            foreach ($banpersData['banpersByWilayah'] as $row) {
                fputcsv($file, [
                    $row->dpw,
                    $row->dpd,
                    number_format($row->jumlah_anggota),
                    'Rp ' . number_format($banpersData['nominalBanpers'], 0, ',', '.'),
                    'Rp ' . number_format($row->total_banpers, 0, ',', '.')
                ]);
            }
            
            fputcsv($file, [
                'TOTAL',
                '',
                number_format($banpersData['totalAnggotaAktif']),
                'Rp ' . number_format($banpersData['nominalBanpers'], 0, ',', '.'),
                'Rp ' . number_format($banpersData['totalBanpers'], 0, ',', '.')
            ]);
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}