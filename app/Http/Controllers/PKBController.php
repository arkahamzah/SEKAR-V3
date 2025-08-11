<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PkbController extends Controller
{
    /**
     * Display the PKB SEKAR document in browser
     */
    public function show(Request $request)
    {
        try {
            // Path to the PKB PDF file - you can store it in storage/app/public/documents/
            // or in public/documents/ folder
            $pdfPath = public_path('documents/pkb-sekar.pdf');

            // Alternative: if stored in storage/app/public/documents/
            // $pdfPath = storage_path('app/public/documents/pkb-sekar.pdf');

            // Check if file exists
            if (!file_exists($pdfPath)) {
                abort(404, 'Dokumen PKB SEKAR tidak ditemukan');
            }

            // Get file contents
            $fileContents = file_get_contents($pdfPath);

            // Return PDF response to display in browser
            return response($fileContents)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="PKB-SEKAR.pdf"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            // Log error and return user-friendly message
            \Log::error('Error displaying PKB SEKAR document: ' . $e->getMessage());
            abort(500, 'Terjadi kesalahan saat memuat dokumen PKB SEKAR');
        }
    }

    /**
     * Download the PKB SEKAR document
     */
    public function download(Request $request)
    {
        try {
            // Path to the PKB PDF file
            $pdfPath = public_path('documents/pkb-sekar.pdf');

            // Alternative: if stored in storage/app/public/documents/
            // $pdfPath = storage_path('app/public/documents/pkb-sekar.pdf');

            // Check if file exists
            if (!file_exists($pdfPath)) {
                return redirect()->back()->with('error', 'Dokumen PKB SEKAR tidak ditemukan');
            }

            // Generate download filename with timestamp
            $downloadName = 'PKB-SEKAR-' . date('Y-m-d') . '.pdf';

            // Return download response
            return response()->download($pdfPath, $downloadName, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $downloadName . '"'
            ]);

        } catch (\Exception $e) {
            // Log error and return with error message
            \Log::error('Error downloading PKB SEKAR document: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh dokumen PKB SEKAR');
        }
    }

    /**
     * Get PKB document info (for admin purposes if needed)
     */
    public function getDocumentInfo()
    {
        try {
            $pdfPath = public_path('documents/pkb-sekar.pdf');

            if (!file_exists($pdfPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak ditemukan'
                ]);
            }

            $fileInfo = [
                'exists' => true,
                'size' => filesize($pdfPath),
                'size_formatted' => $this->formatBytes(filesize($pdfPath)),
                'last_modified' => date('Y-m-d H:i:s', filemtime($pdfPath)),
                'path' => $pdfPath
            ];

            return response()->json([
                'success' => true,
                'data' => $fileInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}