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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class DataAnggotaController extends Controller
{
    /**
     * Display data anggota with filtering and search
     */
    public function index(Request $request)
    {
        if ($request->get('tab') === 'ex-anggota' && !Auth::user()->hasRole('ADM')) {
            return redirect()->route('data-anggota.index')->with('error', 'Anda tidak memiliki hak akses untuk melihat halaman ini.');
        }

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
            
            case 'ex-anggota':
                $data['ex_anggota'] = $this->getExAnggotaData($request);
                break;
        }

        return view('data-anggota.index', $data);
    }

    /**
     * Show the form for creating a new member (Super Admin only)
     */
    public function create()
    {
        $this->checkSuperAdminAccess();
        
        return view('data-anggota.create', [
            'dpwList' => $this->getDpwOptions()->filter(function($dpw) { return $dpw !== 'Semua DPW'; }),
            'dpdList' => $this->getDpdOptions()->filter(function($dpd) { return $dpd !== 'Semua DPD'; }),
        ]);
    }

    /**
     * Store a newly created member (Super Admin only)
     */
    public function store(Request $request)
    {
        $this->checkSuperAdminAccess();

        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|max:30|unique:users,nik',
            'nama' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'no_telp' => 'nullable|string|max:20',
            'dpw' => 'required|string|max:100',
            'dpd' => 'required|string|max:100',
            'iuran_wajib' => 'nullable|numeric|min:0',
            'iuran_sukarela' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $exAnggota = ExAnggota::where('N_NIK', $request->nik)->first();
            if ($exAnggota) {
                $exAnggota->delete();
            }

            // Create user account
            $user = User::create([
                'nik' => $request->nik,
                'name' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make('password123'),
                'membership_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create or update karyawan record with correct DPW/DPD
            Karyawan::updateOrCreate(
                ['N_NIK' => $request->nik],
                [
                    'V_NAMA_KARYAWAN' => $request->nama,
                    'V_SHORT_POSISI' => 'ANGGOTA SEKAR',
                    'V_SHORT_UNIT' => 'SEKAR',
                    'DPW' => $request->dpw,
                    'DPD' => $request->dpd,
                ]
            );

            // Create iuran record if specified
            if ($request->filled('iuran_wajib') || $request->filled('iuran_sukarela')) {
                Iuran::updateOrCreate(
                    ['N_NIK' => $request->nik],
                    [
                        'IURAN_WAJIB' => $request->iuran_wajib ?: 25000,
                        'IURAN_SUKARELA' => $request->iuran_sukarela ?: 0,
                        'TAHUN' => now()->year,
                        'STATUS_BAYAR' => 'AKTIF',
                        'CREATED_BY' => Auth::user()->nik,
                        'CREATED_AT' => now()
                    ]
                );
            }

            DB::commit();

            return redirect()->route('data-anggota.index')
                ->with('success', $exAnggota ? 'Anggota berhasil diaktifkan kembali.' : 'Anggota baru berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan anggota: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing member (Super Admin only)
     */
    public function edit($nik)
    {
        $this->checkSuperAdminAccess();

        $member = DB::table('users as u')
            ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
            ->leftJoin('t_iuran as i', 'u.nik', '=', 'i.N_NIK')
            ->select([
                'u.nik',
                'u.name',
                'u.email',
                'k.V_KOTA_GEDUNG',
                DB::raw('COALESCE(i.IURAN_WAJIB, 0) as IURAN_WAJIB'),
                DB::raw('COALESCE(i.IURAN_SUKARELA, 0) as IURAN_SUKARELA'),
                'k.DPW',
                'k.DPD',
                'u.created_at as TANGGAL_TERDAFTAR'
            ])
            ->where('u.nik', $nik)
            ->first();

        if (!$member) {
            return redirect()->route('data-anggota.index')
                ->with('error', 'Data anggota tidak ditemukan');
        }

        return view('data-anggota.edit', [
            'member' => $member,
            'dpwList' => $this->getDpwOptions()->filter(function($dpw) { return $dpw !== 'Semua DPW'; }),
            'dpdList' => $this->getDpdOptions()->filter(function($dpd) { return $dpd !== 'Semua DPD'; }),
        ]);
    }

    /**
     * Update member data (Super Admin only)
     */
    public function update(Request $request, $nik)
    {
        $this->checkSuperAdminAccess();

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $nik . ',nik',
            'dpw' => 'required|string|max:100',
            'dpd' => 'required|string|max:100',
            'iuran_sukarela' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            User::where('nik', $nik)->update([
                'name' => $request->nama,
                'email' => $request->email,
                'updated_at' => now(),
            ]);

            Karyawan::where('N_NIK', $nik)->update([
                'V_NAMA_KARYAWAN' => $request->nama,
                'DPW' => $request->dpw,
                'DPD' => $request->dpd,
            ]);
            
            Iuran::where('N_NIK', $nik)->update([
                'IURAN_SUKARELA' => $request->iuran_sukarela ?: 0,
                'UPDATE_BY' => Auth::user()->nik,
                'UPDATED_AT' => now(),
            ]);

            DB::commit();

            return redirect()->route('data-anggota.index')
                ->with('success', 'Data anggota berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui data anggota: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove member (Super Admin only) - Soft delete to ex-anggota
     */
    public function destroy($nik)
    {
        $this->checkSuperAdminAccess();

        try {
            DB::beginTransaction();

            // FIXED: Query now uses COALESCE to get DPW/DPD from t_karyawan first,
            // then falls back to t_sekar_pengurus for older data.
            $member = DB::table('users as u')
                ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
                ->leftJoin('t_sekar_pengurus as sp', 'u.nik', '=', 'sp.N_NIK')
                ->leftJoin('t_iuran as i', 'u.nik', '=', 'i.N_NIK')
                ->select([
                    'u.*', 
                    'k.V_NAMA_KARYAWAN', 'k.V_SHORT_POSISI', 'k.V_SHORT_DIVISI', 
                    'k.V_KOTA_GEDUNG',
                    DB::raw('COALESCE(k.DPW, sp.DPW) as DPW'), // Fallback for DPW
                    DB::raw('COALESCE(k.DPD, sp.DPD) as DPD'), // Fallback for DPD
                    DB::raw('COALESCE(i.IURAN_WAJIB, 0) as IURAN_WAJIB'),
                    DB::raw('COALESCE(i.IURAN_SUKARELA, 0) as IURAN_SUKARELA')
                ])
                ->where('u.nik', $nik)
                ->first();

            if (!$member) {
                return redirect()->route('data-anggota.index')
                    ->with('error', 'Data anggota tidak ditemukan');
            }

            ExAnggota::create([
                'N_NIK' => $member->nik,
                'V_NAMA_KARYAWAN' => $member->name,
                'V_SHORT_POSISI' => $member->V_SHORT_POSISI,
                'V_SHORT_DIVISI' => $member->V_SHORT_DIVISI,
                'TGL_KELUAR' => now(),
                'DPW' => $member->DPW, // Now correctly sourced with fallback
                'DPD' => $member->DPD, // Now correctly sourced with fallback
                'V_KOTA_GEDUNG' => $member->V_KOTA_GEDUNG,
                'CREATED_BY' => Auth::user()->nik,
                'CREATED_AT' => now(),
                'IURAN_WAJIB_TERAKHIR' => $member->IURAN_WAJIB,
                'IURAN_SUKARELA_TERAKHIR' => $member->IURAN_SUKARELA,
            ]);

            User::where('nik', $nik)->delete();
            SekarPengurus::where('N_NIK', $nik)->delete();
            Iuran::where('N_NIK', $nik)->delete();
            
            DB::commit();

            return redirect()->route('data-anggota.index')
                ->with('success', 'Anggota berhasil dinonaktifkan dan dipindahkan ke ex-anggota.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('data-anggota.index')
                ->with('error', 'Gagal menonaktifkan anggota: ' . $e->getMessage());
        }
    }

    /**
     * Get anggota aktif data with filters
     */
    private function getAnggotaData(Request $request)
    {
        $query = DB::table('users as u')
            ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
            ->leftJoin('t_iuran as i', 'u.nik', '=', 'i.N_NIK')
            ->select([
                'u.nik as NIK',
                'k.V_NAMA_KARYAWAN as NAMA',
                DB::raw('COALESCE(k.V_KOTA_GEDUNG, "-") as LOKASI'),
                'u.created_at as TANGGAL_TERDAFTAR',
                DB::raw('COALESCE(i.IURAN_WAJIB, 0) as IURAN_WAJIB'),
                DB::raw('COALESCE(i.IURAN_SUKARELA, 0) as IURAN_SUKARELA'),
                'k.DPW as DPW',
                'k.DPD as DPD'
            ])
            ->where('k.V_SHORT_POSISI', 'NOT LIKE', '%GPTP%');

        if ($request->filled('dpw') && $request->dpw !== 'Semua DPW') {
            $query->where('k.DPW', $request->dpw);
        }
        
        if ($request->filled('dpd') && $request->dpd !== 'Semua DPD') {
            $query->where('k.DPD', $request->dpd);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('k.V_NAMA_KARYAWAN', 'LIKE', "%{$search}%")
                  ->orWhere('u.nik', 'LIKE', "%{$search}%");
            });
        }

        // --- PERUBAHAN DI SINI ---
        // Mengurutkan 7 NIK spesifik di awal, sisanya diacak
        return $query->orderByRaw("
            CASE
                WHEN u.nik = '401031' THEN 1
                WHEN u.nik = '401032' THEN 2
                WHEN u.nik = '401033' THEN 3
                WHEN u.nik = '401034' THEN 4
                WHEN u.nik = '401035' THEN 5
                WHEN u.nik = '501031' THEN 6
                WHEN u.nik = '501032' THEN 7
                ELSE 8
            END, RAND()
        ")->paginate(10);
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
                DB::raw('COALESCE(k.V_KOTA_GEDUNG, "-") as LOKASI'),
                DB::raw('CASE WHEN u.nik IS NOT NULL THEN u.created_at ELSE NULL END as TANGGAL_TERDAFTAR'),
                DB::raw('CASE WHEN u.nik IS NOT NULL THEN "Terdaftar" ELSE "Belum Terdaftar" END as STATUS'),
                'k.V_SHORT_POSISI as POSISI'
            ])
            ->where('k.V_SHORT_POSISI', 'LIKE', '%GPTP%');

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
            ->leftJoin('users as u', 'sp.N_NIK', '=', 'u.nik')
            ->leftJoin('t_sekar_roles as sr', 'sp.ID_ROLES', '=', 'sr.ID')
            ->select([
                'sp.N_NIK as NIK',
                'k.V_NAMA_KARYAWAN as NAMA',
                DB::raw('COALESCE(k.V_KOTA_GEDUNG, "-") as LOKASI'),
                'sp.CREATED_AT as TANGGAL_TERDAFTAR',
                'sp.DPW',
                'sp.DPD',
                'sr.NAME as ROLE',
                'sp.V_SHORT_POSISI as POSISI_SEKAR'
            ]);

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

        return $query->orderBy('k.V_NAMA_KARYAWAN', 'asc')->paginate(10);
    }

    /**
     * Get Ex-Anggota data with filters
     */
    private function getExAnggotaData(Request $request)
    {
        $query = ExAnggota::query()
            ->select([
                'N_NIK', 'V_NAMA_KARYAWAN', 'V_SHORT_POSISI', 'TGL_KELUAR',
                'ALASAN_KELUAR', 'DPW', 'DPD'
            ]);

        if ($request->filled('dpw') && $request->dpw !== 'Semua DPW') {
            $query->where('DPW', $request->dpw);
        }
        
        if ($request->filled('dpd') && $request->dpd !== 'Semua DPD') {
            $query->where('DPD', $request->dpd);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('V_NAMA_KARYAWAN', 'LIKE', "%{$search}%")
                  ->orWhere('N_NIK', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('TGL_KELUAR', 'desc')->paginate(10);
    }

    /**
     * Get DPW options for filter
     */
    private function getDpwOptions()
    {
        return Karyawan::select('DPW')
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
        return Karyawan::select('DPD')
            ->whereNotNull('DPD')
            ->where('DPD', '!=', '')
            ->distinct()
            ->orderBy('DPD')
            ->pluck('DPD')
            ->prepend('Semua DPD');
    }

    /**
     * Export data anggota to CSV
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'anggota');
        $format = $request->get('format', 'csv');
        
        $requestForExport = $request->duplicate();
        $requestForExport->offsetUnset('page');

        $data = collect();
        $filename = 'data_' . $type . '_' . date('Y-m-d');

        switch ($type) {
            case 'anggota':
                $data = $this->getAnggotaData($requestForExport)->get();
                break;
            case 'gptp':
                $data = $this->getGptpData($requestForExport)->get();
                break;
            case 'pengurus':
                $data = $this->getPengurusData($requestForExport)->get();
                break;
            case 'ex-anggota':
                $data = $this->getExAnggotaData($requestForExport)->get();
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
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            switch ($type) {
                case 'anggota':
                    fputcsv($file, ['NIK', 'Nama', 'Lokasi', 'Tanggal Terdaftar', 'Iuran Wajib', 'Iuran Sukarela', 'DPW', 'DPD']);
                    break;
                case 'gptp':
                    fputcsv($file, ['NIK', 'Nama', 'Lokasi', 'Tanggal Terdaftar', 'Status', 'Posisi']);
                    break;
                case 'pengurus':
                    fputcsv($file, ['NIK', 'Nama', 'Lokasi', 'Tanggal Terdaftar', 'DPW', 'DPD', 'Role', 'Posisi SEKAR']);
                    break;
                case 'ex-anggota':
                    fputcsv($file, ['NIK', 'Nama', 'Posisi Terakhir', 'Tanggal Keluar', 'Alasan Keluar', 'DPW', 'DPD']);
                    break;
            }
            
            foreach ($data as $row) {
                switch ($type) {
                    case 'anggota':
                        fputcsv($file, [
                            $row->NIK, $row->NAMA, $row->LOKASI,
                            $row->TANGGAL_TERDAFTAR ? date('d-m-Y', strtotime($row->TANGGAL_TERDAFTAR)) : '',
                            'Rp ' . number_format($row->IURAN_WAJIB, 0, ',', '.'),
                            'Rp ' . number_format($row->IURAN_SUKARELA, 0, ',', '.'),
                            $row->DPW, $row->DPD
                        ]);
                        break;
                    case 'gptp':
                        fputcsv($file, [
                            $row->NIK, $row->NAMA, $row->LOKASI,
                            $row->TANGGAL_TERDAFTAR ? date('d-m-Y', strtotime($row->TANGGAL_TERDAFTAR)) : '',
                            $row->STATUS, $row->POSISI
                        ]);
                        break;
                    case 'pengurus':
                        fputcsv($file, [
                            $row->NIK, $row->NAMA, $row->LOKASI,
                            $row->TANGGAL_TERDAFTAR ? date('d-m-Y', strtotime($row->TANGGAL_TERDAFTAR)) : '',
                            $row->DPW, $row->DPD, $row->ROLE, $row->POSISI_SEKAR
                        ]);
                        break;
                    case 'ex-anggota':
                        fputcsv($file, [
                            $row->N_NIK, $row->V_NAMA_KARYAWAN, $row->V_SHORT_POSISI,
                            $row->TGL_KELUAR ? date('d-m-Y', strtotime($row->TGL_KELUAR)) : '',
                            $row->ALASAN_KELUAR, $row->DPW, $row->DPD
                        ]);
                        break;
                }
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }


    private function checkSuperAdminAccess()
    {
        $user = Auth::user();
        
        $isSuperAdmin = DB::table('t_sekar_pengurus as sp')
            ->join('t_sekar_roles as sr', 'sp.ID_ROLES', '=', 'sr.ID')
            ->where('sp.N_NIK', $user->nik)
            ->where('sr.NAME', 'ADM')
            ->exists();

        if (!$isSuperAdmin) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengelola data anggota.');
        }
    }
}