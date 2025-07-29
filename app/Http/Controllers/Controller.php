<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
    /**
     * Handle common validation errors
     */
    protected function handleValidationError(\Exception $e, string $redirectRoute = null)
    {
        Log::error('Validation error: ' . $e->getMessage());
        
        $redirect = $redirectRoute ? redirect()->route($redirectRoute) : back();
        
        return $redirect->withInput()
                       ->with('error', 'Data yang dimasukkan tidak valid. Silakan periksa kembali.');
    }
    
    /**
     * Handle database errors
     */
    protected function handleDatabaseError(\Exception $e, string $context = 'operasi')
    {
        Log::error("Database error in {$context}: " . $e->getMessage());
        
        return back()->with('error', "Terjadi kesalahan saat {$context}. Silakan coba lagi.");
    }
    
    /**
     * Handle general exceptions
     */
    protected function handleGeneralError(\Exception $e, string $context = 'memproses permintaan')
    {
        Log::error("General error in {$context}: " . $e->getMessage());
        
        return back()->with('error', "Terjadi kesalahan saat {$context}. Silakan coba lagi.");
    }
    
    /**
     * Success response helper
     */
    protected function successResponse(string $message, string $route = null)
    {
        $redirect = $route ? redirect()->route($route) : back();
        
        return $redirect->with('success', $message);
    }
    
    /**
     * Info response helper
     */
    protected function infoResponse(string $message, string $route = null)
    {
        $redirect = $route ? redirect()->route($route) : back();
        
        return $redirect->with('info', $message);
    }
    
    /**
     * Warning response helper
     */
    protected function warningResponse(string $message, string $route = null)
    {
        $redirect = $route ? redirect()->route($route) : back();
        
        return $redirect->with('warning', $message);
    }
    
    /**
     * Get authenticated user or throw exception
     */
    protected function getAuthenticatedUser()
    {
        $user = auth()->user();
        
        if (!$user) {
            throw new \Exception('User not authenticated');
        }
        
        return $user;
    }
    
    /**
     * Format number for display
     */
    protected function formatNumber($number, int $decimals = 0): string
    {
        return number_format($number, $decimals, ',', '.');
    }
    
    /**
     * Clean and sanitize input
     */
    protected function sanitizeInput(string $input): string
    {
        return trim(strip_tags($input));
    }
    
    /**
     * Validate NIK format
     */
    protected function isValidNik(string $nik): bool
    {
        // Basic NIK validation - adjust according to your NIK format rules
        return preg_match('/^[0-9]{6,}$/', $nik);
    }
    
    /**
     * Generate dummy email from NIK
     */
    protected function generateDummyEmail(string $nik): string
    {
        return $nik . '@sekar.local';
    }
    
    /**
     * Check if user has specific role
     */
    protected function userHasRole(string $role): bool
    {
        $user = auth()->user();
        
        if (!$user || !$user->pengurus) {
            return false;
        }
        
        return $user->pengurus->role && $user->pengurus->role->NAME === $role;
    }
    
    /**
     * Get current academic year
     */
    protected function getCurrentYear(): string
    {
        return date('Y');
    }
    
    /**
     * Convert string to title case for Indonesian names
     */
    protected function toTitleCase(string $text): string
    {
        $words = explode(' ', strtolower($text));
        $titleCase = array_map('ucfirst', $words);
        
        return implode(' ', $titleCase);
    }
}