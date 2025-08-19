<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\SekarPengurus;
use App\Models\ExAnggota;
use App\Models\Iuran;
use App\Models\SekarRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;

class DataAnggotaController extends Controller
{
    /**
     * Display data anggota with filtering and search.
     */
    public function index(Request $request)
    {
        // Redirect if user without ADM role tries to access ex-anggota tab
        if ($request->get('tab') === 'ex-anggota' && !Auth::user()->hasRole('ADM')) {
            return redirect()->route('data-anggota.index')->with('error', 'Anda tidak memiliki hak akses untuk melihat halaman ini.');
        }

        $activeTab = $request->get('tab', 'anggota');

        $data = [
            'activeTab'  => $activeTab,
            'dpwOptions' => $this->getDpwOptions(),
            'dpdOptions' => $this->getDpdOptions($request),
        ];

        // Fetch data based on the active tab
        switch ($activeTab) {
            case 'anggota':
                $data['anggota'] = $this->getAnggotaData($request)->paginate(10)->withQueryString();
                break;
            case 'gptp':
                $data['gptp'] = $this->getGptpData($request)->paginate(10)->withQueryString();
                break;
            case 'pengurus':
                $data['pengurus'] = $this->getPengurusData($request)->paginate(10)->withQueryString();
                break;
            case 'ex-anggota':
                $data['ex_anggota'] = $this->getExAnggotaData($request)->paginate(10)->withQueryString();
                break;
        }

        return view('data-anggota.index', $data);
    }

    /**
     * Show the form for creating a new member (Super Admin only).
     */
    public function create()
    {
        $this->checkSuperAdminAccess();

        return view('data-anggota.create', [
            'dpwList' => $this->getDpwOptions()->reject(fn($dpw) => $dpw === 'Semua DPW'),
            'dpdList' => $this->getDpdOptions(new Request())->reject(fn($dpd) => $dpd === 'Semua DPD'),
        ]);
    }

    /**
     * Store a newly created member (Super Admin only).
     */
    public function store(Request $request)
    {
        $this->checkSuperAdminAccess();

        $validator = Validator::make($request->all(), [
            'nik'              => 'required|string|max:30|unique:users,nik',
            'nama'             => 'required|string|max:150',
            'email'            => 'required|email|unique:users,email',
            'dpw'              => 'required|string|max:100',
            'dpd'              => 'required|string|max:100',
            'iuran_wajib'      => 'nullable|numeric|min:0',
            'iuran_sukarela'   => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // If the NIK exists in ex-members, delete it to reactivate.
            $exAnggota = ExAnggota::where('N_NIK', $request->nik)->first();
            if ($exAnggota) {
                $exAnggota->delete();
            }

            // Create User
            User::create([
                'nik'      => $request->nik,
                'name'     => $request->nama,
                'email'    => $request->email,
                'password' => Hash::make('password123'), // Default password
            ]);

            // Create or Update Karyawan data
            Karyawan::updateOrCreate(
                ['N_NIK' => $request->nik],
                [
                    'V_NAMA_KARYAWAN' => $request->nama,
                    'V_SHORT_POSISI'  => 'ANGGOTA SEKAR',
                    'V_SHORT_UNIT'    => 'SEKAR',
                    'DPW'             => $request->dpw,
                    'DPD'             => $request->dpd,
                ]
            );

            // Create or Update Iuran data
            Iuran::updateOrCreate(
                ['N_NIK' => $request->nik],
                [
                    'IURAN_WAJIB'    => $request->iuran_wajib ?? 25000,
                    'IURAN_SUKARELA' => $request->iuran_sukarela ?? 0,
                    'TAHUN'          => now()->year,
                    'STATUS_BAYAR'   => 'AKTIF',
                    'CREATED_BY'     => Auth::user()->nik,
                ]
            );

            DB::commit();

            $message = $exAnggota ? 'Anggota berhasil diaktifkan kembali.' : 'Anggota baru berhasil ditambahkan.';
            return redirect()->route('data-anggota.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menambahkan anggota: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Show the form to add a new board member.
     */
    public function createPengurus()
    {
        $this->checkSuperAdminAccess();
        return view('data-anggota.create-pengurus', ['roles' => SekarRole::all()]);
    }

    /**
     * Store a new board member.
     */
/**
     * Store a new board member.
     */
    public function storePengurus(Request $request)
    {
        $this->checkSuperAdminAccess();

        $validator = Validator::make($request->all(), [
            'nik'      => 'required|string|max:30|exists:t_karyawan,N_NIK|unique:t_sekar_pengurus,N_NIK',
            'id_roles' => 'required|exists:t_sekar_roles,ID',
        ], [
            'nik.unique' => 'Karyawan yang dipilih sudah terdaftar sebagai pengurus.',
            'nik.exists' => 'NIK Karyawan tidak valid atau tidak ditemukan.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // =================================================================
            // PERBAIKAN: Menggunakan where() untuk mencari berdasarkan N_NIK
            // =================================================================
            $karyawan = Karyawan::where('N_NIK', $request->nik)->firstOrFail();

            SekarPengurus::create([
                'N_NIK'            => $karyawan->N_NIK,
                'V_SHORT_POSISI'   => $karyawan->V_SHORT_POSISI,
                'V_SHORT_UNIT'     => $karyawan->V_SHORT_UNIT,
                'DPP'              => $karyawan->DPP,
                'DPW'              => $karyawan->DPW,
                'DPD'              => $karyawan->DPD,
                'ID_ROLES'         => $request->id_roles,
                'CREATED_BY'       => Auth::user()->nik,
            ]);

            return redirect()->route('data-anggota.index', ['tab' => 'pengurus'])->with('success', 'Pengurus baru berhasil ditambahkan.');

        } catch (\Exception $e) {
            // Jika terjadi error lain, tampilkan pesannya untuk debugging
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Get employee info via AJAX.
     */
    public function getKaryawanInfo($nik)
    {
        $karyawan = Karyawan::where('N_NIK', $nik)->whereHas('user')->first();

        if (!$karyawan) {
            return response()->json(['status' => 'error', 'message' => 'Anggota tidak ditemukan atau belum terdaftar sebagai user.']);
        }

        if (SekarPengurus::where('N_NIK', $nik)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Karyawan ini sudah terdaftar sebagai pengurus.']);
        }

        return response()->json(['status' => 'success', 'data' => $karyawan]);
    }


    /**
     * Show the form for editing a member.
     */
    public function edit($nik)
    {
        $this->checkSuperAdminAccess();

        $member = User::with(['karyawan', 'iuran'])->where('nik', $nik)->firstOrFail();

        return view('data-anggota.edit', [
            'member'  => $member,
            'dpwList' => $this->getDpwOptions()->reject(fn($dpw) => $dpw === 'Semua DPW'),
            'dpdList' => $this->getDpdOptions(new Request())->reject(fn($dpd) => $dpd === 'Semua DPD'),
        ]);
    }

    /**
     * Update member data.
     */
    public function update(Request $request, $nik)
    {
        $this->checkSuperAdminAccess();

        $user = User::where('nik', $nik)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'nama'           => 'required|string|max:150',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'dpw'            => 'required|string|max:100',
            'dpd'            => 'required|string|max:100',
            'iuran_sukarela' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Update User
            $user->update([
                'name'  => $request->nama,
                'email' => $request->email,
            ]);

            // Update Karyawan
            Karyawan::where('N_NIK', $nik)->update([
                'V_NAMA_KARYAWAN' => $request->nama,
                'DPW'             => $request->dpw,
                'DPD'             => $request->dpd,
            ]);

            // Update Iuran
            Iuran::where('N_NIK', $nik)->update([
                'IURAN_SUKARELA' => $request->iuran_sukarela ?? 0,
                'UPDATE_BY'      => Auth::user()->nik,
            ]);

            DB::commit();
            return redirect()->route('data-anggota.index')->with('success', 'Data anggota berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Deactivate a member and move them to ex-members.
     */
    public function destroy($nik)
    {
        $this->checkSuperAdminAccess();

        DB::beginTransaction();
        try {
            $user = User::with(['karyawan', 'iuran', 'pengurus'])->where('nik', $nik)->first();

            if (!$user) {
                return redirect()->route('data-anggota.index')->with('error', 'Data anggota tidak ditemukan.');
            }

            $karyawan = $user->karyawan;
            $iuran = $user->iuran;

            // Create ExAnggota record
            ExAnggota::create([
                'N_NIK'                   => $user->nik,
                'V_NAMA_KARYAWAN'         => $user->name,
                'V_SHORT_POSISI'          => $karyawan->V_SHORT_POSISI ?? null,
                'V_SHORT_DIVISI'          => $karyawan->V_SHORT_DIVISI ?? null,
                'TGL_KELUAR'              => now(),
                'DPW'                     => $karyawan->DPW ?? $user->pengurus->DPW ?? null,
                'DPD'                     => $karyawan->DPD ?? $user->pengurus->DPD ?? null,
                'V_KOTA_GEDUNG'           => $karyawan->V_KOTA_GEDUNG ?? null,
                'CREATED_BY'              => Auth::user()->nik,
                'IURAN_WAJIB_TERAKHIR'    => $iuran->IURAN_WAJIB ?? 0,
                'IURAN_SUKARELA_TERAKHIR' => $iuran->IURAN_SUKARELA ?? 0,
            ]);

            // Clean up original records
            if ($user->pengurus) $user->pengurus->delete();
            if ($user->iuran) $user->iuran->delete();
            $user->delete(); // This will also delete Karyawan if using cascading deletes

            DB::commit();
            return redirect()->route('data-anggota.index')->with('success', 'Anggota berhasil dinonaktifkan.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('data-anggota.index')->with('error', 'Gagal menonaktifkan anggota: ' . $e->getMessage());
        }
    }

    /**
     * Export data to a CSV file.
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'anggota');
        $filename = 'data_' . $type . '_' . now()->format('Ymd');
        
        $data = collect();
        
        switch ($type) {
            case 'anggota':
                $data = $this->getAnggotaData($request, false)->get();
                break;
            case 'gptp':
                $data = $this->getGptpData($request, false)->get();
                break;
            case 'pengurus':
                $data = $this->getPengurusData($request, false)->get();
                break;
            case 'ex-anggota':
                $data = $this->getExAnggotaData($request, false)->get();
                break;
            default:
                return redirect()->back()->with('error', 'Tipe data tidak valid.');
        }

        return $this->exportToCsv($data, $filename, $type);
    }
    
    // ===================================================================
    // PRIVATE HELPER METHODS
    // ===================================================================

    private function getAnggotaData(Request $request)
    {
        $query = Karyawan::query()
            ->join('users', 't_karyawan.N_NIK', '=', 'users.nik')
            ->leftJoin('t_iuran', 't_karyawan.N_NIK', '=', 't_iuran.N_NIK')
            ->select([
                'users.nik as NIK',
                't_karyawan.V_NAMA_KARYAWAN as NAMA',
                't_karyawan.V_KOTA_GEDUNG as LOKASI',
                'users.created_at as TANGGAL_TERDAFTAR',
                DB::raw('COALESCE(t_iuran.IURAN_WAJIB, 0) as IURAN_WAJIB'),
                DB::raw('COALESCE(t_iuran.IURAN_SUKARELA, 0) as IURAN_SUKARELA'),
                't_karyawan.DPW',
                't_karyawan.DPD'
            ])
            ->where('t_karyawan.V_SHORT_POSISI', 'NOT LIKE', '%GPTP%');
        
        $this->applyCommonFilters($query, $request, 't_karyawan', 'users');

        // PERFORMANCE: Removed slow ORDER BY RAND(). Sorting by name is more predictable and fast.
        return $query->orderBy('t_karyawan.V_NAMA_KARYAWAN', 'asc');
    }

    private function getGptpData(Request $request)
    {
        $query = Karyawan::query()
            ->leftJoin('users', 't_karyawan.N_NIK', '=', 'users.nik')
            ->select([
                't_karyawan.N_NIK as NIK',
                't_karyawan.V_NAMA_KARYAWAN as NAMA',
                't_karyawan.V_KOTA_GEDUNG as LOKASI',
                'users.created_at as TANGGAL_TERDAFTAR',
                DB::raw('CASE WHEN users.nik IS NOT NULL THEN "Terdaftar" ELSE "Belum Terdaftar" END as STATUS'),
                't_karyawan.V_SHORT_POSISI as POSISI',
                't_karyawan.DPW',
                't_karyawan.DPD'
            ])
            ->where('t_karyawan.V_SHORT_POSISI', 'LIKE', '%GPTP%');

        $this->applyCommonFilters($query, $request, 't_karyawan');
        
        return $query->orderBy('t_karyawan.V_NAMA_KARYAWAN', 'asc');
    }

    private function getPengurusData(Request $request)
    {
        $query = SekarPengurus::query()
            ->join('t_karyawan', 't_sekar_pengurus.N_NIK', '=', 't_karyawan.N_NIK')
            ->join('t_sekar_roles', 't_sekar_pengurus.ID_ROLES', '=', 't_sekar_roles.ID')
            ->select([
                't_sekar_pengurus.N_NIK as NIK',
                't_karyawan.V_NAMA_KARYAWAN as NAMA',
                't_karyawan.V_KOTA_GEDUNG as LOKASI',
                't_sekar_pengurus.CREATED_AT as TANGGAL_TERDAFTAR',
                't_sekar_pengurus.DPW',
                't_sekar_pengurus.DPD',
                't_sekar_roles.NAME as ROLE',
                't_sekar_pengurus.V_SHORT_POSISI as POSISI_SEKAR'
            ]);

        $this->applyCommonFilters($query, $request, 't_sekar_pengurus', 't_karyawan');

        return $query->orderBy('t_karyawan.V_NAMA_KARYAWAN', 'asc');
    }
    
    private function getExAnggotaData(Request $request)
    {
        $query = ExAnggota::query();
        $this->applyCommonFilters($query, $request, 't_ex_anggota');
        return $query->orderBy('TGL_KELUAR', 'desc');
    }

    /**
     * Reusable filter logic for DPW, DPD, and search.
     */
    private function applyCommonFilters(Builder $query, Request $request, string $mainTable, string $secondaryTable = null)
    {
        $user = Auth::user();

        // Admin DPW filter
        if ($user->hasRole('ADMIN_DPW') && ($adminDpw = $user->getDPW())) {
            $query->where($mainTable . '.DPW', $adminDpw);
        }

        // DPW filter from request
        if ($request->filled('dpw') && $request->dpw !== 'Semua DPW') {
            $query->where($mainTable . '.DPW', $request->dpw);
        }

        // DPD filter from request
        if ($request->filled('dpd') && $request->dpd !== 'Semua DPD') {
            $query->where($mainTable . '.DPD', $request->dpd);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $mainTable, $secondaryTable) {
                $q->where($mainTable . '.V_NAMA_KARYAWAN', 'LIKE', "%{$search}%")
                  ->orWhere($mainTable . '.N_NIK', 'LIKE', "%{$search}%");

                if ($secondaryTable) {
                     $q->orWhere($secondaryTable . '.name', 'LIKE', "%{$search}%")
                       ->orWhere($secondaryTable . '.nik', 'LIKE', "%{$search}%");
                }
            });
        }
    }
    
    private function getDpwOptions()
    {
        $query = Karyawan::select('DPW')->whereNotNull('DPW')->where('DPW', '!=', '')->distinct();
        
        $user = Auth::user();
        if ($user->hasRole('ADMIN_DPW') && ($adminDpw = $user->getDPW())) {
            return $query->where('DPW', $adminDpw)->orderBy('DPW')->pluck('DPW');
        }

        return $query->orderBy('DPW')->pluck('DPW')->prepend('Semua DPW');
    }

    private function getDpdOptions(Request $request)
    {
        $query = Karyawan::select('DPD')->whereNotNull('DPD')->where('DPD', '!=', '')->distinct();
        
        $user = Auth::user();
        if ($user->hasRole('ADMIN_DPW') && ($adminDpw = $user->getDPW())) {
            $query->where('DPW', $adminDpw);
        } elseif ($request->filled('dpw') && $request->dpw !== 'Semua DPW') {
            $query->where('DPW', $request->dpw);
        }

        return $query->orderBy('DPD')->pluck('DPD')->prepend('Semua DPD');
    }

    private function exportToCsv($data, $filename, $type)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($data, $type) {
            $file = fopen('php://output', 'w');
            // Add BOM to support UTF-8 in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Set Headers
            $csvHeaders = [];
            switch ($type) {
                case 'anggota':
                    $csvHeaders = ['NIK', 'Nama', 'Lokasi', 'Tanggal Terdaftar', 'Iuran Wajib', 'Iuran Sukarela', 'DPW', 'DPD'];
                    break;
                case 'gptp':
                    $csvHeaders = ['NIK', 'Nama', 'Lokasi', 'Tanggal Terdaftar', 'Status', 'Posisi'];
                    break;
                case 'pengurus':
                    $csvHeaders = ['NIK', 'Nama', 'Lokasi', 'Tanggal Terdaftar', 'DPW', 'DPD', 'Role', 'Posisi SEKAR'];
                    break;
                case 'ex-anggota':
                    $csvHeaders = ['NIK', 'Nama', 'Posisi Terakhir', 'Tanggal Keluar', 'Alasan Keluar', 'DPW', 'DPD'];
                    break;
            }
            fputcsv($file, $csvHeaders);

            // Set Rows
            foreach ($data as $row) {
                 $rowData = (array) $row;
                 fputcsv($file, $rowData);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
    
    private function checkSuperAdminAccess()
    {
        // For better performance, this logic should be on the User model
        // e.g., if (Auth::user()->isSuperAdmin()) { ... }
        if (!Auth::user()->hasRole('ADM')) {
             abort(403, 'Akses ditolak. Hanya Super Admin yang dapat melakukan tindakan ini.');
        }
    }
}