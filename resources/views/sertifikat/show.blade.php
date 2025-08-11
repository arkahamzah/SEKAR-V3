@extends('layouts.app')

@section('title', 'Sertifikat Anggota - SEKAR')

@section('content')
<style>
/* Print styles */
@media print {
    body {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    .no-print {
        display: none !important;
    }
    
    .print-only {
        display: block !important;
    }
    
    #certificate {
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        max-width: none !important;
        width: 100% !important;
    }
    
    .certificate-header {
        background: white !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .certificate-container {
        page-break-inside: avoid;
        margin: 0 !important;
        padding: 20px !important;
    }
}

/* Screen styles */
.certificate-container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.certificate-header {
    text-align: center;
    padding: 30px 40px 25px;
    border-bottom: 2px solid #e2e8f0;
}

.certificate-title {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}

.certificate-subtitle {
    font-size: 14px;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 15px;
}

.certificate-contact {
    font-size: 12px;
    color: #64748b;
    line-height: 1.4;
}

.certificate-main {
    padding: 35px 40px;
    position: relative;
}

.certificate-watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1;
    pointer-events: none;
}

.certificate-watermark img {
    width: 300px;
    height: 300px;
    opacity: 0.08;
    object-fit: contain;
}

.certificate-content {
    position: relative;
    z-index: 2;
}

.certificate-declaration {
    text-align: center;
    font-size: 14px;
    color: #374151;
    line-height: 1.6;
    margin-bottom: 25px;
}

.member-info-box {
    padding: 20px;
    margin: 25px 0;
    text-align: center;
}

.member-name-display {
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 5px;
}

.member-id-display {
    font-size: 14px;
    color: #64748b;
}

.certificate-purpose {
    text-align: center;
    font-size: 13px;
    color: #374151;
    line-height: 1.5;
    margin: 25px 0;
}

.certificate-date {
    text-align: center;
    font-size: 14px;
    color: #374151;
    margin: 30px 0 40px;
}

.certificate-signatures {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    margin-top: 40px;
}

.signature-section {
    text-align: center;
}

.signature-title {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 50px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.signature-image {
    height: 60px;
    margin-bottom: 10px;
    display: flex;
    align-items: end;
    justify-content: center;
}

.signature-image img {
    max-height: 50px;
    width: auto;
}

.signature-name {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    border-bottom: 1px solid #d1d5db;
    padding-bottom: 2px;
    min-width: 160px;
    text-align: center;
    display: inline-block;
}

@media (max-width: 768px) {
    .certificate-container {
        margin: 0;
        border-radius: 0;
    }
    
    .certificate-header, .certificate-main {
        padding: 20px;
    }
    
    .certificate-signatures {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .certificate-watermark img {
        width: 200px;
        height: 200px;
        opacity: 0.08;
        object-fit: contain;
    }
}
</style>

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
                    <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center">
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

        <!-- Certificate -->
        <div class="certificate-container" id="certificate">
            <!-- Header -->
            <div class="certificate-header">
                <h1 class="certificate-title">KEANGGOTAAN SEKAR TELKOM</h1>
                <div class="certificate-subtitle">
                    Dewan Pengurus Pusat Serikat Karyawan – PT. Telekomunikasi Indonesia, Tbk.<br>
                    Gedung Kantor Pusat Telkom Lt IV Jl Japati No 1 Bandung – 40133
                </div>
                <div class="certificate-contact">
                    Telp. +62 22 4524103 – Fax +62 22 4524110
                </div>
            </div>

            <!-- Main Content -->
            <div class="certificate-main">
                <!-- Watermark -->
                <div class="certificate-watermark">
                    <img src="{{ asset('asset/logo-tabs.png') }}" alt="SEKAR Logo Watermark" 
                         onerror="this.style.display='none'">
                </div>
                
                <div class="certificate-content">
                    <!-- Declaration -->
                    <div class="certificate-declaration">
                        Dengan ini menyatakan bahwa yang bersangkutan dibawah ini adalah anggota <strong>SEKAR TELKOM</strong>
                    </div>

                    <!-- Member Information -->
                    <div class="member-info-box">
                        <div class="member-name-display">NAMA : {{ strtoupper($user->name) }}</div>
                        <div class="member-id-display">NAS : {{ $user->nik }}</div>
                    </div>

                    <!-- Purpose Statement -->
                    <div class="certificate-purpose">
                        Demikian bukti keanggotaan <strong>SEKAR</strong> ini untuk dipergunakan sebagaimana mestinya
                    </div>

                    <!-- Date and Location -->
                    <div class="certificate-date">
                        Bandung, {{ $joinDate->format('d-M-Y') }}
                    </div>

                    <!-- Signatures -->
                    @if($isSignaturePeriodActive)
                    <div class="certificate-signatures">
                        <div class="signature-section">
                            <div class="signature-title">Ketua Umum Sekar</div>
                            <div class="signature-image">
                                @if(!empty($settings['waketum_signature']))
                                    <img src="{{ asset('storage/signatures/' . $settings['waketum_signature']) }}" alt="Tanda Tangan Ketua Umum">
                                @endif
                            </div>
                            <div class="signature-name">
                                {{ $settings['waketum_name'] ?? 'ASEP MULYANA' }}
                            </div>
                        </div>
                        <div class="signature-section">
                            <div class="signature-title">Sekjen Sekar</div>
                            <div class="signature-image">
                                @if(!empty($settings['sekjen_signature']))
                                    <img src="{{ asset('storage/signatures/' . $settings['sekjen_signature']) }}" alt="Tanda Tangan Sekjen">
                                @endif
                            </div>
                            <div class="signature-name">
                                {{ $settings['sekjen_name'] ?? 'ABDUL KARIM' }}
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="certificate-signatures">
                        <div class="signature-section">
                            <div class="signature-title">Ketua Umum Sekar</div>
                            <div class="signature-image"></div>
                            <div class="signature-name">
                                ___________________
                            </div>
                        </div>
                        <div class="signature-section">
                            <div class="signature-title">Sekjen Sekar</div>
                            <div class="signature-image"></div>
                            <div class="signature-name">
                                ___________________
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection