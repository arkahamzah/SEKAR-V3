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
            <p class="text-gray-600 text-sm mt-1">Kelola pengaturan tanda tangan, periode, dan dokumen penting lainnya</p>
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
                            <input type="date" name="signature_periode_start" value="{{ $settings['signature_periode_start'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Berakhir</label>
                            <input type="date" name="signature_periode_end" value="{{ $settings['signature_periode_end'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
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
                <h3 class="text-lg font-semibold text-gray-900">Pengaturan Dokumen</h3>
                <p class="text-sm text-gray-600 mt-1">Unggah dan kelola dokumen penting untuk anggota (misal: PKB, AD/ART).</p>
            </div>

            <form method="POST" action="{{ route('setting.document.upload') }}" enctype="multipart/form-data" class="p-6 border-b border-gray-200">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="document_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Dokumen <span class="text-red-500">*</span></label>
                        <input type="text" name="document_name" id="document_name" required placeholder="Contoh: Perjanjian Kerja Bersama 2024" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="document_file" class="block text-sm font-medium text-gray-700 mb-2">File (PDF) <span class="text-red-500">*</span></label>
                        <input type="file" name="document_file" id="document_file" accept=".pdf" required class="w-full text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-500 mt-1">Maksimal ukuran: 5MB.</p>
                    </div>
                </div>
                <div class="flex justify-end pt-4 mt-4">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-medium">
                        Upload Dokumen
                    </button>
                </div>
            </form>
            
            <div class="p-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Daftar Dokumen Tersimpan</h4>
                <div class="space-y-3">
                    @forelse($settings['documents'] as $doc)
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0011.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $doc['name'] }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $doc['size'] }} - Diunggah: {{ $doc['uploaded_at'] }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                             <a href="{{ route('dokumen.show', $doc['filename']) }}" target="_blank" class="text-blue-600 hover:text-blue-800 p-1 rounded-md hover:bg-blue-100" title="Lihat">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                            <form action="{{ route('setting.document.delete', $doc['filename']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 p-1 rounded-md hover:bg-red-100" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-sm text-gray-500">
                        Belum ada dokumen yang diunggah.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection