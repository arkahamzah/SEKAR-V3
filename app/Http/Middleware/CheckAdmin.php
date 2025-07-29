<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Check if user is authenticated
        if (!$user) {
            Log::warning('Unauthenticated user attempted to access admin route', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ]);
            
            return redirect()->route('login')
                           ->with('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        }
        
        // Check if user has pengurus relationship
        if (!$user->pengurus) {
            Log::warning('Non-pengurus user attempted to access admin route', [
                'user_id' => $user->id,
                'user_nik' => $user->nik,
                'user_name' => $user->name,
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            return redirect()->route('dashboard')
                           ->with('error', 'Akses ditolak. Anda tidak terdaftar sebagai pengurus SEKAR.');
        }
        
        // Check if pengurus has role
        if (!$user->pengurus->role) {
            Log::warning('Pengurus without role attempted to access admin route', [
                'user_id' => $user->id,
                'user_nik' => $user->nik,
                'user_name' => $user->name,
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            return redirect()->route('dashboard')
                           ->with('error', 'Akses ditolak. Role pengurus Anda belum ditetapkan. Silakan hubungi administrator.');
        }
        
        // Check if user has admin role
        $adminRoles = ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD'];
        $userRole = $user->pengurus->role->NAME;
        
        if (!in_array($userRole, $adminRoles)) {
            Log::warning('Non-admin user attempted to access admin route', [
                'user_id' => $user->id,
                'user_nik' => $user->nik,
                'user_name' => $user->name,
                'user_role' => $userRole,
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            $errorMessage = $this->getErrorMessageForRole($userRole);
            
            return redirect()->route('dashboard')
                           ->with('error', $errorMessage);
        }
        
        // Log successful admin access
        Log::info('Admin user accessed admin route', [
            'user_id' => $user->id,
            'user_nik' => $user->nik,
            'user_name' => $user->name,
            'user_role' => $userRole,
            'url' => $request->fullUrl()
        ]);
        
        return $next($request);
    }
    
    /**
     * Get specific error message based on user role
     */
    private function getErrorMessageForRole(?string $role): string
    {
        if (!$role) {
            return 'Akses ditolak. Anda belum memiliki role yang ditetapkan.';
        }
        
        return match($role) {
            'ANGGOTA' => 'Akses ditolak. Fitur ini hanya tersedia untuk pengurus SEKAR.',
            'PENGURUS' => 'Akses ditolak. Anda memerlukan role admin untuk mengakses fitur ini.',
            default => 'Akses ditolak. Role Anda (' . $role . ') tidak memiliki hak akses admin.'
        };
    }
    
    /**
     * Static method to check if current user is admin
     */
    public static function isAdmin(): bool
    {
        $user = Auth::user();
        
        if (!$user || !$user->pengurus || !$user->pengurus->role) {
            return false;
        }
        
        $adminRoles = ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD'];
        return in_array($user->pengurus->role->NAME, $adminRoles);
    }
    
    /**
     * Get current user's admin level
     */
    public static function getUserLevel(): int
    {
        $user = Auth::user();
        
        if (!$user || !$user->pengurus || !$user->pengurus->role) {
            return 0;
        }
        
        return match($user->pengurus->role->NAME) {
            'ADM' => 4,
            'ADMIN_DPP' => 3,
            'ADMIN_DPW' => 2,
            'ADMIN_DPD' => 1,
            default => 0
        };
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole(string $role): bool
    {
        $user = Auth::user();
        
        if (!$user || !$user->pengurus || !$user->pengurus->role) {
            return false;
        }
        
        return $user->pengurus->role->NAME === $role;
    }
}