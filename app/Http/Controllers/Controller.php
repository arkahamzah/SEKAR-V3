<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
    protected function handleValidationError(\Exception $e, string $redirectRoute = null)
    {
        Log::error('Validation error: ' . $e->getMessage());
        
        $redirect = $redirectRoute ? redirect()->route($redirectRoute) : back();
        
        return $redirect->withInput()
                       ->with('error', 'Data yang dimasukkan tidak valid. Silakan periksa kembali.');
    }

    protected function handleDatabaseError(\Exception $e, string $context = 'operasi')
    {
        Log::error("Database error in {$context}: " . $e->getMessage());
        
        return back()->with('error', "Terjadi kesalahan saat {$context}. Silakan coba lagi.");
    }
    
    protected function handleGeneralError(\Exception $e, string $context = 'memproses permintaan')
    {
        Log::error("General error in {$context}: " . $e->getMessage());
        
        return back()->with('error', "Terjadi kesalahan saat {$context}. Silakan coba lagi.");
    }
    

    protected function successResponse(string $message, string $route = null)
    {
        $redirect = $route ? redirect()->route($route) : back();
        
        return $redirect->with('success', $message);
    }
    

    protected function infoResponse(string $message, string $route = null)
    {
        $redirect = $route ? redirect()->route($route) : back();
        
        return $redirect->with('info', $message);
    }
    

    protected function warningResponse(string $message, string $route = null)
    {
        $redirect = $route ? redirect()->route($route) : back();
        
        return $redirect->with('warning', $message);
    }
    
    protected function getAuthenticatedUser()
    {
        $user = auth()->user();
        
        if (!$user) {
            throw new \Exception('User not authenticated');
        }
        
        return $user;
    }
    
    protected function formatNumber($number, int $decimals = 0): string
    {
        return number_format($number, $decimals, ',', '.');
    }
    
    protected function sanitizeInput(string $input): string
    {
        return trim(strip_tags($input));
    }
    
    protected function isValidNik(string $nik): bool
    {
        return preg_match('/^[0-9]{6,}$/', $nik);
    }
    
    protected function generateDummyEmail(string $nik): string
    {
        return $nik . '@sekar.local';
    }
    
    protected function userHasRole(string $role): bool
    {
        $user = auth()->user();
        
        if (!$user || !$user->pengurus) {
            return false;
        }
        
        return $user->pengurus->role && $user->pengurus->role->NAME === $role;
    }
    
    protected function getCurrentYear(): string
    {
        return date('Y');
    }
    
    protected function toTitleCase(string $text): string
    {
        $words = explode(' ', strtolower($text));
        $titleCase = array_map('ucfirst', $words);
        
        return implode(' ', $titleCase);
    }
}