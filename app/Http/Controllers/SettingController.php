<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    /**
     * Menampilkan halaman pengaturan.
     */
    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->route('dashboard')
                           ->with('error', 'Akses ditolak. Fitur ini hanya untuk admin.');
        }

        $settings = $this->getAllSettings();
        
        // Ambil daftar dokumen dari database dan decode
        $documentsJson = Setting::getValue('site_documents', '[]');
        $settings['documents'] = json_decode($documentsJson, true) ?: [];

        return view('setting.index', compact('settings'));
    }

    /**
     * Update pengaturan Tanda Tangan dan Periode.
     */
    public function update(Request $request)
    {
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
            if ($request->hasFile('sekjen_signature')) {
                $this->updateSignature('sekjen_signature', $request->file('sekjen_signature'));
            }

            if ($request->hasFile('waketum_signature')) {
                $this->updateSignature('waketum_signature', $request->file('waketum_signature'));
            }

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
     * Mengunggah dokumen baru.
     */
    public function uploadDocument(Request $request)
    {
        if (!Auth::user()->hasRole('ADM')) {
            return redirect()->route('setting.index')->with('error', 'Hanya Super Admin yang dapat mengunggah dokumen.');
        }

        $request->validate([
            'document_name' => 'required|string|max:100',
            'document_file' => 'required|file|mimes:pdf|max:5120', // Maks 5MB
        ]);

        try {
            $file = $request->file('document_file');
            $documentName = $request->input('document_name');
            
            // ================= PERBAIKAN DI SINI =================
            // Langkah 1: Ambil semua informasi dari file SEBELUM dipindahkan
            $fileSize = $file->getSize();
            $originalFilename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            // =====================================================

            // Membuat nama file yang unik untuk menghindari konflik
            $slug = Str::slug($documentName) . '-' . time();
            $filename = "{$slug}.{$extension}";

            // Simpan file ke direktori public/documents
            $path = public_path('documents');
            if (!file_exists($path)) {
                mkdir($path, 0775, true);
            }
            
            // Langkah 2: Pindahkan file setelah semua info didapatkan
            $file->move($path, $filename);

            // Ambil daftar dokumen yang ada, atau buat array kosong jika belum ada
            $documentsJson = Setting::getValue('site_documents', '[]');
            $documents = json_decode($documentsJson, true) ?: [];

            // Langkah 3: Gunakan informasi yang sudah disimpan di variabel tadi
            $documents[] = [
                'name' => $documentName,
                'filename' => $filename,
                'original_name' => $originalFilename, // Menggunakan variabel
                'size' => number_format($fileSize / 1048576, 2) . ' MB', // Menggunakan variabel
                'uploaded_at' => now()->format('d M Y, H:i'),
            ];

            // Simpan kembali array yang sudah diperbarui sebagai JSON ke database
            Setting::setValue('site_documents', json_encode($documents), Auth::user()->nik);

            return redirect()->route('setting.index')->with('success', 'Dokumen berhasil diunggah.');
        } catch (\Exception $e) {
            Log::error('Gagal mengunggah dokumen: ' . $e->getMessage());
            return redirect()->route('setting.index')->with('error', 'Gagal mengunggah dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus dokumen.
     */
    public function deleteDocument(string $filename)
    {
        if (!Auth::user()->hasRole('ADM')) {
            return redirect()->route('setting.index')->with('error', 'Hanya Super Admin yang dapat menghapus dokumen.');
        }

        try {
            // Hapus file fisik dari server
            $path = public_path('documents/' . $filename);
            if (file_exists($path)) {
                unlink($path);
            }

            // Hapus data dokumen dari database
            $documentsJson = Setting::getValue('site_documents', '[]');
            $documents = json_decode($documentsJson, true) ?: [];
            
            // Filter array untuk menghapus data dokumen yang sesuai
            $updatedDocuments = array_filter($documents, function($doc) use ($filename) {
                return $doc['filename'] !== $filename;
            });

            // Simpan kembali array yang sudah diperbarui
            Setting::setValue('site_documents', json_encode(array_values($updatedDocuments)), Auth::user()->nik);

            return redirect()->route('setting.index')->with('success', 'Dokumen berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus dokumen: ' . $e->getMessage());
            return redirect()->route('setting.index')->with('error', 'Gagal menghapus dokumen.');
        }
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
            'site_documents' => '[]',
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
}