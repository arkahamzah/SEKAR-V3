<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    // ... metode index() tidak berubah ...
    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->route('dashboard')
                           ->with('error', 'Akses ditolak. Fitur ini hanya untuk admin.');
        }

        $settings = $this->getAllSettings();

        // Decode PKB document info if it exists
        if (!empty($settings['pkb_document_info'])) {
            $settings['pkb_document_info'] = json_decode($settings['pkb_document_info'], true);
        }
        // Fallback to check physical file if not in DB
        else if (file_exists(public_path('documents/pkb-sekar.pdf'))) {
            $filePath = public_path('documents/pkb-sekar.pdf');
            $settings['pkb_document_info'] = [
                'original_name' => 'pkb-sekar.pdf',
                'size' => number_format(filesize($filePath) / 1048576, 2) . ' MB',
                'last_modified' => date('d M Y, H:i', filemtime($filePath)),
            ];
        }

        return view('setting.index', compact('settings'));
    }


    /**
     * Update settings untuk Tanda Tangan dan Periode.
     */
    public function update(Request $request)
    {
        if (!$this->isAdmin()) {
            return redirect()->route('dashboard')
                           ->with('error', 'Akses ditolak. Fitur ini hanya untuk admin.');
        }

        // Validasi sekarang HANYA untuk tanda tangan dan tanggal
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
                           ->with('success', 'Pengaturan tanda tangan dan periode berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->route('setting.index')
                           ->with('error', 'Terjadi kesalahan saat menyimpan pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * NEW: Metode ini HANYA untuk mengunggah dokumen PKB.
     */
    public function updatePkbOnly(Request $request)
    {
        // Otorisasi: Hanya Super Admin
        if (!Auth::user()->hasRole('ADM')) {
            return redirect()->route('setting.index')->with('error', 'Hanya Super Admin yang dapat mengunggah dokumen PKB.');
        }

        // Validasi: Hanya untuk file PKB dan wajib diisi
        $request->validate([
            'pkb_document' => 'required|file|mimes:pdf|max:5120',
        ]);

        try {
            $this->updatePkbDocument($request->file('pkb_document'));
            return redirect()->route('setting.index')->with('success', 'Dokumen PKB berhasil diunggah.');
        } catch (\Exception $e) {
            return redirect()->route('setting.index')->with('error', 'Gagal mengunggah dokumen PKB: ' . $e->getMessage());
        }
    }


    // ... sisa metode (updatePkbDocument, isAdmin, dll) tidak perlu diubah ...
/**
     * Update PKB document by storing it in the public directory.
     */
    private function updatePkbDocument($file): void
    {
        // Langkah 1: Ambil semua informasi dari file SEBELUM dipindahkan
        $info = [
            'original_name' => $file->getClientOriginalName(),
            'size' => number_format($file->getSize() / 1048576, 2) . ' MB',
            'last_modified' => now()->format('d M Y, H:i'),
        ];

        $filename = 'pkb-sekar.pdf';
        $path = public_path('documents');

        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }

        // Langkah 2: Pindahkan file setelah semua info didapatkan
        $file->move($path, $filename);

        // Langkah 3: Simpan info yang sudah didapatkan ke database
        $this->updateSetting('pkb_document_info', json_encode($info));
    }

    private function isAdmin(): bool
    {
        $user = Auth::user();

        if (!$user || !$user->pengurus || !$user->pengurus->role) {
            return false;
        }

        $adminRoles = ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD'];
        return in_array($user->pengurus->role->NAME, $adminRoles);
    }

    private function getAllSettings(): array
    {
        $settings = Setting::all()->pluck('SETTING_VALUE', 'SETTING_KEY')->toArray();

        $defaultSettings = [
            'sekjen_signature' => '',
            'waketum_signature' => '',
            'signature_periode_start' => '',
            'signature_periode_end' => '',
            'pkb_document_info' => null,
        ];

        return array_merge($defaultSettings, $settings);
    }

    private function updateSignature(string $key, $file): void
    {
        $oldSetting = Setting::where('SETTING_KEY', $key)->first();
        if ($oldSetting && $oldSetting->SETTING_VALUE) {
            Storage::disk('public')->delete('signatures/' . $oldSetting->SETTING_VALUE);
        }

        $filename = time() . '_' . $key . '.' . $file->getClientOriginalExtension();
        $file->storeAs('signatures', $filename, 'public');

        $this->updateSetting($key, $filename);
    }

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

    public static function getSignatureUrl(string $key): ?string
    {
        $setting = Setting::where('SETTING_KEY', $key)->first();

        if ($setting && $setting->SETTING_VALUE) {
            return Storage::url('signatures/' . $setting->SETTING_VALUE);
        }

        return null;
    }

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