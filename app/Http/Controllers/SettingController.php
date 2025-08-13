<?php

namespace App\Http\Controllers;

use App\Models\Setting;
// Tambahkan model Jajaran dan SekarJajaran
use App\Models\Jajaran;
use App\Models\SekarJajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
        
        $documentsJson = Setting::getValue('site_documents', '[]');
        $settings['documents'] = json_decode($documentsJson, true) ?: [];

        // BARU: Mengambil data jajaran untuk ditampilkan di view
        $jajaran = DB::table('t_sekar_jajaran as tj')
            ->join('m_jajaran as mj', 'tj.ID_JAJARAN', '=', 'mj.ID')
            ->select('tj.V_NAMA_KARYAWAN as nama', 'mj.NAMA_JAJARAN as posisi')
            ->where('tj.IS_AKTIF', '1')
            ->get()
            ->map(function ($item) use ($settings) {
                // Mencocokkan jajaran dengan file tanda tangan dari settings
                if (str_contains(strtoupper($item->posisi), 'KETUA UMUM')) {
                    $item->signature_file = $settings['ketum_signature'] ?? null;
                } elseif (str_contains(strtoupper($item->posisi), 'SEKRETARIS JENDRAL')) {
                    $item->signature_file = $settings['sekjen_signature'] ?? null;
                } else {
                    $item->signature_file = null;
                }
                return $item;
            });

        return view('setting.index', compact('settings', 'jajaran'));
    }

    /**
     * Update pengaturan Tanda Tangan, Nama Pejabat, dan Periode.
     */
    public function update(Request $request)
    {
        if (!$this->isAdmin()) {
            return redirect()->route('dashboard')
                           ->with('error', 'Akses ditolak. Fitur ini hanya untuk admin.');
        }

        $validated = $request->validate([
            'ketum_signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'sekjen_signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'ketum_name' => 'required|string|max:100',
            'sekjen_name' => 'required|string|max:100',
            'signature_periode_start' => 'required|date',
            'signature_periode_end' => 'required|date|after:signature_periode_start',
        ]);

        try {
            if ($request->hasFile('ketum_signature')) {
                $this->updateSignature('ketum_signature', $request->file('ketum_signature'));
            }

            if ($request->hasFile('sekjen_signature')) {
                $this->updateSignature('sekjen_signature', $request->file('sekjen_signature'));
            }

            $this->updateSetting('ketum_name', $validated['ketum_name']);
            $this->updateSetting('sekjen_name', $validated['sekjen_name']);
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
            'document_file' => 'required|file|mimes:pdf|max:5120',
        ]);

        try {
            $file = $request->file('document_file');
            $documentName = $request->input('document_name');
            
            $fileSize = $file->getSize();
            $originalFilename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            
            $slug = Str::slug($documentName) . '-' . time();
            $filename = "{$slug}.{$extension}";

            $path = public_path('documents');
            if (!file_exists($path)) {
                mkdir($path, 0775, true);
            }
            $file->move($path, $filename);

            $documentsJson = Setting::getValue('site_documents', '[]');
            $documents = json_decode($documentsJson, true) ?: [];

            $documents[] = [
                'name' => $documentName,
                'filename' => $filename,
                'original_name' => $originalFilename,
                'size' => number_format($fileSize / 1048576, 2) . ' MB',
                'uploaded_at' => now()->format('d M Y, H:i'),
            ];

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
            $path = public_path('documents/' . $filename);
            if (file_exists($path)) {
                unlink($path);
            }

            $documentsJson = Setting::getValue('site_documents', '[]');
            $documents = json_decode($documentsJson, true) ?: [];
            
            $updatedDocuments = array_filter($documents, function($doc) use ($filename) {
                return $doc['filename'] !== $filename;
            });

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
            'ketum_signature' => '',
            'sekjen_signature' => '',
            'ketum_name' => 'ASEP MULYANA',
            'sekjen_name' => 'ABDUL KARIM',
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