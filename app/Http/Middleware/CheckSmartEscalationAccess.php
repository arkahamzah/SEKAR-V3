<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Konsultasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;


class CheckSmartEscalationAccess
{
    /**
     * Handle an incoming request to validate escalation permissions
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $konsultasiId = $request->route('id');
        $konsultasi = Konsultasi::find($konsultasiId);

        if (!$user || !$user->pengurus || !$user->pengurus->role) {
            return redirect()->route('konsultasi.index')->with('error', 'Anda tidak memiliki hak akses.');
        }
        
        $userRole = $user->pengurus->role->NAME;
        $userDPW = $user->pengurus->DPW;
        $userDPD = $user->pengurus->DPD;

        if ($userRole === 'ADM' || $userRole === 'ADMIN_DPP') {
            return $next($request);
        }

        if ($konsultasi) {
            // Menggunakan strcasecmp untuk perbandingan case-insensitive
            if ($userRole === 'ADMIN_DPW' && $konsultasi->TUJUAN === 'DPW' && strcasecmp($konsultasi->TUJUAN_SPESIFIK, $userDPW) === 0) {
                return $next($request);
            }
            if ($userRole === 'ADMIN_DPD' && $konsultasi->TUJUAN === 'DPD' && strcasecmp($konsultasi->TUJUAN_SPESIFIK, $userDPD) === 0) {
                return $next($request);
            }
        }
        
        return redirect()->route('konsultasi.show', $konsultasiId)->with('error', 'Anda tidak memiliki otorisasi untuk melakukan eskalasi pada konsultasi ini.');
    }

    /**
     * Validate escalation request against smart rules
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function validateEscalationRequest(Request $request, Closure $next, $user): Response
    {
        $konsultasiId = $request->route('id');
        $escalateTo = $request->input('escalate_to');
        $escalateToSpecific = $request->input('escalate_to_specific');

        // Get konsultasi details
        $konsultasi = DB::table('t_konsultasi')->where('ID', $konsultasiId)->first();
        
        if (!$konsultasi) {
            Log::error('Escalation attempted on non-existent konsultasi', [
                'user_id' => $user->id,
                'konsultasi_id' => $konsultasiId
            ]);
            
            return redirect()->back()
                ->with('error', 'Konsultasi tidak ditemukan.');
        }

        $userRole = $user->pengurus->role->NAME;
        $userDPW = $user->pengurus->DPW ?? null;
        $userDPD = $user->pengurus->DPD ?? null;

        // Validate based on user role and smart escalation rules
        $validation = $this->validateSmartEscalationRules(
            $konsultasi, 
            $userRole, 
            $userDPW, 
            $userDPD, 
            $escalateTo, 
            $escalateToSpecific
        );

        if (!$validation['allowed']) {
            Log::warning('Smart escalation rule violation', [
                'user_id' => $user->id,
                'user_role' => $userRole,
                'user_dpw' => $userDPW,
                'user_dpd' => $userDPD,
                'konsultasi_id' => $konsultasiId,
                'current_target' => $konsultasi->TUJUAN,
                'current_specific' => $konsultasi->TUJUAN_SPESIFIK,
                'requested_target' => $escalateTo,
                'requested_specific' => $escalateToSpecific,
                'violation_reason' => $validation['message']
            ]);
            
            return redirect()->back()
                ->with('error', $validation['message'])
                ->withInput();
        }

        // Log successful validation
        Log::info('Smart escalation validation passed', [
            'user_id' => $user->id,
            'user_role' => $userRole,
            'konsultasi_id' => $konsultasiId,
            'escalate_from' => $konsultasi->TUJUAN . ':' . $konsultasi->TUJUAN_SPESIFIK,
            'escalate_to' => $escalateTo . ':' . $escalateToSpecific
        ]);

        return $next($request);
    }

    /**
     * Validate smart escalation rules based on user role and territory
     *
     * @param  object  $konsultasi
     * @param  string  $userRole
     * @param  string|null  $userDPW
     * @param  string|null  $userDPD
     * @param  string  $escalateTo
     * @param  string|null  $escalateToSpecific
     * @return array
     */
    private function validateSmartEscalationRules($konsultasi, $userRole, $userDPW, $userDPD, $escalateTo, $escalateToSpecific): array
    {
        $currentLevel = $konsultasi->TUJUAN;
        $currentSpecific = $konsultasi->TUJUAN_SPESIFIK;

        switch ($userRole) {
            case 'ADMIN_DPD':
                return $this->validateDPDEscalation($konsultasi, $userDPD, $escalateTo, $escalateToSpecific);
                
            case 'ADMIN_DPW':
                return $this->validateDPWEscalation($konsultasi, $userDPW, $escalateTo, $escalateToSpecific);
                
            case 'ADMIN_DPP':
            case 'ADM':
                return $this->validateDPPEscalation($konsultasi, $escalateTo, $escalateToSpecific);
                
            default:
                return [
                    'allowed' => false,
                    'message' => 'Role tidak dikenal untuk melakukan eskalasi.'
                ];
        }
    }

    /**
     * Validate DPD admin escalation rules
     *
     * @param  object  $konsultasi
     * @param  string|null  $userDPD
     * @param  string  $escalateTo
     * @param  string|null  $escalateToSpecific
     * @return array
     */
    private function validateDPDEscalation($konsultasi, $userDPD, $escalateTo, $escalateToSpecific): array
    {
        // DPD can only escalate from their own DPD
        if ($konsultasi->TUJUAN !== 'DPD' || $konsultasi->TUJUAN_SPESIFIK !== $userDPD) {
            return [
                'allowed' => false,
                'message' => 'DPD hanya dapat mengeskalasi konsultasi yang ditujukan ke DPD sendiri.'
            ];
        }

        // Valid escalation targets for DPD
        $validTargets = ['DPW', 'DPP', 'GENERAL'];
        
        if (!in_array($escalateTo, $validTargets)) {
            return [
                'allowed' => false,
                'message' => 'DPD hanya dapat mengeskalasi ke DPW, DPP, atau SEKAR Pusat.'
            ];
        }

        return ['allowed' => true, 'message' => ''];
    }

    /**
     * Validate DPW admin escalation rules with smart restrictions
     *
     * @param  object  $konsultasi
     * @param  string|null  $userDPW
     * @param  string  $escalateTo
     * @param  string|null  $escalateToSpecific
     * @return array
     */
    private function validateDPWEscalation($konsultasi, $userDPW, $escalateTo, $escalateToSpecific): array
    {
        $currentLevel = $konsultasi->TUJUAN;
        $currentSpecific = $konsultasi->TUJUAN_SPESIFIK;

        // Rule 1: DPW can only escalate konsultasi in their jurisdiction
        if ($currentLevel === 'DPD') {
            // Check if DPD belongs to user's DPW
            $dpdInSameDPW = $this->getDPDsInDPW($userDPW);
            
            if (!in_array($currentSpecific, $dpdInSameDPW)) {
                return [
                    'allowed' => false,
                    'message' => 'DPW hanya dapat mengeskalasi konsultasi DPD yang berada di wilayah DPW sendiri.'
                ];
            }
        } elseif ($currentLevel === 'DPW' && $currentSpecific !== $userDPW) {
            return [
                'allowed' => false,
                'message' => 'DPW hanya dapat mengeskalasi konsultasi yang ditujukan ke DPW sendiri.'
            ];
        }

        // Rule 2: DPW cannot escalate to DPD in other DPW areas
        if ($escalateTo === 'DPD') {
            $dpdInSameDPW = $this->getDPDsInDPW($userDPW);
            
            if (!in_array($escalateToSpecific, $dpdInSameDPW)) {
                return [
                    'allowed' => false,
                    'message' => 'DPW tidak dapat mengeskalasi ke DPD yang berada di wilayah DPW lain. Hanya dapat mengeskalasi ke DPD di wilayah sendiri atau ke DPW lain.'
                ];
            }
        }

        // Rule 3: DPW cannot escalate to same DPW
        if ($escalateTo === 'DPW' && $escalateToSpecific === $userDPW) {
            return [
                'allowed' => false,
                'message' => 'Tidak dapat mengeskalasi ke DPW sendiri.'
            ];
        }

        // Rule 4: Check if escalation target is different from current
        if ($escalateTo === $currentLevel && $escalateToSpecific === $currentSpecific) {
            return [
                'allowed' => false,
                'message' => 'Tidak dapat mengeskalasi ke tujuan yang sama.'
            ];
        }

        return ['allowed' => true, 'message' => ''];
    }

    /**
     * Validate DPP admin escalation rules
     *
     * @param  object  $konsultasi
     * @param  string  $escalateTo
     * @param  string|null  $escalateToSpecific
     * @return array
     */
    private function validateDPPEscalation($konsultasi, $escalateTo, $escalateToSpecific): array
    {
        // DPP can escalate to GENERAL only (unless already at GENERAL)
        if ($konsultasi->TUJUAN === 'GENERAL') {
            return [
                'allowed' => false,
                'message' => 'Konsultasi sudah berada di level tertinggi (SEKAR Pusat).'
            ];
        }

        if ($escalateTo !== 'GENERAL') {
            return [
                'allowed' => false,
                'message' => 'DPP hanya dapat mengeskalasi ke SEKAR Pusat.'
            ];
        }

        return ['allowed' => true, 'message' => ''];
    }

    /**
     * Get DPDs that belong to a specific DPW
     *
     * @param  string|null  $dpw
     * @return array
     */
    private function getDPDsInDPW($dpw): array
    {
        if (!$dpw) return [];

        try {
            return DB::table('t_sekar_pengurus')
                ->where('DPW', $dpw)
                ->whereNotNull('DPD')
                ->where('DPD', '!=', '')
                ->distinct()
                ->pluck('DPD')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get DPDs in DPW', [
                'dpw' => $dpw,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get all DPWs except the specified one
     *
     * @param  string|null  $excludeDPW
     * @return array
     */
    private function getOtherDPWs($excludeDPW): array
    {
        try {
            $query = DB::table('t_sekar_pengurus')
                ->whereNotNull('DPW')
                ->where('DPW', '!=', '')
                ->distinct();

            if ($excludeDPW) {
                $query->where('DPW', '!=', $excludeDPW);
            }

            return $query->pluck('DPW')->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get other DPWs', [
                'exclude_dpw' => $excludeDPW,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Check if DPD belongs to specific DPW
     *
     * @param  string  $dpd
     * @param  string  $dpw
     * @return bool
     */
    private function isDPDInDPW($dpd, $dpw): bool
    {
        if (!$dpd || !$dpw) return false;

        try {
            return DB::table('t_sekar_pengurus')
                ->where('DPW', $dpw)
                ->where('DPD', $dpd)
                ->exists();
        } catch (\Exception $e) {
            Log::error('Failed to check DPD in DPW', [
                'dpd' => $dpd,
                'dpw' => $dpw,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get escalation path validation summary for logging
     *
     * @param  object  $konsultasi
     * @param  string  $escalateTo
     * @param  string|null  $escalateToSpecific
     * @return array
     */
    private function getEscalationPathSummary($konsultasi, $escalateTo, $escalateToSpecific): array
    {
        return [
            'from' => [
                'level' => $konsultasi->TUJUAN,
                'specific' => $konsultasi->TUJUAN_SPESIFIK,
                'display' => $konsultasi->TUJUAN . ($konsultasi->TUJUAN_SPESIFIK ? " ({$konsultasi->TUJUAN_SPESIFIK})" : '')
            ],
            'to' => [
                'level' => $escalateTo,
                'specific' => $escalateToSpecific,
                'display' => $escalateTo . ($escalateToSpecific ? " ({$escalateToSpecific})" : '')
            ]
        ];
    }
}