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
    public function index(Request $request)
    {
        $statistics = Cache::remember('dashboard_main_statistics', 10, function () {
            return $this->getStatistics();
        });

        $karyawanDpw = DB::table('v_karyawan_base')
            ->select(DB::raw('DPW COLLATE utf8mb4_general_ci as DPW'))
            ->whereNotNull('DPW')->where('DPW', '!=', '');

        $allDpwOptions = DB::table('t_sekar_pengurus')
            ->select(DB::raw('DPW'))
            ->whereNotNull('DPW')->where('DPW', '!=', '')
            ->union($karyawanDpw)
            ->distinct()
            ->orderBy('DPW')
            ->pluck('DPW');

        $mappingWithStats = $this->getDpwMappingWithStats($request);
        $greeting = $this->getGreetingData();

        return view('dashboard', array_merge($statistics, [
            'greeting' => $greeting,
            'mappingWithStats' => $mappingWithStats,
            'allDpwOptions' => $allDpwOptions
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

        $statusMessage = 'Selamat datang di portal SEKAR Telkom!';

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
        $anggotaAktif = Karyawan::where('STATUS_ANGGOTA', 'Terdaftar')
            ->where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')
            ->count();
        $totalPengurus = SekarPengurus::count();
        $anggotaKeluar = ExAnggota::count();
        $totalKaryawanNonGPTP = Karyawan::where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')->count();
        $nonAnggota = max(0, $totalKaryawanNonGPTP - $anggotaAktif);
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
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

    private function getDpwMappingWithStats(Request $request)
    {
        $karyawanMappings = DB::table('v_karyawan_base')
            ->select(
                DB::raw('DPW COLLATE utf8mb4_general_ci as DPW'),
                DB::raw('DPD COLLATE utf8mb4_general_ci as DPD')
            )
            ->whereNotNull('DPW')->where('DPW', '!=', '')
            ->whereNotNull('DPD')->where('DPD', '!=', '');

        $baseQuery = DB::table('t_sekar_pengurus')
            ->select('DPW', 'DPD')
            ->whereNotNull('DPW')->where('DPW', '!=', '')
            ->whereNotNull('DPD')->where('DPD', '!=', '')
            ->union($karyawanMappings);

        $mappings = DB::query()->fromSub($baseQuery, 'mappings')->select('DPW', 'DPD')->distinct();

        $anggotaAktifCounts = DB::table('v_karyawan_base')->select(DB::raw('DPW COLLATE utf8mb4_general_ci as DPW'), DB::raw('DPD COLLATE utf8mb4_general_ci as DPD'), DB::raw('count(*) as count'))->where('STATUS_ANGGOTA', 'Terdaftar')->where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')->groupBy('DPW', 'DPD');
        $totalKaryawanCounts = DB::table('v_karyawan_base')->select(DB::raw('DPW COLLATE utf8mb4_general_ci as DPW'), DB::raw('DPD COLLATE utf8mb4_general_ci as DPD'), DB::raw('count(*) as count'))->where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%')->groupBy('DPW', 'DPD');
        
        $pengurusCounts = SekarPengurus::query()->select('DPW', 'DPD', DB::raw('count(*) as count'))->groupBy('DPW', 'DPD');
        $anggotaKeluarCounts = ExAnggota::query()->select('DPW', 'DPD', DB::raw('count(*) as count'))->groupBy('DPW', 'DPD');

        $query = $mappings
            ->leftJoinSub($anggotaAktifCounts, 'aa', fn ($join) => $join->on('mappings.DPW', '=', 'aa.DPW')->on('mappings.DPD', '=', 'aa.DPD'))
            ->leftJoinSub($pengurusCounts, 'p', fn ($join) => $join->on('mappings.DPW', '=', 'p.DPW')->on('mappings.DPD', '=', 'p.DPD'))
            ->leftJoinSub($anggotaKeluarCounts, 'ak', fn ($join) => $join->on('mappings.DPW', '=', 'ak.DPW')->on('mappings.DPD', '=', 'ak.DPD'))
            ->leftJoinSub($totalKaryawanCounts, 'tk', fn ($join) => $join->on('mappings.DPW', '=', 'tk.DPW')->on('mappings.DPD', '=', 'tk.DPD'))
            ->select(
                'mappings.DPW as dpw',
                'mappings.DPD as dpd',
                DB::raw('COALESCE(aa.count, 0) as anggota_aktif'),
                DB::raw('COALESCE(p.count, 0) as pengurus'),
                DB::raw('COALESCE(ak.count, 0) as anggota_keluar'),
                DB::raw('GREATEST(0, COALESCE(tk.count, 0) - COALESCE(aa.count, 0)) as non_anggota')
            );

        if ($request->filled('dpw')) {
            $query->where('mappings.DPW', $request->dpw);
        }
        if ($request->filled('dpd')) {
            $query->where('mappings.DPD', 'like', '%' . $request->dpd . '%');
        }

        return $query->orderBy('dpw')->orderBy('dpd')->paginate(10)->withQueryString();
    }
}