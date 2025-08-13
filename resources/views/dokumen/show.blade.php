@extends('layouts.app')

@section('title', 'Dokumen SEKAR')

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Dokumen SEKAR</h1>
                <p class="text-gray-600">Dokumen-dokumen penting terkait Serikat Pekerja</p>
            </div>

            <div class="flex space-x-3">
                <a href="{{ route('dokumen.download') }}"
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

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium text-blue-900 mb-1">Tentang Dokumen Ini</h3>
                <p class="text-sm text-blue-800 leading-relaxed">
                    Dokumen ini berisi informasi penting yang disediakan oleh Serikat Pekerja untuk para anggotanya.
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Dokumen-SEKAR.pdf</h3>
                        <p class="text-sm text-gray-500">Dokumen Serikat Pekerja</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative" style="min-height: 800px;">
            <iframe id="pdfViewer"
                    src="{{ route('dokumen.show') }}?v={{ time() }}#toolbar=1&navpanes=1&scrollbar=1"
                    class="w-full border-0"
                    style="height: 800px;">
                <p class="p-6 text-center text-gray-500">
                    Browser Anda tidak mendukung tampilan PDF.
                    <a href="{{ route('dokumen.download') }}?v={{ time() }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                        Klik di sini untuk mengunduh dokumen.
                    </a>
                </p>
            </iframe>

            <div id="pdfLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-2 text-gray-600">Memuat dokumen...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pdfViewer = document.getElementById('pdfViewer');
    const pdfLoading = document.getElementById('pdfLoading');
    
    pdfViewer.addEventListener('load', function() {
        pdfLoading.style.display = 'none';
    });

    setTimeout(function() {
        if (pdfLoading) {
            pdfLoading.style.display = 'none';
        }
    }, 5000);
});
</script>