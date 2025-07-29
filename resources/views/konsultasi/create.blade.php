@extends('layouts.app')

@section('title', 'Buat Advokasi & Aspirasi - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
                <a href="{{ route('konsultasi.index') }}" class="hover:text-blue-600">Advokasi & Aspirasi</a>
                <span>/</span>
                <span class="text-gray-900">Buat Baru</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Buat Advokasi & Aspirasi</h1>
            <p class="text-gray-600 text-sm mt-1">Sampaikan aspirasi atau ajukan advokasi kepada pengurus SEKAR</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('konsultasi.store') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    @csrf
                    
                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                            <h4 class="font-medium mb-2">Terdapat kesalahan pada form:</h4>
                            <ul class="list-disc list-inside text-sm space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Step 1: Jenis -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">1. Pilih Jenis Pengajuan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group">
                                <input type="radio" name="jenis" value="ADVOKASI" class="mt-1 mr-3" required onchange="toggleKategoriAdvokasi()">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        <span class="font-medium text-gray-900 group-hover:text-blue-700">Advokasi</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Bantuan hukum, perlindungan hak pekerja, atau penanganan pelanggaran</p>
                                    <div class="mt-2 text-xs text-gray-500">
                                        <strong>Contoh:</strong> Diskriminasi, pelecehan, pelanggaran K3, masalah upah
                                    </div>
                                </div>
                            </label>
                            
                            <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group">
                                <input type="radio" name="jenis" value="ASPIRASI" class="mt-1 mr-3" required onchange="toggleKategoriAdvokasi()">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                        <span class="font-medium text-gray-900 group-hover:text-blue-700">Aspirasi</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Saran, masukan, atau ide untuk perbaikan kebijakan dan layanan</p>
                                    <div class="mt-2 text-xs text-gray-500">
                                        <strong>Contoh:</strong> Usulan program, saran kebijakan, feedback layanan
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Kategori Advokasi -->
                    <div id="kategoriAdvokasi" class="mb-8 hidden">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">2. Kategori Advokasi</h3>
                        <select name="kategori_advokasi" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Kategori</option>
                            @foreach($kategoriAdvokasi as $kategori)
                                <option value="{{ $kategori }}" {{ old('kategori_advokasi') === $kategori ? 'selected' : '' }}>
                                    {{ $kategori }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih kategori yang paling sesuai dengan masalah yang dihadapi</p>
                    </div>

                    <!-- Step 3: Tujuan -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <span id="stepNumber">2</span>. Tujuan Pengajuan
                        </h3>
                        <div class="space-y-3">
                            @foreach($availableTargets as $key => $value)
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="tujuan" value="{{ $key }}" class="mr-3" required onchange="toggleTujuanSpesifik()" 
                                       {{ old('tujuan') === $key ? 'checked' : '' }}>
                                <div class="flex-1">
                                    <span class="font-medium text-gray-900">{{ $value }}</span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        @if($key === 'DPD')
                                            Untuk masalah tingkat daerah/lokasi kerja
                                        @elseif($key === 'DPW')
                                            Untuk masalah tingkat wilayah/provinsi
                                        @elseif($key === 'DPP')
                                            Untuk masalah tingkat pusat/nasional
                                        @else
                                            Untuk aspirasi umum kepada SEKAR
                                        @endif
                                    </p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tujuan Spesifik -->
                    <div id="tujuanSpesifik" class="mb-8 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan Spesifik</label>
                        <input type="text" name="tujuan_spesifik" id="tujuanSpesifikInput" 
                               value="{{ old('tujuan_spesifik') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nama DPW/DPD/DPP tujuan">
                        <p class="text-xs text-gray-500 mt-1">Akan terisi otomatis berdasarkan lokasi kerja Anda</p>
                    </div>

                    <!-- Step 4: Detail -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <span id="stepNumberDetail">3</span>. Detail Pengajuan
                        </h3>
                        
                        <!-- Judul -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Judul <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="judul" value="{{ old('judul') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Judul singkat dan jelas" required maxlength="200">
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>Judul yang jelas akan membantu proses penanganan</span>
                                <span id="judulCounter">0/200</span>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Deskripsi Lengkap <span class="text-red-500">*</span>
                            </label>
                            <textarea name="deskripsi" rows="8" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Jelaskan secara detail..." required>{{ old('deskripsi') }}</textarea>
                            <div class="text-xs text-gray-500 mt-1">
                                <p class="mb-1"><strong>Tips penulisan yang baik:</strong></p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Jelaskan kronologi kejadian secara runtut</li>
                                    <li>Sertakan tanggal, tempat, dan pihak yang terlibat</li>
                                    <li>Lampirkan bukti jika ada (nomor surat, foto, dokumen)</li>
                                    <li>Sebutkan dampak yang dirasakan</li>
                                    <li>Sampaikan harapan solusi yang diinginkan</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('konsultasi.index') }}" 
                           class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg text-center hover:bg-gray-300 transition font-medium">
                            Batal
                        </a>
                        <button type="submit" 
                                class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-medium">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Info Card Pengirim -->
                @if($karyawan)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Informasi Pengirim</h4>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-blue-600 font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-gray-600">NIK: {{ Auth::user()->nik }}</p>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="grid grid-cols-1 gap-2">
                            <div>
                                <span class="text-gray-600">Jabatan:</span>
                                <p class="font-medium">{{ $karyawan->V_SHORT_POSISI }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Unit:</span>
                                <p class="font-medium">{{ $karyawan->V_SHORT_UNIT }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Divisi:</span>
                                <p class="font-medium">{{ $karyawan->V_SHORT_DIVISI }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Lokasi:</span>
                                <p class="font-medium">{{ $karyawan->V_KOTA_GEDUNG }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Info Process -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h4 class="font-semibold text-blue-900 mb-4">Proses Penanganan</h4>
                    <div class="space-y-3 text-sm text-blue-800">
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs mr-3 mt-0.5">1</div>
                            <div>
                                <p class="font-medium">Pengajuan Diterima</p>
                                <p class="text-blue-700">Admin akan menerima notifikasi email</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs mr-3 mt-0.5">2</div>
                            <div>
                                <p class="font-medium">Review & Tindak Lanjut</p>
                                <p class="text-blue-700">Admin akan mengkaji dan memberikan respon</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs mr-3 mt-0.5">3</div>
                            <div>
                                <p class="font-medium">Solusi & Penutupan</p>
                                <p class="text-blue-700">Masalah diselesaikan dan status ditutup</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help & Contact -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Butuh Bantuan?</h4>
                    <div class="space-y-3 text-sm text-gray-700">
                        <p>Jika Anda mengalami kesulitan dalam mengisi form atau memerlukan konsultasi awal:</p>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span>admin@sekar.telkom.co.id</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span>0800-1-SEKAR (73527)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for judul
    const judulInput = document.querySelector('input[name="judul"]');
    const judulCounter = document.getElementById('judulCounter');
    
    judulInput.addEventListener('input', function() {
        judulCounter.textContent = `${this.value.length}/200`;
    });
    
    // Initialize form state
    toggleKategoriAdvokasi();
    toggleTujuanSpesifik();
});

function toggleKategoriAdvokasi() {
    const jenisRadios = document.querySelectorAll('input[name="jenis"]');
    const kategoriAdvokasi = document.getElementById('kategoriAdvokasi');
    const stepNumber = document.getElementById('stepNumber');
    const stepNumberDetail = document.getElementById('stepNumberDetail');
    
    let selectedJenis = null;
    jenisRadios.forEach(radio => {
        if (radio.checked) {
            selectedJenis = radio.value;
        }
    });
    
    if (selectedJenis === 'ADVOKASI') {
        kategoriAdvokasi.classList.remove('hidden');
        kategoriAdvokasi.querySelector('select').required = true;
        stepNumber.textContent = '3';
        stepNumberDetail.textContent = '4';
    } else {
        kategoriAdvokasi.classList.add('hidden');
        kategoriAdvokasi.querySelector('select').required = false;
        kategoriAdvokasi.querySelector('select').value = '';
        stepNumber.textContent = '2';
        stepNumberDetail.textContent = '3';
    }
}

function toggleTujuanSpesifik() {
    const tujuanRadios = document.querySelectorAll('input[name="tujuan"]');
    const tujuanSpesifik = document.getElementById('tujuanSpesifik');
    const tujuanSpesifikInput = document.getElementById('tujuanSpesifikInput');
    
    let selectedTujuan = null;
    tujuanRadios.forEach(radio => {
        if (radio.checked) {
            selectedTujuan = radio.value;
        }
    });
    
    if (selectedTujuan && selectedTujuan !== 'GENERAL') {
        tujuanSpesifik.classList.remove('hidden');
        tujuanSpesifikInput.required = true;
        
        // Auto-fill based on selection
        const selectedOption = document.querySelector(`input[name="tujuan"][value="${selectedTujuan}"]`).closest('label').querySelector('span');
        tujuanSpesifikInput.value = selectedOption.textContent;
    } else {
        tujuanSpesifik.classList.add('hidden');
        tujuanSpesifikInput.required = false;
        tujuanSpesifikInput.value = '';
    }
}
</script>

<style>
/* Radio button enhancements */
input[type="radio"]:checked + div {
    color: #2563eb;
}

input[type="radio"]:checked + div .group-hover\:text-blue-700 {
    color: #1d4ed8 !important;
}

/* Step transitions */
#kategoriAdvokasi {
    transition: all 0.3s ease;
}

#tujuanSpesifik {
    transition: all 0.3s ease;
}

/* Form validation styling */
input:invalid {
    border-color: #ef4444;
}

input:valid {
    border-color: #10b981;
}

/* Character counter */
#judulCounter {
    transition: color 0.2s ease;
}

input[name="judul"]:focus + div #judulCounter {
    color: #2563eb;
}
</style>
@endsection