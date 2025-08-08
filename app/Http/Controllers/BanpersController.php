<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\Params;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class BanpersController extends Controller
{
    public function index()
    {
        $banpersData = $this->getBanpersData();
        $isSuperAdmin = $this->isSuperAdmin();
        
        return view('banpers.index', array_merge($banpersData, [
            'isSuperAdmin' => $isSuperAdmin
        ]));
    }

    /**
     * Show form for editing banpers nominal (Super Admin only)
     */
    public function edit()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->route('banpers.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengubah nominal banpers');
        }

        $params = Params::where('IS_AKTIF', '1')
                       ->where('TAHUN', date('Y'))
                       ->first();
                       
        $currentNominal = $params ? (int)$params->NOMINAL_BANPERS : 20000;
        
        return view('banpers.edit', [
            'currentNominal' => $currentNominal,
            'tahun' => date('Y')
        ]);
    }

    /**
     * Update banpers nominal (Super Admin only)
     */
    public function update(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->route('banpers.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengubah nominal banpers');
        }

        $validator = Validator::make($request->all(), [
            'nominal_banpers' => 'required|integer|min:0|max:999999999',
            'keterangan' => 'nullable|string|max:255'
        ], [
            'nominal_banpers.required' => 'Nominal banpers wajib diisi',
            'nominal_banpers.integer' => 'Nominal banpers harus berupa angka',
            'nominal_banpers.min' => 'Nominal banpers minimal 0',
            'nominal_banpers.max' => 'Nominal banpers terlalu besar',
            'keterangan.max' => 'Keterangan maksimal 255 karakter'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $tahun = date('Y');
            $currentUser = Auth::user();
            
            // Get current params
            $params = Params::where('IS_AKTIF', '1')
                           ->where('TAHUN', $tahun)
                           ->first();
            
            $oldNominal = $params ? $params->NOMINAL_BANPERS : '0';
            $newNominal = $request->nominal_banpers;

            if ($params) {
                // Update existing params
                $params->update([
                    'NOMINAL_BANPERS' => $newNominal,
                    'UPDATED_BY' => $currentUser->nik,
                    'UPDATED_AT' => now()
                ]);
            } else {
                // Create new params for current year
                Params::create([
                    'NOMINAL_IURAN_WAJIB' => '25000', // Default from sprint notes
                    'NOMINAL_BANPERS' => $newNominal,
                    'TAHUN' => $tahun,
                    'IS_AKTIF' => '1',
                    'CREATED_BY' => $currentUser->nik,
                    'CREATED_AT' => now()
                ]);
            }

            // Log the change in banpers history if the table exists
            $this->logBanpersChange($oldNominal, $newNominal, $tahun, $request->keterangan ?? 'Perubahan nominal banpers oleh admin');

            DB::commit();

            return redirect()->route('banpers.index')
                ->with('success', 'Nominal banpers berhasil diupdate dari Rp ' . number_format($oldNominal, 0, ',', '.') . ' menjadi Rp ' . number_format($newNominal, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Gagal mengupdate nominal banpers: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Log banpers nominal changes
     */
    private function logBanpersChange($oldNominal, $newNominal, $tahun, $keterangan = null)
    {
        try {
            // Check if banpers history table exists
            if (DB::getSchemaBuilder()->hasTable('t_banpers_history')) {
                DB::table('t_banpers_history')->insert([
                    'N_NIK' => 'SYSTEM', // System-wide change
                    'NOMINAL_LAMA' => $oldNominal,
                    'NOMINAL_BARU' => $newNominal,
                    'TAHUN' => $tahun,
                    'STATUS_PROSES' => 'IMPLEMENTED',
                    'TGL_PERUBAHAN' => now(),
                    'TGL_PROSES' => now(),
                    'TGL_IMPLEMENTASI' => now(),
                    'KETERANGAN' => $keterangan ?? 'Perubahan nominal banpers sistem',
                    'CREATED_BY' => Auth::user()->nik,
                    'CREATED_AT' => now()
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            \Log::error('Failed to log banpers change: ' . $e->getMessage());
        }
    }

    /**
     * Check if current user is super admin
     */
    private function isSuperAdmin(): bool
    {
        $user = Auth::user();
        
        if (!$user || !$user->pengurus || !$user->pengurus->role) {
            return false;
        }
        
        // Check if user is super admin (ADM role)
        return in_array($user->pengurus->role->NAME, ['ADM', 'SUPER_ADMIN']);
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