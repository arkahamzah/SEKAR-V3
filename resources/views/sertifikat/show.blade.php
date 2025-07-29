@extends('layouts.app')

@section('title', 'Sertifikat Anggota - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Breadcrumb -->
        <div class="mb-6 no-print">
            <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
                <a href="{{ route('profile.index') }}" class="hover:text-blue-600">Profile</a>
                <span>/</span>
                <span class="text-gray-900">Sertifikat Anggota</span>
            </div>
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Sertifikat Keanggotaan SEKAR</h1>
                <div class="flex space-x-3">
                    <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center no-print">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Cetak
                    </button>
                </div>
            </div>
        </div>

        @if(!$isSignaturePeriodActive)
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg mb-6 no-print">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-yellow-800">Sertifikat Belum Berlaku</p>
                    <p class="text-xs text-yellow-700">
                        Sertifikat akan berlaku pada periode: 
                        @if($periode['start'] && $periode['end'])
                            {{ \Carbon\Carbon::parse($periode['start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($periode['end'])->format('d M Y') }}
                        @else
                            Belum ditentukan
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Certificate Card -->
        <div class="bg-white rounded-lg shadow-lg border-2 border-blue-200 print:shadow-none print:border-gray-400" id="certificate">
            <!-- Header with Logo -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <img src="{{ asset('asset/logo-tabs.png') }}" alt="SEKAR Logo" class="h-16 w-16 bg-white rounded-full p-2">
                        <div>
                            <h2 class="text-2xl font-bold">SERIKAT KARYAWAN TELKOM</h2>
                            <p class="text-blue-100">SERTIFIKAT KEANGGOTAAN</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-blue-100">No. Sertifikat</p>
                        <p class="font-mono text-lg">SEKAR/{{ $joinDate->format('Y/m') }}/{{ $user->nik }}</p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="p-8">
                <div class="text-center mb-8">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">SERTIFIKAT KEANGGOTAAN</h3>
                    <p class="text-gray-600">Diberikan kepada:</p>
                </div>

                <!-- Member Info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <!-- Photo Section -->
                    <div class="flex justify-center">
                        @if($user->profile_picture)
                            <div class="w-32 h-40 rounded-lg overflow-hidden border-2 border-gray-300 bg-white">
                                <img src="{{ asset('storage/profile-pictures/' . $user->profile_picture) }}" 
                                     alt="Foto Anggota" 
                                     class="w-full h-full object-cover">
                                <div class="text-center mt-1">
                                    <p class="text-xs text-gray-500">Foto Anggota</p>
                                </div>
                            </div>
                        @else
                            <div class="w-32 h-40 bg-gray-200 rounded-lg flex items-center justify-center border-2 border-gray-300">
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-gray-400 rounded-full mx-auto mb-2 flex items-center justify-center">
                                        <span class="text-white text-2xl font-bold">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Foto Anggota</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Member Details -->
                    <div class="col-span-2">
                        <div class="text-center mb-6">
                            <h4 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h4>
                            <p class="text-gray-600">NIK: {{ $user->nik }}</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Jabatan:</label>
                                <p class="text-gray-900">{{ $karyawan->V_SHORT_POSISI ?? 'OFF 2 HCM & RISK OPERATION' }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Unit Kerja:</label>
                                <p class="text-gray-900">{{ $karyawan->V_SHORT_UNIT ?? 'HCM RISK OPERATION' }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Divisi:</label>
                                <p class="text-gray-900">{{ $karyawan->V_SHORT_DIVISI ?? 'DIVISI INFORMATION SYSTEM' }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Lokasi Kerja:</label>
                                <p class="text-gray-900">{{ $karyawan->V_SHORT_LOKASI ?? 'BANDUNG' }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Tanggal Bergabung:</label>
                                <p class="text-gray-900">{{ $joinDate->format('d F Y') }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Status:</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Anggota Aktif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statement -->
                <div class="text-center mb-8">
                    <p class="text-gray-700 leading-relaxed">
                        Dengan ini menyatakan bahwa yang bersangkutan telah terdaftar sebagai 
                        <strong>Anggota Serikat Karyawan Telkom (SEKAR)</strong> sejak tanggal 
                        <strong>{{ $joinDate->format('d F Y') }}</strong> dan memiliki hak serta 
                        kewajiban sesuai dengan Anggaran Dasar dan Anggaran Rumah Tangga SEKAR.
                    </p>
                </div>

                <!-- Signatures -->
                @if($isSignaturePeriodActive)
                <div class="signature-section grid grid-cols-2 gap-16 mt-12">
                    <!-- Sekjen Signature -->
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-900 mb-8">Sekretaris Jenderal</p>
                        
                        @if(!empty($signatures['sekjen']))
                            <div class="mb-4">
                                <img src="{{ $signatures['sekjen'] }}" alt="Tanda Tangan Sekjen" class="max-h-16 mx-auto">
                            </div>
                        @else
                            <div class="h-16 border-b border-gray-300 rounded flex items-center justify-center mb-4">
                                <span class="text-xs text-gray-400">Tanda Tangan</span>
                            </div>
                        @endif
                        
                        <div class="border-t border-gray-300 pt-2">
                            <p class="text-sm font-medium text-gray-900">( _____________________ )</p>
                        </div>
                    </div>

                    <!-- Waketum Signature -->
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-900 mb-8">Wakil Ketua Umum</p>
                        
                        @if(!empty($signatures['waketum']))
                            <div class="mb-4">
                                <img src="{{ $signatures['waketum'] }}" alt="Tanda Tangan Waketum" class="max-h-16 mx-auto">
                            </div>
                        @else
                            <div class="h-16 border-b border-gray-300 rounded flex items-center justify-center mb-4">
                                <span class="text-xs text-gray-400">Tanda Tangan</span>
                            </div>
                        @endif
                        
                        <div class="border-t border-gray-300 pt-2">
                            <p class="text-sm font-medium text-gray-900">( _____________________ )</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Footer -->
                <div class="text-center mt-8 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        Sertifikat ini diterbitkan secara digital oleh Sistem Informasi SEKAR<br>
                        Tanggal cetak: {{ now()->format('d F Y, H:i') }} WIB
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Sembunyikan semua elemen kecuali sertifikat */
    body * {
        visibility: hidden;
    }
    
    /* Tampilkan hanya sertifikat dan child elements */
    #certificate, 
    #certificate * {
        visibility: visible;
    }
    
    /* Posisikan sertifikat untuk mengisi halaman */
    #certificate {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 20px !important;
        box-shadow: none !important;
        border: 2px solid #000 !important;
        border-radius: 8px !important;
        page-break-inside: avoid;
        background: white !important;
        transform: scale(0.95);
        transform-origin: top left;
    }
    
    /* Sembunyikan elemen yang tidak ingin dicetak */
    .no-print,
    nav,
    .sidebar,
    .bg-yellow-50,
    button,
    .breadcrumb {
        display: none !important;
        visibility: hidden !important;
    }
    
    /* Reset body untuk print */
    body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        font-size: 12px;
        line-height: 1.4;
    }
    
    /* Pastikan gradien dan warna tercetak */
    .bg-gradient-to-r {
        background: linear-gradient(to right, #1d4ed8, #1e40af) !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
        print-color-adjust: exact;
        color: white !important;
    }
    
    /* Style untuk header sertifikat */
    #certificate .bg-gradient-to-r {
        padding: 24px !important;
        border-radius: 8px 8px 0 0 !important;
    }
    
    /* Ukuran font untuk print */
    #certificate h2 {
        font-size: 20px !important;
        font-weight: bold !important;
        line-height: 1.2 !important;
    }
    
    #certificate h3 {
        font-size: 16px !important;
        font-weight: 600 !important;
    }
    
    #certificate h4 {
        font-size: 18px !important;
        font-weight: bold !important;
    }
    
    #certificate .text-sm {
        font-size: 11px !important;
    }
    
    #certificate .text-xs {
        font-size: 10px !important;
    }
    
    #certificate .text-lg {
        font-size: 14px !important;
    }
    
    #certificate .text-2xl {
        font-size: 18px !important;
    }
    
    /* Atur spacing untuk print */
    #certificate .p-6 {
        padding: 20px !important;
    }
    
    #certificate .p-8 {
        padding: 24px !important;
    }
    
    #certificate .mb-8 {
        margin-bottom: 24px !important;
    }
    
    #certificate .mb-6 {
        margin-bottom: 16px !important;
    }
    
    #certificate .mt-8 {
        margin-top: 24px !important;
    }
    
    #certificate .mt-12 {
        margin-top: 32px !important;
    }
    
    /* Grid layout untuk member info */
    #certificate .grid-cols-3 {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 24px;
        align-items: start;
    }
    
    /* Photo section */
    #certificate .w-32 {
        width: 100px !important;
        height: 120px !important;
    }
    
    #certificate .h-40 {
        height: 120px !important;
    }
    
    /* Pastikan foto profile tercetak dengan benar */
    #certificate img[alt="Foto Anggota"] {
        width: 100px !important;
        height: 120px !important;
        object-fit: cover !important;
        border-radius: 8px !important;
    }
    
    #certificate .w-16 {
        width: 48px !important;
        height: 48px !important;
    }
    
    #certificate .h-16 {
        height: 48px !important;
    }
    
    /* Member details */
    #certificate .space-y-4 > * + * {
        margin-top: 12px !important;
    }
    
    /* Signature section */
    #certificate .signature-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 48px;
        margin-top: 32px !important;
    }
    
    #certificate .gap-16 {
        gap: 48px !important;
    }
    
    /* Signature images */
    #certificate .signature-section img {
        max-height: 48px !important;
        width: auto !important;
    }
    
    /* Footer */
    #certificate .border-t {
        border-top: 1px solid #d1d5db !important;
        padding-top: 16px !important;
        margin-top: 24px !important;
    }
    
    /* Logo */
    #certificate img[alt="SEKAR Logo"] {
        width: 48px !important;
        height: 48px !important;
    }
    
    /* Status badge */
    #certificate .bg-green-100 {
        background-color: #dcfce7 !important;
        color: #166534 !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Borders */
    #certificate .border-gray-300 {
        border-color: #d1d5db !important;
    }
    
    #certificate .border-2 {
        border-width: 1px !important;
    }
    
    /* Text colors */
    #certificate .text-blue-100 {
        color: #dbeafe !important;
    }
    
    #certificate .text-gray-600 {
        color: #4b5563 !important;
    }
    
    #certificate .text-gray-700 {
        color: #374151 !important;
    }
    
    #certificate .text-gray-900 {
        color: #111827 !important;
    }
    
    #certificate .text-gray-500 {
        color: #6b7280 !important;
    }
}

/* Screen styles tetap sama */
@media screen {
    .print-only {
        display: none;
    }
}
</style>

@endsection