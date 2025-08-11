@extends('layouts.app')

@section('title', 'Pengaturan - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div id="successAlert" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 alert">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Pengaturan</h1>
            <p class="text-gray-600 text-sm mt-1">Kelola pengaturan tanda tangan, periode, dan dokumen PKB</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pengaturan Tanda Tangan & Periode</h3>
                <p class="text-sm text-gray-600 mt-1">Upload tanda tangan dan atur periode berlaku untuk sertifikat anggota</p>
            </div>

            <form method="POST" action="{{ route('setting.update') }}" enctype="multipart/form-data" class="p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanda Tangan Sekretaris Jenderal</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                            @if(!empty($settings['sekjen_signature']))
                                <div class="text-center mb-4">
                                    <img src="{{ asset('storage/signatures/' . $settings['sekjen_signature']) }}"
                                         alt="Tanda Tangan Sekjen" class="max-h-20 mx-auto border rounded">
                                </div>
                            @endif
                            <input type="file" name="sekjen_signature" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanda Tangan Wakil Ketua Umum</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                             @if(!empty($settings['waketum_signature']))
                                <div class="text-center mb-4">
                                    <img src="{{ asset('storage/signatures/' . $settings['waketum_signature']) }}"
                                         alt="Tanda Tangan Waketum" class="max-h-20 mx-auto border rounded">
                                </div>
                            @endif
                            <input type="file" name="waketum_signature" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Periode Berlaku Tanda Tangan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                            <input type="date" name="signature_periode_start" value="{{ $settings['signature_periode_start'] }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Berakhir</label>
                            <input type="date" name="signature_periode_end" value="{{ $settings['signature_periode_end'] }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 mt-6 border-t border-gray-200">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                        Simpan Tanda Tangan & Periode
                    </button>
                </div>
            </form>
        </div>

        @if(Auth::user()->hasRole('ADM'))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pengaturan Dokumen PKB</h3>
                <p class="text-sm text-gray-600 mt-1">Unggah dokumen Perjanjian Kerja Bersama (PKB) terbaru.</p>
            </div>

            <form method="POST" action="{{ route('setting.pkb.update') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="pkb_document" class="block text-sm font-medium text-gray-700 mb-2">File Dokumen PKB (PDF)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                            <input type="file" name="pkb_document" id="pkb_document" accept=".pdf" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">File yang ada akan ditimpa dengan yang baru.</p>
                        </div>
                    </div>

                    @if(!empty($settings['pkb_document_info']))
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-sm font-medium text-gray-800">Dokumen Saat Ini:</p>
                        <div class="text-xs text-gray-600 mt-2 space-y-1">
                            <p><strong>Nama File:</strong> {{ $settings['pkb_document_info']['original_name'] ?? 'N/A' }}</p>
                            <p><strong>Ukuran:</strong> {{ $settings['pkb_document_info']['size'] ?? 'N/A' }}</p>
                            <p><strong>Terakhir Diperbarui:</strong> {{ $settings['pkb_document_info']['last_modified'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="flex justify-end pt-6 mt-6 border-t border-gray-200">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-medium">
                        Upload Dokumen PKB
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection