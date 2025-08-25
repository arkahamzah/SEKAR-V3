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
   public function index(Request $request)
    {
        if ($request->get('tab') === 'ex-anggota' && !Auth::user()->hasRole('ADM')) {
            return redirect()->route('data-anggota.index')->with('error', 'Anda tidak memiliki hak akses untuk melihat halaman ini.');
        }
        
        $userScope = $this->getUserScope();

        if ($userScope['dpd']) {
            $request->merge(['dpd' => $userScope['dpd']]);
        }
        if ($userScope['dpw']) {
            $request->merge(['dpw' => $userScope['dpw']]);
        }

        $activeTab = $request->get('tab', 'anggota');

        $dpwOptions = $this->getDpwOptions($userScope);
        $dpdOptions = $this->getDpdOptions($request, $userScope);

        $data = [
            'activeTab'  => $activeTab,
            'dpwOptions' => $dpwOptions,
            'dpdOptions' => $dpdOptions,
            'userScope'  => $userScope
        ];

        if ($activeTab === 'pengurus') {
        $data['roles'] = SekarRole::orderBy('NAME', 'asc')->get();
    }

        switch ($activeTab) {
            case 'anggota':
                $data['anggota'] = $this->getAnggotaData($request, true, $userScope);
                break;
            case 'gptp':
                $data['gptp'] = $this->getGptpData($request, true, $userScope);
                break;
           case 'pengurus':
                $data['pengurus'] = $this->getPengurusData($request, $userScope)->paginate($this->resolvePerPage($request))->withQueryString();
                break;
            case 'ex-anggota':
                $data['ex_anggota'] = $this->getExAnggotaData($request, $userScope)->paginate($this->resolvePerPage($request))->withQueryString();
                break;
        }
        return view('data-anggota.index', $data);
    }

    public function create()
    {
        $this->checkSuperAdminAccess();
        return view('data-anggota.create', [
            'dpwList' => $this->getDpwOptionsFromCache()->reject(fn($dpw) => $dpw === 'Semua DPW'),
            'dpdList' => $this->getDpdOptionsFromCache(new Request())->reject(fn($dpd) => $dpd === 'Semua DPD'),
        ]);
    }

    public function store(Request $request)
    {
        $this->checkSuperAdminAccess();
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|max:30|unique:users,nik',
            'nama' => 'required|string|max:150',
            'dpw' => 'required|string|max:100',
            'dpd' => 'required|string|max:100',
            'iuran_wajib' => 'nullable|numeric|min:0',
            'iuran_sukarela' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            if ($exAnggota = ExAnggota::where('N_NIK', $request->nik)->first()) {
                $exAnggota->delete();
            }
            User::create(['nik' => $request->nik, 'name' => $request->nama, 'password' => Hash::make('password123')]);
            DB::table('t_karyawan')->updateOrInsert(['N_NIK' => $request->nik], ['V_NAMA_KARYAWAN' => $request->nama, 'V_SHORT_POSISI' => 'ANGGOTA SEKAR', 'V_SHORT_UNIT' => 'SEKAR']);
            Iuran::updateOrCreate(['N_NIK' => $request->nik], ['IURAN_WAJIB' => $request->iuran_wajib ?? 25000, 'IURAN_SUKARELA' => $request->iuran_sukarela ?? 0, 'TAHUN' => now()->year, 'STATUS_BAYAR' => 'AKTIF', 'CREATED_BY' => Auth::user()->nik]);

            DB::commit();
            return redirect()->route('data-anggota.index')->with('success', 'Anggota baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menambahkan anggota: ' . $e->getMessage())->withInput();
        }
    }

    public function createPengurus()
    {
        $this->checkSuperAdminAccess();
        return view('data-anggota.create-pengurus', ['roles' => SekarRole::all()]);
    }

    public function storePengurus(Request $request)
    {
        $this->checkSuperAdminAccess();
        $validator = Validator::make($request->all(), [
            'nik'      => 'required|string|max:30|exists:v_karyawan_base,N_NIK|unique:t_sekar_pengurus,N_NIK',
            'id_roles' => 'required|exists:t_sekar_roles,ID',
        ], ['nik.unique' => 'Karyawan yang dipilih sudah terdaftar sebagai pengurus.', 'nik.exists' => 'NIK Karyawan tidak valid atau tidak ditemukan.']);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $karyawan = Karyawan::where('N_NIK', $request->nik)->firstOrFail();
            SekarPengurus::create(['N_NIK' => $karyawan->N_NIK, 'V_SHORT_POSISI' => $karyawan->V_SHORT_POSISI, 'V_SHORT_UNIT' => $karyawan->V_SHORT_UNIT, 'ID_ROLES' => $request->id_roles, 'CREATED_BY' => Auth::user()->nik]);
            return redirect()->route('data-anggota.index', ['tab' => 'pengurus'])->with('success', 'Pengurus baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function getKaryawanInfo($nik)
    {
        $karyawan = Karyawan::where('N_NIK', $nik)->where('STATUS_PENDAFTARAN', 'Terdaftar')->first();
        if (!$karyawan) {
            return response()->json(['status' => 'error', 'message' => 'Anggota tidak ditemukan atau belum terdaftar sebagai user.']);
        }
        if (SekarPengurus::where('N_NIK', $nik)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Karyawan ini sudah terdaftar sebagai pengurus.']);
        }
        return response()->json(['status' => 'success', 'data' => $karyawan]);
    }

    public function edit($nik)
    {
        $this->checkSuperAdminAccess();
        $member = Karyawan::where('N_NIK', $nik)->firstOrFail();

        $dpw_dpd = $this->getSingleDpwDpd($member);
        $member->DPW = $dpw_dpd['dpw'];
        $member->DPD = $dpw_dpd['dpd'];

        return view('data-anggota.edit', [
            'member'  => $member,
            'dpwList' => $this->getDpwOptionsFromCache()->reject(fn($dpw) => $dpw === 'Semua DPW'),
            'dpdList' => $this->getDpdOptionsFromCache(new Request(['dpw' => $member->DPW]))->reject(fn($dpd) => $dpd === 'Semua DPD'),
        ]);
    }

private function getSingleDpwDpd($karyawan)
{
    $key = $this->buildPsaKodlok($karyawan->C_PERSONNEL_SUB_AREA ?? '', $karyawan->C_KODE_UNIT ?? null);

    $mapping = DB::table('mapping_dpd')
        ->where('PSA_Kodlok', $key)
        ->first(['DPW', 'DPD']);

    return [
        'dpw' => $mapping->DPW ?? null,
        'dpd' => $mapping->DPD ?? null,
    ];
}


    public function update(Request $request, $nik)
    {
        $this->checkSuperAdminAccess();
        $validator = Validator::make($request->all(), [
            'nama'           => 'required|string|max:150',
            'dpw'            => 'nullable|string|max:100', // Nullable as it's from mapping
            'dpd'            => 'nullable|string|max:100', // Nullable as it's from mapping
            'iuran_sukarela' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            if ($user = User::where('nik', $nik)->first()) {
                $user->update(['name'  => $request->nama]);
            }
            DB::table('t_karyawan')->where('N_NIK', $nik)->update(['V_NAMA_KARYAWAN' => $request->nama]);
            Iuran::where('N_NIK', $nik)->update(['IURAN_SUKARELA' => $request->iuran_sukarela ?? 0, 'UPDATE_BY' => Auth::user()->nik]);
            DB::commit();
            return redirect()->route('data-anggota.index')->with('success', 'Data anggota berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($nik)
    {
        $this->checkSuperAdminAccess();
        DB::beginTransaction();
        try {
            $user = User::where('nik', $nik)->first();
            $karyawan = Karyawan::where('N_NIK', $nik)->first();

            if (!$user || !$karyawan) {
                return redirect()->route('data-anggota.index')->with('error', 'Data anggota tidak ditemukan.');
            }

            $dpw_dpd = $this->getSingleDpwDpd($karyawan);

            ExAnggota::create(['N_NIK' => $user->nik, 'V_NAMA_KARYAWAN' => $user->name, 'V_SHORT_POSISI' => $karyawan->V_SHORT_POSISI ?? null, 'V_SHORT_DIVISI' => $karyawan->V_SHORT_DIVISI ?? null, 'TGL_KELUAR' => now(), 'DPW' => $dpw_dpd['dpw'] ?? null, 'DPD' => $dpw_dpd['dpd'] ?? null, 'V_KOTA_GEDUNG' => $karyawan->V_KOTA_GEDUNG ?? null, 'CREATED_BY' => Auth::user()->nik, 'IURAN_WAJIB_TERAKHIR' => $karyawan->IURAN_WAJIB ?? 0, 'IURAN_SUKARELA_TERAKHIR' => $karyawan->IURAN_SUKARELA ?? 0]);

            if ($pengurus = SekarPengurus::where('N_NIK', $nik)->first()) $pengurus->delete();
            if ($iuran = Iuran::where('N_NIK', $nik)->first()) $iuran->delete();
            $user->delete();

            DB::commit();
            return redirect()->route('data-anggota.index')->with('success', 'Anggota berhasil dinonaktifkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('data-anggota.index')->with('error', 'Gagal menonaktifkan anggota: ' . $e->getMessage());
        }
    }

    private function resolvePerPage(Request $request): int
    {
        // ### PERUBAHAN DI SINI ###
        $allowed = [10, 25, 50, 100];
        $fallback = 10;
        $size = (int) $request->get('size', $fallback);
        return in_array($size, $allowed, true) ? $size : $fallback;
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'anggota');
        $filename = 'data_' . $type . '_' . now()->format('Ymd');
        $data = collect();

        switch ($type) {
            case 'anggota': $data = $this->getAnggotaData($request, false)->get(); break;
            case 'gptp': $data = $this->getGptpData($request, false)->get(); break;
            case 'pengurus': $data = $this->getPengurusData($request)->get(); break;
            case 'ex-anggota': $data = $this->getExAnggotaData($request)->get(); break;
            default: return redirect()->back()->with('error', 'Tipe data tidak valid.');
        }

        return $this->exportToCsv($data, $filename, $type);
    }

    private function getAnggotaData(Request $request, bool $shouldPaginate = true)
    {
        $query = Karyawan::query()
            ->where(DB::raw("STATUS_PENDAFTARAN"), '=', 'Terdaftar')
            ->where(DB::raw("V_SHORT_POSISI"), 'NOT LIKE', '%GPTP%');

        $this->applyBaseKaryawanFilters($query, $request);

        if ($shouldPaginate) {
            $paginatedAnggota = $query->orderBy('V_NAMA_KARYAWAN', 'asc')->paginate($this->resolvePerPage($request))->withQueryString();
            return $this->attachDpwAndDpd($paginatedAnggota, $request);
        }

        return $this->attachDpwAndDpdToCollection($query->orderBy('V_NAMA_KARYAWAN', 'asc')->get(), $request);
    }

    private function getGptpData(Request $request, bool $shouldPaginate = true)
    {
        $query = Karyawan::query()->where(DB::raw("V_SHORT_POSISI"), 'LIKE', '%GPTP%');
        $this->applyBaseKaryawanFilters($query, $request);

        if ($shouldPaginate) {
            $paginatedGptp = $query->orderBy('V_NAMA_KARYAWAN', 'asc')->paginate($this->resolvePerPage($request))->withQueryString();
            return $this->attachDpwAndDpd($paginatedGptp, $request);
        }

        return $this->attachDpwAndDpdToCollection($query->orderBy('V_NAMA_KARYAWAN', 'asc')->get(), $request);
    }

    private function attachDpwAndDpd($paginator, Request $request)
    {
        if ($paginator->isEmpty()) {
            return $paginator;
        }

        $items = $this->attachDpwAndDpdToCollection($paginator->getCollection(), $request);

        $paginator->setCollection($items);
        return $paginator;
    }
    
    // ## PERUBAHAN 2: Menghapus Logika Filter dari Method Ini
    private function attachDpwAndDpdToCollection($collection, Request $request)
    {
        if ($collection->isEmpty()) {
            return $collection;
        }

$keyMap = [];
$keysToSearch = $collection->mapWithKeys(function ($item) use (&$keyMap) {
    $key = $this->buildPsaKodlok($item->C_PERSONNEL_SUB_AREA ?? '', $item->C_KODE_UNIT ?? null);
    $keyMap[$item->N_NIK] = $key;
    return [$item->N_NIK => $key];
})->unique()->values()->all();


        $mapping = DB::table('mapping_dpd')->whereIn('PSA_Kodlok', $keysToSearch)->get(['PSA_Kodlok', 'DPW', 'DPD'])->keyBy('PSA_Kodlok');

        $collection->transform(function ($item) use ($keyMap, $mapping) {
            $key = $keyMap[$item->N_NIK] ?? null;
            $item->DPW = $mapping[$key]->DPW ?? null;
            $item->DPD = $mapping[$key]->DPD ?? null;
            return $item;
        });

        if ($request->filled('dpw') && $request->dpw !== 'Semua DPW') {
            $collection = $collection->filter(fn($item) => $item->DPW == $request->dpw);
        }
        if ($request->filled('dpd') && $request->dpd !== 'Semua DPD') {
            $collection = $collection->filter(fn($item) => $item->DPD == $request->dpd);
        }

        return $collection->values();
    }

    private function applyBaseKaryawanFilters(Builder $query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('N_NIK', 'LIKE', "%{$search}%")->orWhere('V_NAMA_KARYAWAN', 'LIKE', "%{$search}%"));
        }

        $hasDpwFilter = $request->filled('dpw') && $request->dpw !== 'Semua DPW';
        $hasDpdFilter = $request->filled('dpd') && $request->dpd !== 'Semua DPD';

        if ($hasDpwFilter || $hasDpdFilter) {
            $mappingQuery = DB::table('mapping_dpd');
            if ($hasDpwFilter) {
                $mappingQuery->where('DPW', $request->dpw);
            }
            if ($hasDpdFilter) {
                $mappingQuery->where('DPD', $request->dpd);
            }
            $matchingKeys = $mappingQuery->pluck('PSA_Kodlok');

            if ($matchingKeys->isEmpty()) {
                $query->whereRaw('1 = 0');
                return;
            }

$keyExpression = DB::raw("
    CASE
        WHEN C_KODE_UNIT IS NOT NULL AND LOCATE('-', C_KODE_UNIT) > 0 THEN
            CONCAT(
                C_PERSONNEL_SUB_AREA, '_',
                SUBSTRING_INDEX(C_KODE_UNIT, '-', 1), '-',
                CASE
                    -- 1) 7 char sesudah '-' ⇒ ambil 3 terakhir
                    WHEN LENGTH(SUBSTRING_INDEX(C_KODE_UNIT, '-', -1)) = 7
                        THEN RIGHT(SUBSTRING_INDEX(C_KODE_UNIT, '-', -1), 3)

                    -- 2) Prefix ITTP & 6 char & 2 char terakhir huruf ⇒ ambil 1 terakhir
                    WHEN UPPER(SUBSTRING_INDEX(C_KODE_UNIT, '-', 1)) = 'ITTP'
                         AND LENGTH(SUBSTRING_INDEX(C_KODE_UNIT, '-', -1)) = 6
                         AND SUBSTRING(SUBSTRING_INDEX(C_KODE_UNIT, '-', -1), -2, 1) REGEXP '[A-Za-z]'
                         AND SUBSTRING(SUBSTRING_INDEX(C_KODE_UNIT, '-', -1), -1, 1) REGEXP '[A-Za-z]'
                        THEN RIGHT(SUBSTRING_INDEX(C_KODE_UNIT, '-', -1), 1)

                    -- 3) Umum: 6 char sesudah '-' ⇒ ambil 2 terakhir
                    WHEN LENGTH(SUBSTRING_INDEX(C_KODE_UNIT, '-', -1)) = 6
                        THEN RIGHT(SUBSTRING_INDEX(C_KODE_UNIT, '-', -1), 2)

                    -- 4) Fallback ⇒ ambil 3 terakhir
                    ELSE RIGHT(SUBSTRING_INDEX(C_KODE_UNIT, '-', -1), 3)
                END
            )
        ELSE
            CONCAT(C_PERSONNEL_SUB_AREA, '_')
    END
");

            $query->whereIn($keyExpression, $matchingKeys);
        }
    }

    private function getPengurusData(Request $request, array $userScope)
    {
        $query = SekarPengurus::query()
            ->join('v_karyawan_base', 't_sekar_pengurus.N_NIK', '=', 'v_karyawan_base.N_NIK')
            ->join('t_sekar_roles', 't_sekar_pengurus.ID_ROLES', '=', 't_sekar_roles.ID')
            ->select(
                't_sekar_pengurus.N_NIK',
                't_sekar_pengurus.DPW',
                't_sekar_pengurus.DPD',
                'v_karyawan_base.V_NAMA_KARYAWAN',
                'v_karyawan_base.V_KOTA_GEDUNG',
                't_sekar_roles.NAME as ROLE',
                't_sekar_pengurus.V_SHORT_POSISI'
            );

        // Terapkan scope (dari perubahan sebelumnya)
        if ($userScope['dpw'] === 'INVALID_SCOPE') {
            return $query->whereRaw('1 = 0');
        }
        if ($userScope['dpd']) {
            $query->where('t_sekar_pengurus.DPD', $userScope['dpd']);
        } elseif ($userScope['dpw']) {
            $query->where('t_sekar_pengurus.DPW', $userScope['dpw']);
        }


        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('t_sekar_pengurus.N_NIK', 'LIKE', "%{$search}%")->orWhere('v_karyawan_base.V_NAMA_KARYAWAN', 'LIKE', "%{$search}%"));
        }
        if ($request->filled('dpw') && $request->dpw !== 'Semua DPW' && !$userScope['dpw']) {
            $query->where('t_sekar_pengurus.DPW', $request->dpw);
        }
        if ($request->filled('dpd') && $request->dpd !== 'Semua DPD' && !$userScope['dpd']) {
            $query->where('t_sekar_pengurus.DPD', $request->dpd);
        }

        if ($request->filled('role')) {
            $query->where('t_sekar_pengurus.ID_ROLES', $request->role);
        }

        return $query->orderBy('v_karyawan_base.V_NAMA_KARYAWAN', 'asc');
    }

    private function getExAnggotaData(Request $request)
    {
        $query = ExAnggota::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('N_NIK', 'LIKE', "%{$search}%")->orWhere('V_NAMA_KARYAWAN', 'LIKE', "%{$search}%"));
        }
        if ($request->filled('dpw') && $request->dpw !== 'Semua DPW') {
            $query->where('DPW', $request->dpw);
        }
        if ($request->filled('dpd') && $request->dpd !== 'Semua DPD') {
            $query->where('DPD', $request->dpd);
        }

        return $query->orderBy('TGL_KELUAR', 'desc');
    }

    private function getDpwOptions(array $userScope)
    {
        if (!empty($userScope['dpw']) && $userScope['dpw'] !== 'INVALID_SCOPE') {

            return collect(['Semua DPW', $userScope['dpw']]);
        }
        
        return DB::table('mapping_dpd')->whereNotNull('DPW')->where('DPW', '!=', '')
            ->distinct()->orderBy('DPW')->pluck('DPW')->prepend('Semua DPW');
    }

    private function getDpdOptions(Request $request, array $userScope)
    {
        if (!empty($userScope['dpd']) && $userScope['dpd'] !== 'INVALID_SCOPE') {
            return collect(['Semua DPD', $userScope['dpd']]);
        }

        $query = DB::table('mapping_dpd')->whereNotNull('DPD')->where('DPD', '!=', '');
        
        $selectedDpw = $userScope['dpw'] ?? $request->get('dpw');
        
        if ($selectedDpw && $selectedDpw !== 'Semua DPW') {
            $query->where('DPW', $selectedDpw);
        }
        
        return $query->distinct()->orderBy('DPD')->pluck('DPD')->prepend('Semua DPD');
    }

    private function exportToCsv($data, $filename, $type)
    {
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',];
        $callback = function() use ($data, $type) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            $csvHeaders = [];
            switch ($type) {
                case 'anggota': $csvHeaders = ['NIK', 'Nama', 'Lokasi', 'Tanggal Terdaftar', 'Iuran Wajib', 'Iuran Sukarela', 'DPW', 'DPD']; break;
                case 'gptp': $csvHeaders = ['NIK', 'Nama', 'Lokasi', 'Status', 'Posisi', 'DPW', 'DPD']; break;
                case 'pengurus': $csvHeaders = ['NIK', 'Nama', 'Lokasi', 'Role', 'Posisi SEKAR', 'DPW', 'DPD']; break;
                case 'ex-anggota': $csvHeaders = ['NIK', 'Nama', 'Posisi Terakhir', 'Tanggal Keluar', 'DPW', 'DPD']; break;
            }
            fputcsv($file, $csvHeaders);

            foreach ($data as $row) {
                 $rowData = [];
                 switch ($type) {
                     case 'anggota':
                         $rowData = [
                             $row->N_NIK, $row->V_NAMA_KARYAWAN, $row->V_KOTA_GEDUNG,
                             $row->TGL_TERDAFTAR ? \Carbon\Carbon::parse($row->TGL_TERDAFTAR)->format('d-m-Y') : '-',
                             $row->IURAN_WAJIB, $row->IURAN_SUKARELA, $row->DPW, $row->DPD,
                         ];
                         break;
                     case 'gptp':
                         $rowData = [
                             $row->N_NIK, $row->V_NAMA_KARYAWAN, $row->V_KOTA_GEDUNG,
                             $row->STATUS_PENDAFTARAN, $row->V_SHORT_POSISI, $row->DPW, $row->DPD,
                         ];
                         break;
                     case 'pengurus':
                         $rowData = [
                             $row->N_NIK, $row->V_NAMA_KARYAWAN, $row->V_KOTA_GEDUNG,
                             $row->ROLE, $row->V_SHORT_POSISI, $row->DPW, $row->DPD,
                         ];
                         break;
                     case 'ex-anggota':
                         $rowData = [
                             $row->N_NIK, $row->V_NAMA_KARYAWAN, $row->V_SHORT_POSISI,
                             $row->TGL_KELUAR ? \Carbon\Carbon::parse($row->TGL_KELUAR)->format('d-m-Y') : '-',
                             $row->DPW, $row->DPD,
                         ];
                         break;
                 }
                 fputcsv($file, $rowData);
            }
            fclose($file);
        };
        return Response::stream($callback, 200, $headers);
    }

    private function checkSuperAdminAccess()
    {
        if (!Auth::user()->hasRole('ADM')) {
             abort(403, 'Akses ditolak.');
        }
    }

    private function getUserScope(): array
    {
        $user = Auth::user();

        if ($user->hasRole('ADM')) {
            return ['dpw' => null, 'dpd' => null];
        }
        $pengurusInfo = SekarPengurus::where('N_NIK', $user->nik)->select('DPW', 'DPD')->first();

        if (!$pengurusInfo) {
            return ['dpw' => 'INVALID_SCOPE', 'dpd' => 'INVALID_SCOPE'];
        }
        if ($user->hasRole('ADMIN_DPW')) {
            return ['dpw' => $pengurusInfo->DPW, 'dpd' => null];
        }

        if ($user->hasRole('ADMIN_DPD')) {
            return ['dpw' => $pengurusInfo->DPW, 'dpd' => $pengurusInfo->DPD];
        }

        return ['dpw' => 'INVALID_SCOPE', 'dpd' => 'INVALID_SCOPE'];
    }

    /**
 * Bangun key PSA_Kodlok dari PSA dan C_KODE_UNIT sesuai logic view.
 * Format: "<PSA>_<PREFIX>-<SUFFIX>" atau "<PSA>_" bila C_KODE_UNIT tidak valid.
 */
private function buildPsaKodlok(?string $psa, ?string $kodeUnit): string
{
    if (empty($psa)) {
        return '';
    }

    if (!empty($kodeUnit) && strpos($kodeUnit, '-') !== false) {
        $prefix = strstr($kodeUnit, '-', true);                    // bagian sebelum '-'
        $after  = substr($kodeUnit, strpos($kodeUnit, '-') + 1);   // bagian sesudah '-'

        if (strlen($after) === 7) {
            // 1) 7 char sesudah '-' ⇒ ambil 3 terakhir
            $suffix = substr($after, -3);
        } elseif (strcasecmp($prefix, 'ITTP') === 0
            && strlen($after) === 6
            && ctype_alpha(substr($after, -2, 1))                  // 2 char terakhir huruf
            && ctype_alpha(substr($after, -1, 1))) {
            // 2) Prefix ITTP & 6 char & 2 huruf di akhir ⇒ ambil 1 terakhir
            $suffix = substr($after, -1);
        } elseif (strlen($after) === 6) {
            // 3) Umum: 6 char sesudah '-' ⇒ ambil 2 terakhir
            $suffix = substr($after, -2);
        } else {
            // 4) Fallback ⇒ ambil 3 terakhir
            $suffix = substr($after, -3);
        }

        return "{$psa}_{$prefix}-{$suffix}";
    }

    return "{$psa}_";
}

}