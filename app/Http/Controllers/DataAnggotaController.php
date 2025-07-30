<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\SekarPengurus;
use App\Models\ExAnggota;
use App\Models\Iuran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class DataAnggotaController extends Controller
{
    /**
     * Display data anggota with filtering and search
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'anggota');
        
        $data = [
            'activeTab' => $activeTab,
            'dpwOptions' => $this->getDpwOptions(),
            'dpdOptions' => $this->getDpdOptions(),
        ];

        switch ($activeTab) {
            case 'anggota':
                $data['anggota'] = $this->getAnggotaData($request);
                break;
            case 'gptp':
                $data['gptp'] = $this->getGptpData($request);
                break;
            case 'pengurus':
                $data['pengurus'] = $this->getPengurusData($request);
                break;
        }

        return view('data-anggota.index', $data);
    }

    /**
     * Get anggota aktif data with filters
     */
    private function getAnggotaData(Request $request)
    {
        $query = DB::table('users as u')
            ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
            ->leftJoin('t_iuran as i', 'u.nik', '=', 'i.N_NIK')
            ->leftJoin('t_sekar_pengurus as sp', 'u.nik', '=', 'sp.N_NIK')
            ->select([
                'u.nik as NIK',
                'u.name as NAMA',
                DB::raw('COALESCE(k.NO_TELP, "-") as NO_TELP'),
                'u.created_at as TANGGAL_TERDAFTAR',
                'i.IURAN_WAJIB',
                'i.IURAN_SUKARELA',
                DB::raw('COALESCE(sp.DPW, "DPW Jabar") as DPW'),
                DB::raw('COALESCE(sp.DPD, CONCAT("DPD ", UPPER(k.V_KOTA_GEDUNG))) as DPD')
            ])
            ->where('k.V_SHORT_POSISI', 'NOT LIKE', '%GPTP%'); // Exclude GPTP

        // Apply filters
        if ($request->filled('dpw') && $request->dpw !== 'Semua DPW') {
            $query->where('sp.DPW', $request->dpw);
        }

        if ($request->filled('dpd') && $request->dpd !== 'Semua DPD') {
            $query->where('sp.DPD', $request->dpd);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('u.name', 'LIKE', "%{$search}%")
                  ->orWhere('u.nik', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('u.created_at', 'desc')->paginate(10);
    }

    /**
     * Get GPTP data with filters
     */
    private function getGptpData(Request $request)
    {
        $query = DB::table('t_karyawan as k')
            ->leftJoin('users as u', 'k.N_NIK', '=', 'u.nik')
            ->select([
                'k.N_NIK as NIK',
                'k.V_NAMA_KARYAWAN as NAMA',
                DB::raw('COALESCE(k.NO_TELP, "-") as NO_TELP'),
                DB::raw('CASE WHEN u.nik IS NOT NULL THEN u.created_at ELSE NULL END as TANGGAL_TERDAFTAR'),
                DB::raw('CASE WHEN u.nik IS NOT NULL THEN "Terdaftar" ELSE "Belum Terdaftar" END as STATUS'),
                'k.V_SHORT_POSISI as POSISI',
                'k.V_KOTA_GEDUNG as LOKASI'
            ])
            ->where('k.V_SHORT_POSISI', 'LIKE', '%GPTP%'); // Only GPTP

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('k.V_NAMA_KARYAWAN', 'LIKE', "%{$search}%")
                  ->orWhere('k.N_NIK', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('k.V_NAMA_KARYAWAN', 'asc')->paginate(10);
    }

    /**
     * Get pengurus data with filters
     */
    private function getPengurusData(Request $request)
    {
        $query = DB::table('t_sekar_pengurus as sp')
            ->join('t_karyawan as k', 'sp.N_NIK', '=', 'k.N_NIK')
            ->leftJoin('users as u', 'sp.N_NIK', '=', 'u.nik') // Changed to leftJoin
            ->leftJoin('t_sekar_roles as sr', 'sp.ID_ROLES', '=', 'sr.ID')
            ->select([
                'sp.N_NIK as NIK',
                'k.V_NAMA_KARYAWAN as NAMA',
                DB::raw('COALESCE(k.NO_TELP, "-") as NO_TELP'),
                'sp.CREATED_AT as TANGGAL_TERDAFTAR',
                'sp.DPW',
                'sp.DPD',
                'sr.NAME as ROLE',
                'sp.V_SHORT_POSISI as POSISI_SEKAR'
            ]);

        // Apply filters
        if ($request->filled('dpw') && $request->dpw !== 'Semua DPW') {
            $query->where('sp.DPW', $request->dpw);
        }

        if ($request->filled('dpd') && $request->dpd !== 'Semua DPD') {
            $query->where('sp.DPD', $request->dpd);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('k.V_NAMA_KARYAWAN', 'LIKE', "%{$search}%")
                  ->orWhere('sp.N_NIK', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('sp.CREATED_AT', 'desc')->paginate(10);
    }

    /**
     * Get DPW options for filter
     */
    private function getDpwOptions()
    {
        return DB::table('t_sekar_pengurus')
            ->select('DPW')
            ->whereNotNull('DPW')
            ->where('DPW', '!=', '')
            ->distinct()
            ->orderBy('DPW')
            ->pluck('DPW')
            ->prepend('Semua DPW');
    }

    /**
     * Get DPD options for filter
     */
    private function getDpdOptions()
    {
        return DB::table('t_sekar_pengurus')
            ->select('DPD')
            ->whereNotNull('DPD')
            ->where('DPD', '!=', '')
            ->distinct()
            ->orderBy('DPD')
            ->pluck('DPD')
            ->prepend('Semua DPD');
    }

    /**
     * Export data anggota to Excel/CSV
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'anggota');
        $format = $request->get('format', 'csv');
        
        switch ($type) {
            case 'anggota':
                $data = $this->getAnggotaData($request);
                $filename = 'data_anggota_' . date('Y-m-d');
                break;
            case 'gptp':
                $data = $this->getGptpData($request);
                $filename = 'data_gptp_' . date('Y-m-d');
                break;
            case 'pengurus':
                $data = $this->getPengurusData($request);
                $filename = 'data_pengurus_' . date('Y-m-d');
                break;
            default:
                return redirect()->back()->with('error', 'Tipe data tidak valid.');
        }

        if ($format === 'csv') {
            return $this->exportToCsv($data, $filename, $type);
        }

        return redirect()->back()->with('error', 'Format export tidak didukung.');
    }

    /**
     * Export data to CSV
     */
    private function exportToCsv($data, $filename, $type)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($data, $type) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Write headers
            switch ($type) {
                case 'anggota':
                    fputcsv($file, ['NIK', 'Nama', 'No. Telp', 'Tanggal Terdaftar', 'Iuran Wajib', 'Iuran Sukarela', 'DPW', 'DPD']);
                    break;
                case 'gptp':
                    fputcsv($file, ['NIK', 'Nama', 'No. Telp', 'Tanggal Terdaftar', 'Status', 'Posisi', 'Lokasi']);
                    break;
                case 'pengurus':
                    fputcsv($file, ['NIK', 'Nama', 'No. Telp', 'Tanggal Terdaftar', 'DPW', 'DPD', 'Role', 'Posisi SEKAR']);
                    break;
            }
            
            // Write data
            foreach ($data as $row) {
                switch ($type) {
                    case 'anggota':
                        fputcsv($file, [
                            $row->NIK,
                            $row->NAMA,
                            $row->NO_TELP,
                            $row->TANGGAL_TERDAFTAR ? date('d-m-Y', strtotime($row->TANGGAL_TERDAFTAR)) : '',
                            $row->IURAN_WAJIB ? 'Rp ' . number_format($row->IURAN_WAJIB, 0, ',', '.') : '',
                            $row->IURAN_SUKARELA ? 'Rp ' . number_format($row->IURAN_SUKARELA, 0, ',', '.') : '',
                            $row->DPW,
                            $row->DPD
                        ]);
                        break;
                    case 'gptp':
                        fputcsv($file, [
                            $row->NIK,
                            $row->NAMA,
                            $row->NO_TELP,
                            $row->TANGGAL_TERDAFTAR ? date('d-m-Y', strtotime($row->TANGGAL_TERDAFTAR)) : '',
                            $row->STATUS,
                            $row->POSISI,
                            $row->LOKASI
                        ]);
                        break;
                    case 'pengurus':
                        fputcsv($file, [
                            $row->NIK,
                            $row->NAMA,
                            $row->NO_TELP,
                            $row->TANGGAL_TERDAFTAR ? date('d-m-Y', strtotime($row->TANGGAL_TERDAFTAR)) : '',
                            $row->DPW,
                            $row->DPD,
                            $row->ROLE,
                            $row->POSISI_SEKAR
                        ]);
                        break;
                }
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}