<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Jajaran;
use App\Models\SekarJajaran;
use App\Models\SertifikatSignature;
use App\Models\Document;
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
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        // Mengambil semua dokumen, termasuk yang nonaktif (trashed)
        $documents = Document::withTrashed()->orderBy('created_at', 'desc')->get();

        $signatures = SertifikatSignature::orderBy('start_date', 'desc')->get();
        $jajaran = SekarJajaran::where('IS_AKTIF', '1')->with('jajaran')->get();

        return view('setting.index', compact('documents', 'signatures', 'jajaran'));
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
/**
     * Mengunggah dokumen baru. (VERSI FINAL DENGAN PERBAIKAN URUTAN)
     */
    public function uploadDocument(Request $request)
    {
        if (!Auth::user()->hasRole('ADM')) {
            return redirect()->route('setting.index')->with('error', 'Hanya Super Admin yang dapat mengunggah dokumen.');
        }

        try {
            $request->validate([
                'document_name' => 'required|string|max:100',
                'document_file' => 'required|file|mimes:pdf|max:5120',
            ]);

            $file = $request->file('document_file');
            $documentName = $request->input('document_name');
            
            $fileSize = $file->getSize();
            $originalName = $file->getClientOriginalName();
            $slug = Str::slug($documentName) . '-' . time();
            $filename = "{$slug}." . $file->getClientOriginalExtension();

            // 2. BARU pindahkan filenya
            $file->move(public_path('documents'), $filename);

            // 3. Simpan data ke database
            Document::create([
                'name'          => $documentName,
                'filename'      => $filename,
                'original_name' => $originalName,
                'size'          => number_format($fileSize / 1048576, 2) . ' MB',
            ]);
            
    
            return redirect()->route('setting.index')->with('success', 'Dokumen berhasil diunggah.');

        } catch (\Exception $e) {
            // Log error umum untuk debugging
            Log::error('Gagal mengunggah dokumen: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()->route('setting.index')->with('error', 'Terjadi kesalahan internal saat mengunggah dokumen. Silakan hubungi administrator.');
        }
    }

    /**
     * Menghapus dokumen.
     */
   // Ganti nama fungsinya agar lebih sesuai
        public function deactivateDocument(Document $document)
        {
            if (!Auth::user()->hasRole('ADM')) {
                return redirect()->route('setting.index')->with('error', 'Akses ditolak.');
            }

            try {
                // Ini akan melakukan soft delete (mengisi kolom deleted_at)
                $document->delete();
                return redirect()->route('setting.index')->with('success', 'Dokumen berhasil dinonaktifkan.');
            } catch (\Exception $e) {
                Log::error('Gagal menonaktifkan dokumen: ' . $e->getMessage());
                return redirect()->route('setting.index')->with('error', 'Gagal menonaktifkan dokumen.');
            }
        }

     public function restoreDocument($id)
    {
        if (!Auth::user()->hasRole('ADM')) {
            return redirect()->route('setting.index')->with('error', 'Akses ditolak.');
        }

        try {
            // Cari dokumen termasuk yang sudah di-soft-delete
            $document = Document::withTrashed()->findOrFail($id);
            // Kembalikan dokumen (mengosongkan kolom deleted_at)
            $document->restore();
            return redirect()->route('setting.index')->with('success', 'Dokumen berhasil diaktifkan kembali.');
        } catch (\Exception $e) {
            Log::error('Gagal mengaktifkan dokumen: ' . $e->getMessage());
            return redirect()->route('setting.index')->with('error', 'Gagal mengaktifkan dokumen.');
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

    public function updateSignature(Request $request, SertifikatSignature $signature)
    {
        if (!Auth::user()->hasRole('ADM')) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat mengubah data.');
        }

        $validated = $request->validate([
            'nama_pejabat' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'signature_file' => 'nullable|image|mimes:png|max:2048', // File tidak wajib diisi saat update
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            if ($request->hasFile('signature_file')) {
                // Hapus file lama jika ada
                if ($signature->signature_file && Storage::disk('public')->exists('signatures/' . $signature->signature_file)) {
                    Storage::disk('public')->delete('signatures/' . $signature->signature_file);
                }
                
                // Simpan file baru
                $file = $request->file('signature_file');
                $filename = time() . '_' . Str::slug($validated['jabatan']) . '.png';
                $file->storeAs('signatures', $filename, 'public');
                $signature->signature_file = $filename;
            }

            $signature->nama_pejabat = $validated['nama_pejabat'];
            $signature->jabatan = $validated['jabatan'];
            $signature->start_date = $validated['start_date'];
            $signature->end_date = $validated['end_date'];
            $signature->save();

            return redirect()->route('setting.index')->with('success', 'Data tanda tangan berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Gagal memperbarui tanda tangan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui tanda tangan: ' . $e->getMessage())->withInput();
        }
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

      public function storeSignature(Request $request)
    {
        if (!Auth::user()->hasRole('ADM')) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menambahkan data tanda tangan.');
        }

        $validated = $request->validate([
            'jajaran_id' => 'required|exists:t_sekar_jajaran,ID', // Validasi ID pejabat
            'signature_file' => 'required|image|mimes:png|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            // Ambil data pejabat dari database
            $pejabat = SekarJajaran::with('jajaran')->findOrFail($validated['jajaran_id']);
            $namaPejabat = $pejabat->V_NAMA_KARYAWAN;
            $jabatan = $pejabat->jajaran->NAMA_JAJARAN;

            $file = $request->file('signature_file');
            $filename = time() . '_' . Str::slug($jabatan) . '.png';
            $file->storeAs('signatures', $filename, 'public');

            SertifikatSignature::create([
                'nama_pejabat' => $namaPejabat,
                'jabatan' => $jabatan,
                'signature_file' => $filename,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'created_by' => Auth::user()->nik,
            ]);

            return redirect()->route('setting.index')->with('success', 'Tanda tangan baru berhasil ditambahkan ke riwayat.');

        } catch (\Exception $e) {
            Log::error('Gagal menyimpan tanda tangan baru: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan tanda tangan: ' . $e->getMessage())->withInput();
        }
    }

     public function getSignaturesHistory(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $history = SertifikatSignature::orderBy('start_date', 'desc')->paginate(10);
        return response()->json(['success' => true, 'data' => $history]);
    }

    public function destroySignature(SertifikatSignature $signature)
    {
        if (!Auth::user()->hasRole('ADM')) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus data.');
        }

        try {
            // Hapus file dari storage
            if ($signature->signature_file && Storage::disk('public')->exists('signatures/' . $signature->signature_file)) {
                Storage::disk('public')->delete('signatures/' . $signature->signature_file);
            }
            
            // Hapus record dari database
            $signature->delete();

            return redirect()->route('setting.index')->with('success', 'Data tanda tangan berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Gagal menghapus tanda tangan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus tanda tangan: ' . $e->getMessage());
        }
    }

    public function editSignature(SertifikatSignature $signature)
    {
        if (!Auth::user()->hasRole('ADM')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($signature);
    }

    public function searchJajaran(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $request->get('q');
        
        $jajaran = SekarJajaran::where('IS_AKTIF', '1')
            ->where('V_NAMA_KARYAWAN', 'LIKE', "%{$query}%")
            ->with('jajaran')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->ID,
                    'text' => $item->V_NAMA_KARYAWAN,
                    'jabatan' => $item->jajaran->NAMA_JAJARAN ?? 'Tidak diketahui'
                ];
            });

        return response()->json($jajaran);
    }
}