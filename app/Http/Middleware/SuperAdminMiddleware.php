<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();
        
        // Check if user is super admin (ADM role)
        $isSuperAdmin = DB::table('t_sekar_pengurus as sp')
            ->join('t_sekar_roles as sr', 'sp.ID_ROLES', '=', 'sr.ID')
            ->where('sp.N_NIK', $user->nik)
            ->where('sr.NAME', 'ADM')
            ->exists();

        if (!$isSuperAdmin) {
            // If request is AJAX, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.',
                    'error' => 'Unauthorized'
                ], 403);
            }

            // For regular requests, redirect with error message
            return redirect()->route('data-anggota.index')
                ->with('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }

        return $next($request);
    }
}