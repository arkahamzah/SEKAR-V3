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
            'dpw' => 'nullable|string|max:100',
            'dpd' => 'nullable|string|max:100',
            'iuran_wajib' => 'nullable|numeric|min:0',
            'iuran_sukarela' => 'nullable|numeric|min:0',
        ]);

        // Check if NIK exists in ex-anggota (allow reactivation)
        $exAnggota = ExAnggota::where('N_NIK', $request->nik)->first();
        if ($exAnggota) {
            // Remove unique validation for NIK if it's a reactivation
            $validator = Validator::make($request->all(), [
                'nik' => 'required|string|max:30',
                'nama' => 'required|string|max:150',
                'email' => 'required|email|unique:users,email',
                'no_telp' => 'nullable|string|max:20',
                'dpw' => 'nullable|string|max:100',
                'dpd' => 'nullable|string|max:100',
                'iuran_wajib' => 'nullable|numeric|min:0',
                'iuran_sukarela' => 'nullable|numeric|min:0',
            ]);
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Check if this NIK exists in ex-anggota, if yes delete it (reactivation)
            ExAnggota::where('N_NIK', $request->nik)->delete();

            // Create user account
            $user = User::create([
                'nik' => $request->nik,
                'name' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make('password123'),
                'role' => 'USER',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create karyawan record
            Karyawan::updateOrCreate(
                ['N_NIK' => $request->nik],
                [
                    'V_NAMA_KARYAWAN' => $request->nama,
                    'NO_TELP' => $request->no_telp,
                    'V_SHORT_POSISI' => 'ANGGOTA SEKAR',
                    'V_SHORT_UNIT' => 'SEKAR',
                ]
            );

            // Create pengurus record if DPW/DPD specified
            if ($request->dpw || $request->dpd) {
                SekarPengurus::updateOrCreate(
                    ['N_NIK' => $request->nik],
                    [
                        'DPW' => $request->dpw,
                        'DPD' => $request->dpd,
                        'CREATED_BY' => Auth::user()->nik,
                        'CREATED_AT' => now(),
                    ]
                );
            }

            // Create iuran record if specified
            if ($request->iuran_wajib || $request->iuran_sukarela) {
                DB::table('t_iuran')->updateOrInsert(
                    ['N_NIK' => $request->nik],
                    [
                        'IURAN_WAJIB' => $request->iuran_wajib ?: 0,
                        'IURAN_SUKARELA' => $request->iuran_sukarela ?: 0,
                    ]
                );
            }

            DB::commit();

            return redirect()->route('data-anggota.index')
                ->with('success', $exAnggota ? 'Anggota berhasil diaktifkan kembali' : 'Anggota berhasil ditambahkan');

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
            ->leftJoin('t_sekar_pengurus as sp', 'u.nik', '=', 'sp.N_NIK')
            ->leftJoin('t_iuran as i', 'u.nik', '=', 'i.N_NIK')
            ->select([
                'u.nik',
                'u.name',
                'u.email',
                'k.NO_TELP',
                DB::raw('COALESCE(i.IURAN_WAJIB, 0) as IURAN_WAJIB'),
                DB::raw('COALESCE(i.IURAN_SUKARELA, 0) as IURAN_SUKARELA'),
                'sp.DPW',
                'sp.DPD',
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
            'no_telp' => 'nullable|string|max:20',
            'dpw' => 'nullable|string|max:100',
            'dpd' => 'nullable|string|max:100',
            'iuran_sukarela' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update user
            User::where('nik', $nik)->update([
                'name' => $request->nama,
                'email' => $request->email,
                'updated_at' => now(),
            ]);

            // Update karyawan
            Karyawan::where('N_NIK', $nik)->update([
                'V_NAMA_KARYAWAN' => $request->nama,
                'NO_TELP' => $request->no_telp,
            ]);

            // Update or create pengurus record
            SekarPengurus::updateOrCreate(
                ['N_NIK' => $nik],
                [
                    'DPW' => $request->dpw,
                    'DPD' => $request->dpd,
                    'UPDATED_BY' => Auth::user()->nik,
                    'UPDATED_AT' => now(),
                ]
            );

            // Update or create iuran record (only iuran sukarela)
            $existingIuran = DB::table('t_iuran')->where('N_NIK', $nik)->first();
            DB::table('t_iuran')->updateOrInsert(
                ['N_NIK' => $nik],
                [
                    'IURAN_WAJIB' => $existingIuran->IURAN_WAJIB ?? 0, // Keep existing iuran wajib
                    'IURAN_SUKARELA' => $request->iuran_sukarela ?: 0,
                ]
            );

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

            // Get member data with all related information
            $member = DB::table('users as u')
                ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
                ->leftJoin('t_sekar_pengurus as sp', 'u.nik', '=', 'sp.N_NIK')
                ->leftJoin('t_iuran as i', 'u.nik', '=', 'i.N_NIK')
                ->select([
                    'u.*', 
                    'k.V_NAMA_KARYAWAN', 'k.V_SHORT_POSISI', 'k.V_SHORT_DIVISI', 
                    'k.NO_TELP', 'k.V_KOTA_GEDUNG',
                    'sp.DPW', 'sp.DPD',
                    DB::raw('COALESCE(i.IURAN_WAJIB, 0) as IURAN_WAJIB'),
                    DB::raw('COALESCE(i.IURAN_SUKARELA, 0) as IURAN_SUKARELA')
                ])
                ->where('u.nik', $nik)
                ->first();

            if (!$member) {
                return redirect()->route('data-anggota.index')
                    ->with('error', 'Data anggota tidak ditemukan');
            }

            // Move to ex-anggota
            ExAnggota::create([
                'N_NIK' => $member->nik,
                'V_NAMA_KARYAWAN' => $member->name,
                'V_SHORT_POSISI' => $member->V_SHORT_POSISI ?: 'ANGGOTA SEKAR',
                'V_SHORT_DIVISI' => $member->V_SHORT_DIVISI ?: 'SEKAR',
                'NO_TELP' => $member->NO_TELP,
                'TGL_KELUAR' => now(),
                'DPP' => null,
                'DPW' => $member->DPW,
                'DPD' => $member->DPD,
                'V_KOTA_GEDUNG' => $member->V_KOTA_GEDUNG,
                'CREATED_BY' => Auth::user()->nik,
                'CREATED_AT' => now(),
            ]);

            // Delete from related tables
            User::where('nik', $nik)->delete();
            Karyawan::where('N_NIK', $nik)->delete();
            SekarPengurus::where('N_NIK', $nik)->delete();
            DB::table('t_iuran')->where('N_NIK', $nik)->delete();

            DB::commit();

            return redirect()->route('data-anggota.index')
                ->with('success', 'Anggota berhasil dihapus dan dipindahkan ke ex-anggota');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('data-anggota.index')
                ->with('error', 'Gagal menghapus anggota: ' . $e->getMessage());
        }
    }

    /**
     * Get anggota aktif data with filters - FIXED QUERY
     */
    private function getAnggotaData(Request $request)
    {
        $query = DB::table('users as u')
            ->join('t_karyawan as k', 'u.nik', '=', 'k.N_NIK')
            ->leftJoin('t_iuran as i', 'u.nik', '=', 'i.N_NIK')
            ->leftJoin('t_sekar_pengurus as sp', 'u.nik', '=', 'sp.N_NIK')
            ->select([
                'u.nik as NIK',
                'k.V_NAMA_KARYAWAN as NAMA',
                DB::raw('COALESCE(k.NO_TELP, "-") as NO_TELP'),
                'u.created_at as TANGGAL_TERDAFTAR', // FIXED: dari users.created_at
                DB::raw('COALESCE(i.IURAN_WAJIB, 0) as IURAN_WAJIB'), // FIXED: dari t_iuran
                DB::raw('COALESCE(i.IURAN_SUKARELA, 0) as IURAN_SUKARELA'), // FIXED: dari t_iuran
                DB::raw('COALESCE(sp.DPW, "-") as DPW') // FIXED: dari t_sekar_pengurus
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
                $q->where('k.V_NAMA_KARYAWAN', 'LIKE', "%{$search}%")
                  ->orWhere('u.nik', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('k.V_NAMA_KARYAWAN', 'asc')->paginate(10);
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
            ->leftJoin('users as u', 'sp.N_NIK', '=', 'u.nik')
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

        return $query->orderBy('k.V_NAMA_KARYAWAN', 'asc')->paginate(10);
    }

    /**
     * Get Ex-Anggota data with filters
     */
    private function getExAnggotaData(Request $request)
    {
        $query = ExAnggota::query();

        // Apply filters
        if ($request->filled('dpw') && $request->dpw !== 'Semua DPW') {
            $query->byDpw($request->dpw);
        }
        
        if ($request->filled('dpd') && $request->dpd !== 'Semua DPD') {
            $query->byDpd($request->dpd);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        return $query->orderBy('TGL_KELUAR', 'desc')->paginate(10);
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
     * Export data anggota to CSV
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
            case 'ex-anggota':
                $data = $this->getExAnggotaData($request);
                $filename = 'data_ex_anggota_' . date('Y-m-d');
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
                    fputcsv($file, ['NIK', 'Nama', 'No. Telp', 'Tanggal Terdaftar', 'Iuran Wajib', 'Iuran Sukarela', 'DPW']);
                    break;
                case 'gptp':
                    fputcsv($file, ['NIK', 'Nama', 'No. Telp', 'Tanggal Terdaftar', 'Status', 'Posisi', 'Lokasi']);
                    break;
                case 'pengurus':
                    fputcsv($file, ['NIK', 'Nama', 'No. Telp', 'Tanggal Terdaftar', 'DPW', 'DPD', 'Role', 'Posisi SEKAR']);
                    break;
                case 'ex-anggota':
                    fputcsv($file, ['NIK', 'Nama', 'Posisi Terakhir', 'Tanggal Keluar', 'Alasan Keluar', 'DPW', 'DPD']);
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
                            $row->DPW
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
                    case 'ex-anggota':
                        fputcsv($file, [
                            $row->N_NIK,
                            $row->V_NAMA_KARYAWAN,
                            $row->V_SHORT_POSISI,
                            $row->TGL_KELUAR ? date('d-m-Y', strtotime($row->TGL_KELUAR)) : '',
                            $row->ALASAN_KELUAR,
                            $row->DPW,
                            $row->DPD
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