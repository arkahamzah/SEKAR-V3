@extends('layouts.app')

@section('title', 'Dokumen PKB SEKAR')

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Dokumen PKB SEKAR</h1>
                <p class="text-gray-600">Perjanjian Kerja Bersama antara PT Telekomunikasi Indonesia dengan Serikat Pekerja</p>
            </div>

            <!-- Download Button -->
            <div class="flex space-x-3">
                <a href="{{ route('pkb.download') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download PDF
                </a>

                <button onclick="window.print()"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print
                </button>
            </div>
        </div>
    </div>

    <!-- Document Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium text-blue-900 mb-1">Tentang Dokumen PKB SEKAR</h3>
                <p class="text-sm text-blue-800 leading-relaxed">
                    Dokumen ini berisi Perjanjian Kerja Bersama (PKB) yang merupakan kesepakatan antara PT Telekomunikasi Indonesia (Persero) Tbk dengan Serikat Pekerja.
                    PKB ini mengatur hak dan kewajiban pekerja serta perusahaan, termasuk sistem pengupahan, kesejahteraan, waktu kerja, dan ketentuan lainnya.
                </p>
                <div class="mt-2 text-xs text-blue-700">
                    <strong>Berlaku:</strong> 2 (dua) tahun dengan evaluasi tahunan
                </div>
            </div>
        </div>
    </div>

    <!-- PDF Viewer Container -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- PDF Viewer Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">PKB-SEKAR.pdf</h3>
                        <p class="text-sm text-gray-500">Perjanjian Kerja Bersama SEKAR</p>
                    </div>
                </div>

                <!-- Zoom Controls -->
                <div class="flex items-center space-x-2">
                    <button id="zoomOut" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-200 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    <span id="zoomLevel" class="text-sm text-gray-600 min-w-12 text-center">100%</span>
                    <button id="zoomIn" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-200 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                    <button id="fitToWidth" class="px-3 py-1 text-xs text-gray-600 hover:text-gray-900 hover:bg-gray-200 rounded transition-colors">
                        Fit Width
                    </button>
                </div>
            </div>
        </div>

        <!-- PDF Viewer -->
        <div class="relative" style="min-height: 800px;">
            <iframe id="pdfViewer"
                    src="{{ route('pkb.show') }}#toolbar=1&navpanes=1&scrollbar=1"
                    class="w-full border-0"
                    style="height: 800px;">
                <p class="p-6 text-center text-gray-500">
                    Browser Anda tidak mendukung tampilan PDF.
                    <a href="{{ route('pkb.download') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        Klik di sini untuk mengunduh dokumen.
                    </a>
                </p>
            </iframe>

            <!-- Loading Overlay -->
            <div id="pdfLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-2 text-gray-600">Memuat dokumen...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Chapters/Sections Quick Navigation -->
    <div class="mt-6 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Navigasi Cepat - Bab dalam PKB</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h4 class="font-medium text-gray-900 mb-2">BAB I - Ketentuan Umum</h4>
                <p class="text-sm text-gray-600">Definisi dan ruang lingkup PKB</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h4 class="font-medium text-gray-900 mb-2">BAB II - Hak dan Kewajiban</h4>
                <p class="text-sm text-gray-600">Hak pekerja, kewajiban perusahaan</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h4 class="font-medium text-gray-900 mb-2">BAB III - Waktu Kerja</h4>
                <p class="text-sm text-gray-600">Jam kerja dan istirahat</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h4 class="font-medium text-gray-900 mb-2">BAB IV - Upah dan Kesejahteraan</h4>
                <p class="text-sm text-gray-600">Sistem pengupahan dan tunjangan</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h4 class="font-medium text-gray-900 mb-2">BAB V - Cuti dan Libur</h4>
                <p class="text-sm text-gray-600">Jenis cuti dan prosedurnya</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h4 class="font-medium text-gray-900 mb-2">BAB VI - Pengembangan Karier</h4>
                <p class="text-sm text-gray-600">Pelatihan, promosi, dan mutasi</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pdfViewer = document.getElementById('pdfViewer');
    const pdfLoading = document.getElementById('pdfLoading');
    const zoomInBtn = document.getElementById('zoomIn');
    const zoomOutBtn = document.getElementById('zoomOut');
    const fitToWidthBtn = document.getElementById('fitToWidth');
    const zoomLevelSpan = document.getElementById('zoomLevel');

    let currentZoom = 100;

    // Hide loading overlay when PDF loads
    pdfViewer.addEventListener('load', function() {
        pdfLoading.style.display = 'none';
    });

    // Zoom functionality (basic implementation)
    if (zoomInBtn && zoomOutBtn) {
        zoomInBtn.addEventListener('click', function() {
            currentZoom = Math.min(currentZoom + 25, 200);
            updateZoom();
        });

        zoomOutBtn.addEventListener('click', function() {
            currentZoom = Math.max(currentZoom - 25, 50);
            updateZoom();
        });

        fitToWidthBtn.addEventListener('click', function() {
            currentZoom = 100;
            updateZoom();
        });
    }

    function updateZoom() {
        zoomLevelSpan.textContent = currentZoom + '%';
        // Note: Actual zoom implementation would require PDF.js or similar library
        // This is a simplified version
    }

    // Auto-hide loading after timeout as fallback
    setTimeout(function() {
        if (pdfLoading) {
            pdfLoading.style.display = 'none';
        }
    }, 5000);
});
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }

    #pdfViewer {
        height: auto !important;
        min-height: 100vh;
    }
}
</style>
@endsection