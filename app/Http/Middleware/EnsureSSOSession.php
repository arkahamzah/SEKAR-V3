<?php
// app/Http/Middleware/EnsureSSOSession.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Karyawan;
use App\Services\SSOSessionHelper; // GANTI NAMESPACE
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureSSOSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return $next($request);
        }

        if (!SSOSessionHelper::hasCompleteSessionData()) {
            $this->refreshSessionData($user);
        }

        return $next($request);
    }

    private function refreshSessionData($user): void
    {
        try {
            $karyawan = Karyawan::where('N_NIK', $user->nik)->first();
            
            if ($karyawan) {
                Session::put('USER_DETAIL', [
                    'NIK' => $karyawan->N_NIK,
                    'name' => $karyawan->V_NAMA_KARYAWAN,
                    'position' => $karyawan->V_SHORT_POSISI,
                    'unit' => $karyawan->V_SHORT_UNIT,
                    'divisi' => $karyawan->V_SHORT_DIVISI,
                    'location' => $karyawan->V_KOTA_GEDUNG,
                    'pa' => $karyawan->C_PERSONNEL_AREA,
                    'psa' => $karyawan->C_PERSONNEL_SUB_AREA,
                    'kodeDivisi' => $karyawan->C_KODE_DIVISI,
                    'kodeUnit' => $karyawan->C_KODE_UNIT,
                    'kodePosisi' => $karyawan->C_KODE_POSISI,
                    'bandPosisi' => $karyawan->V_BAND_POSISI,
                    'noTelp' => $karyawan->NO_TELP,
                ]);
                
                Log::info('Session data refreshed for user', [
                    'user_id' => $user->id,
                    'nik' => $user->nik
                ]);
            } else {
                Session::put('USER_DETAIL', [
                    'NIK' => $user->nik,
                    'name' => $user->name,
                    'position' => 'N/A',
                    'unit' => 'N/A',
                    'divisi' => 'N/A',
                    'location' => 'N/A',
                    'pa' => 'N/A',
                    'psa' => 'N/A',
                    'kodeDivisi' => 'N/A',
                    'kodeUnit' => 'N/A',
                    'kodePosisi' => 'N/A',
                    'bandPosisi' => 'N/A',
                    'noTelp' => 'N/A',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to refresh session data', [
                'user_id' => $user->id,
                'nik' => $user->nik,
                'error' => $e->getMessage()
            ]);
        }
    }
}