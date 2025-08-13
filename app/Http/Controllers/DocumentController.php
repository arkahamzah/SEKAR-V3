<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    /**
     * Menampilkan dokumen berdasarkan nama file.
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Contracts\View\View
     */
    public function show(string $filename)
    {
        try {
            // Validasi nama file untuk keamanan, mencegah directory traversal
            if (str_contains($filename, '..') || str_contains($filename, '/')) {
                abort(400, 'Nama file tidak valid.');
            }

            $path = public_path('documents/' . $filename);

            if (!file_exists($path)) {
                abort(404, 'Dokumen tidak ditemukan.');
            }

            // Mengembalikan file langsung ke browser untuk ditampilkan (inline)
            return response()->file($path, ['Content-Disposition' => 'inline; filename="' . $filename . '"']);
        } catch (\Exception $e) {
            Log::error('Gagal menampilkan dokumen: ' . $e->getMessage(), ['filename' => $filename]);
            abort(500, 'Tidak dapat menampilkan dokumen.');
        }
    }

    /**
     * Mengunduh dokumen berdasarkan nama file.
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function download(string $filename)
    {
        try {
            // Validasi nama file untuk keamanan
            if (str_contains($filename, '..') || str_contains($filename, '/')) {
                abort(400, 'Nama file tidak valid.');
            }
            
            $path = public_path('documents/' . $filename);

            if (!file_exists($path)) {
                abort(404, 'Dokumen tidak ditemukan.');
            }

            return response()->download($path);
        } catch (\Exception $e) {
            Log::error('Gagal mengunduh dokumen: ' . $e->getMessage(), ['filename' => $filename]);
            return redirect()->back()->with('error', 'Gagal mengunduh dokumen.');
        }
    }

    /**
     * Menyediakan daftar dokumen dalam format JSON untuk API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listDocuments()
    {
        try {
            // Mengambil data JSON dari tabel settings
            $documentsJson = Setting::getValue('site_documents', '[]');
            $documents = json_decode($documentsJson, true);

            // Memastikan data yang dikembalikan adalah array dan mengurutkan dari yang terbaru
            $documentList = is_array($documents) ? array_reverse($documents) : [];

            return response()->json([
                'success' => true,
                'documents' => $documentList
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil daftar dokumen: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data.'], 500);
        }
    }
}