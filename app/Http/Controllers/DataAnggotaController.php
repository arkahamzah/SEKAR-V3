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

        $activeTab = $request->get('tab', 'anggota');
        $data = ['activeTab'  => $activeTab, 'dpwOptions' => $this->getDpwOptionsFromCache(), 'dpdOptions' => $this->getDpdOptionsFromCache($request)];

        switch ($activeTab) {
            case 'anggota':
                $data['anggota'] = $this->getAnggotaData($request);
                break;
            case 'gptp':
                $data['gptp'] = $this->getGptpData($request);
                break;
            case 'pengurus':
                $data['pengurus'] = $this->getPengurusData($request)->simplePaginate(15)->withQueryString();
                break;
            case 'ex-anggota':
                $data['ex_anggota'] = $this->getExAnggotaData($request)->simplePaginate(15)->withQueryString();
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
        $karyawan = Karyawan::where('N_NIK', $nik)->where('STATUS_ANGGOTA', 'Terdaftar')->first();
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
        $key = '';
        if (!empty($karyawan->C_KODE_UNIT) && strpos($karyawan->C_KODE_UNIT, '-') > 0) {
            $key = $karyawan->C_PERSONNEL_SUB_AREA . '_' . substr($karyawan->C_KODE_UNIT, 0, strpos($karyawan->C_KODE_UNIT, '-')) . '-' . substr($karyawan->C_KODE_UNIT, -3);
        } else {
            $key = $karyawan->C_PERSONNEL_SUB_AREA . '_';
        }
        $mapping = DB::table('mapping_dpd')->where('PSA_Kodlok', $key)->first(['DPW', 'DPD']);
        return ['dpw' => $mapping->DPW ?? null, 'dpd' => $mapping->DPD ?? null];
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

    public function export(Request $request)
    {
        $type = $request->get('type', 'anggota');
        $filename = 'data_' . $type . '_' . now()->format('Ymd');
        $data = collect();

        switch ($type) {
            case 'anggota': $data = $this->getAnggotaData($request)->get(); break;
            case 'gptp': $data = $this->getGptpData($request)->get(); break;
            case 'pengurus': $data = $this->getPengurusData($request)->get(); break;
            case 'ex-anggota': $data = $this->getExAnggotaData($request)->get(); break;
            default: return redirect()->back()->with('error', 'Tipe data tidak valid.');
        }

        return $this->exportToCsv($data, $filename, $type);
    }
    private function getAnggotaData(Request $request)
    {
        $query = Karyawan::query()
            ->where('STATUS_ANGGOTA', 'Terdaftar')
            ->where('V_SHORT_POSISI', 'NOT LIKE', '%GPTP%');
        
        $this->applyBaseKaryawanFilters($query, $request);
        
        $paginatedAnggota = $query->orderBy('V_NAMA_KARYAWAN', 'asc')->simplePaginate(15)->withQueryString();

        return $this->attachDpwAndDpd($paginatedAnggota, $request);
    }

    private function getGptpData(Request $request)
    {
        $query = Karyawan::query()->where('V_SHORT_POSISI', 'LIKE', '%GPTP%');
        $this->applyBaseKaryawanFilters($query, $request);
        $paginatedGptp = $query->orderBy('V_NAMA_KARYAWAN', 'asc')->simplePaginate(15)->withQueryString();
        return $this->attachDpwAndDpd($paginatedGptp, $request);
    }
    
    private function attachDpwAndDpd($paginator, Request $request)
    {
        if ($paginator->isEmpty()) {
            return $paginator;
        }

        $items = $paginator->getCollection();
        $keyMap = [];
        $keysToSearch = $items->mapWithKeys(function ($item) use (&$keyMap) {
            $key = '';
            if (!empty($item->C_KODE_UNIT) && strpos($item->C_KODE_UNIT, '-') > 0) {
                $key = $item->C_PERSONNEL_SUB_AREA . '_' . substr($item->C_KODE_UNIT, 0, strpos($item->C_KODE_UNIT, '-')) . '-' . substr($item->C_KODE_UNIT, -3);
            } else {
                $key = $item->C_PERSONNEL_SUB_AREA . '_';
            }
            $keyMap[$item->N_NIK] = $key;
            return [$item->N_NIK => $key];
        })->unique()->values()->all();

        $mapping = DB::table('mapping_dpd')->whereIn('PSA_Kodlok', $keysToSearch)->get(['PSA_Kodlok', 'DPW', 'DPD'])->keyBy('PSA_Kodlok');

        $items->transform(function ($item) use ($keyMap, $mapping) {
            $key = $keyMap[$item->N_NIK] ?? null;
            $item->DPW = $mapping[$key]->DPW ?? null;
            $item->DPD = $mapping[$key]->DPD ?? null;
            return $item;
        });

        // Terapkan filter DPW/DPD di sini (Application-Side Filter)
        if ($request->filled('dpw') && $request->dpw !== 'Semua DPW') {
            $items = $items->filter(fn($item) => $item->DPW == $request->dpw);
        }
        if ($request->filled('dpd') && $request->dpd !== 'Semua DPD') {
            $items = $items->filter(fn($item) => $item->DPD == $request->dpd);
        }

        $paginator->setCollection($items->values());
        return $paginator;
    }
    
    private function applyBaseKaryawanFilters(Builder $query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('N_NIK', 'LIKE', "%{$search}%")->orWhere('V_NAMA_KARYAWAN', 'LIKE', "%{$search}%"));
        }
    }

    private function getPengurusData(Request $request)
    {
        $query = SekarPengurus::query()
            ->join('v_karyawan_base', 't_sekar_pengurus.N_NIK', '=', 'v_karyawan_base.N_NIK')
            ->join('t_sekar_roles', 't_sekar_pengurus.ID_ROLES', '=', 't_sekar_roles.ID')
            ->select('t_sekar_pengurus.N_NIK', 'v_karyawan_base.V_NAMA_KARYAWAN', 'v_karyawan_base.V_KOTA_GEDUNG', 't_sekar_roles.NAME as ROLE', 'v_karyawan_base.V_SHORT_POSISI');
        return $query->orderBy('v_karyawan_base.V_NAMA_KARYAWAN', 'asc');
    }

    private function getExAnggotaData(Request $request)
    {
        return ExAnggota::query()->orderBy('TGL_KELUAR', 'desc');
    }

    private function getDpwOptionsFromCache()
    {
        return cache()->remember('dpw_options_all', now()->addHour(), function () {
            return DB::table('mapping_dpd')->whereNotNull('DPW')->where('DPW', '!=', '')->distinct()->orderBy('DPW')->pluck('DPW')->prepend('Semua DPW');
        });
    }

    private function getDpdOptionsFromCache(Request $request)
    {
        $dpw = $request->get('dpw', 'all');
        return cache()->remember("dpd_options_{$dpw}", now()->addHour(), function () use ($request) {
            $query = DB::table('mapping_dpd')->whereNotNull('DPD')->where('DPD', '!=', '');
            if ($request->filled('dpw') && $request->dpw !== 'Semua DPW') {
                $query->where('DPW', $request->dpw);
            }
            return $query->distinct()->orderBy('DPD')->pluck('DPD')->prepend('Semua DPD');
        });
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
                 fputcsv($file, (array) $row->toArray());
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
}