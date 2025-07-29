<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    /**
     * Display setting page
     */
    public function index()
    {
        // Check admin access
        if (!$this->isAdmin()) {
            return redirect()->route('dashboard')
                           ->with('error', 'Akses ditolak. Fitur ini hanya untuk admin.');
        }
        
        $settings = $this->getAllSettings();
        
        return view('setting.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        // Check admin access
        if (!$this->isAdmin()) {
            return redirect()->route('dashboard')
                           ->with('error', 'Akses ditolak. Fitur ini hanya untuk admin.');
        }
        
        $validated = $request->validate([
            'sekjen_signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'waketum_signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature_periode_start' => 'required|date',
            'signature_periode_end' => 'required|date|after:signature_periode_start',
        ]);

        try {
            // Handle signature uploads
            if ($request->hasFile('sekjen_signature')) {
                $this->updateSignature('sekjen_signature', $request->file('sekjen_signature'));
            }

            if ($request->hasFile('waketum_signature')) {
                $this->updateSignature('waketum_signature', $request->file('waketum_signature'));
            }

            // Update periode settings
            $this->updateSetting('signature_periode_start', $validated['signature_periode_start']);
            $this->updateSetting('signature_periode_end', $validated['signature_periode_end']);

            return redirect()->route('setting.index')
                           ->with('success', 'Pengaturan berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->route('setting.index')
                           ->with('error', 'Terjadi kesalahan saat menyimpan pengaturan.');
        }
    }

    /**
     * Check if current user is admin
     */
    private function isAdmin(): bool
    {
        $user = Auth::user();
        
        if (!$user || !$user->pengurus || !$user->pengurus->role) {
            return false;
        }
        
        $adminRoles = ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD'];
        return in_array($user->pengurus->role->NAME, $adminRoles);
    }

    /**
     * Get all settings as key-value array
     */
    private function getAllSettings(): array
    {
        $settings = Setting::all()->pluck('SETTING_VALUE', 'SETTING_KEY')->toArray();
        
        // Add default values if not exist
        $defaultSettings = [
            'sekjen_signature' => '',
            'waketum_signature' => '',
            'signature_periode_start' => '',
            'signature_periode_end' => '',
        ];

        return array_merge($defaultSettings, $settings);
    }

    /**
     * Update signature file
     */
    private function updateSignature(string $key, $file): void
    {
        // Delete old signature if exists
        $oldSetting = Setting::where('SETTING_KEY', $key)->first();
        if ($oldSetting && $oldSetting->SETTING_VALUE) {
            Storage::disk('public')->delete('signatures/' . $oldSetting->SETTING_VALUE);
        }

        // Store new signature
        $filename = time() . '_' . $key . '.' . $file->getClientOriginalExtension();
        $file->storeAs('signatures', $filename, 'public');

        $this->updateSetting($key, $filename);
    }

    /**
     * Update or create setting
     */
    private function updateSetting(string $key, string $value): void
    {
        Setting::updateOrCreate(
            ['SETTING_KEY' => $key],
            [
                'SETTING_VALUE' => $value,
                'UPDATED_BY' => Auth::user()->nik,
                'UPDATED_AT' => now()
            ]
        );
    }

    /**
     * Get signature URL
     */
    public static function getSignatureUrl(string $key): ?string
    {
        $setting = Setting::where('SETTING_KEY', $key)->first();
        
        if ($setting && $setting->SETTING_VALUE) {
            return Storage::url('signatures/' . $setting->SETTING_VALUE);
        }

        return null;
    }

    /**
     * Check if current date is within signature period
     */
    public static function isSignaturePeriodActive(): bool
    {
        $startDate = Setting::where('SETTING_KEY', 'signature_periode_start')->value('SETTING_VALUE');
        $endDate = Setting::where('SETTING_KEY', 'signature_periode_end')->value('SETTING_VALUE');

        if (!$startDate || !$endDate) {
            return false;
        }

        $today = now()->format('Y-m-d');
        
        return $today >= $startDate && $today <= $endDate;
    }
}