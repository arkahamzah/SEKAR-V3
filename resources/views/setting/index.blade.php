@extends('layouts.app')

@section('title', 'Pengaturan Sistem - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Pengaturan Sistem</h1>
            <p class="text-gray-600 text-sm mt-1">Kelola pengaturan tanda tangan dan periode berlaku sertifikat</p>
        </div>

        <!-- Settings Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pengaturan Tanda Tangan Sertifikat</h3>
                <p class="text-sm text-gray-600 mt-1">Upload tanda tangan dan atur periode berlaku untuk sertifikat anggota</p>
            </div>

            <form method="POST" action="{{ route('setting.update') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                
                <!-- Signature Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Sekjen Signature -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanda Tangan Sekretaris Jenderal</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                            @if(!empty($settings['sekjen_signature']))
                                <div class="text-center mb-4">
                                    <img src="{{ asset('storage/signatures/' . $settings['sekjen_signature']) }}" 
                                         alt="Tanda Tangan Sekjen" 
                                         class="max-h-20 mx-auto border rounded">
                                    <p class="text-xs text-gray-500 mt-1">Tanda tangan saat ini</p>
                                </div>
                            @endif
                            <input type="file" name="sekjen_signature" accept="image/*" 
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG (Maks. 2MB)</p>
                        </div>
                    </div>

                    <!-- Waketum Signature -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanda Tangan Wakil Ketua Umum</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                            @if(!empty($settings['waketum_signature']))
                                <div class="text-center mb-4">
                                    <img src="{{ asset('storage/signatures/' . $settings['waketum_signature']) }}" 
                                         alt="Tanda Tangan Waketum" 
                                         class="max-h-20 mx-auto border rounded">
                                    <p class="text-xs text-gray-500 mt-1">Tanda tangan saat ini</p>
                                </div>
                            @endif
                            <input type="file" name="waketum_signature" accept="image/*" 
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG (Maks. 2MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Period Settings -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Periode Berlaku Tanda Tangan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                            <input type="date" name="signature_periode_start" 
                                   value="{{ $settings['signature_periode_start'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Berakhir</label>
                            <input type="date" name="signature_periode_end" 
                                   value="{{ $settings['signature_periode_end'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Tanda tangan hanya akan muncul di sertifikat jika tanggal saat ini berada dalam periode yang ditentukan.
                    </p>
                </div>

                <!-- Current Period Status -->
                @if(!empty($settings['signature_periode_start']) && !empty($settings['signature_periode_end']))
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        @php
                            $today = now()->format('Y-m-d');
                            $isActive = $today >= $settings['signature_periode_start'] && $today <= $settings['signature_periode_end'];
                        @endphp
                        
                        @if($isActive)
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-green-800">Periode Aktif</p>
                                <p class="text-xs text-green-700">
                                    Tanda tangan sedang berlaku: {{ \Carbon\Carbon::parse($settings['signature_periode_start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($settings['signature_periode_end'])->format('d M Y') }}
                                </p>
                            </div>
                        @else
                            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">Periode Tidak Aktif</p>
                                <p class="text-xs text-yellow-700">
                                    Tanda tangan akan berlaku: {{ \Carbon\Carbon::parse($settings['signature_periode_start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($settings['signature_periode_end'])->format('d M Y') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Submit Button -->
                <div class="flex justify-end pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>

        <!-- Information Card -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-blue-900 mb-1">Informasi Pengaturan</h4>
                    <ul class="text-xs text-blue-700 space-y-1">
                        <li>• Tanda tangan akan muncul di sertifikat anggota sesuai periode yang ditentukan</li>
                        <li>• Format gambar yang didukung: JPG, PNG dengan ukuran maksimal 2MB</li>
                        <li>• Pastikan tanda tangan memiliki latar belakang transparan untuk hasil terbaik</li>
                        <li>• Perubahan akan langsung berlaku setelah disimpan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection