@extends('layouts.app')

@section('title', 'Buat Advokasi & Aspirasi - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
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

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">1. Pilih Jenis Pengajuan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group">
                                <input type="radio" name="jenis" value="ADVOKASI" class="mt-1 mr-3" required onchange="updateFormBasedOnJenis()">
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
                                <input type="radio" name="jenis" value="ASPIRASI" class="mt-1 mr-3" required onchange="updateFormBasedOnJenis()">
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

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <span id="stepNumber">2</span>. Tujuan Pengajuan
                        </h3>
                        <div id="tujuanOptions" class="space-y-3">
                            </div>
                    </div>

                    <div id="tujuanSpesifik" class="mb-8 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan Spesifik</label>
                        <select name="tujuan_spesifik" id="tujuanSpesifikSelect" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Tujuan Spesifik</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1" id="tujuanSpesifikHint">Akan terisi otomatis berdasarkan lokasi kerja Anda atau pilih manual</p>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <span id="stepNumberDetail">3</span>. Detail Pengajuan
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="judul" class="block text-sm font-medium text-gray-700 mb-2">
                                    Judul <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="judul" 
                                       id="judul" 
                                       value="{{ old('judul') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Tulis judul yang jelas dan singkat"
                                       required
                                       maxlength="200">
                                <p class="text-xs text-gray-500 mt-1">Maksimal 200 karakter</p>
                            </div>
                            
                            <div>
                                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">
                                    Deskripsi <span class="text-red-500">*</span>
                                </label>
                                <textarea name="deskripsi" 
                                          id="deskripsi" 
                                          rows="6"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Jelaskan secara detail..."
                                          required
                                          maxlength="2000">{{ old('deskripsi') }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Maksimal 2000 karakter. Jelaskan kronologi, dampak, dan solusi yang diharapkan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('konsultasi.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                            <span id="submitText">Ajukan</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Informasi Penting</h4>
                    
                    <div class="space-y-4 text-sm">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="font-medium text-blue-900 mb-1">Proses Pengajuan</p>
                                    <ul class="text-blue-700 text-xs space-y-1">
                                        <li>• Pengajuan akan direview dalam 1-3 hari kerja</li>
                                        <li>• Anda akan mendapat notifikasi via email</li>
                                        <li>• Status dapat dipantau di dashboard</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="font-medium text-yellow-900 mb-1">Perhatian</p>
                                    <ul class="text-yellow-700 text-xs space-y-1">
                                        <li>• Pastikan data yang disampaikan akurat</li>
                                        <li>• Sertakan bukti pendukung jika ada</li>
                                        <li>• Gunakan bahasa yang sopan</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="font-medium text-green-900 mb-1">Kerahasiaan</p>
                                    <p class="text-green-700 text-xs">Semua informasi akan dijaga kerahasiaannya sesuai kebijakan SEKAR.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Informasi Anda</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nama:</span>
                            <span class="font-medium">{{ auth()->user()->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">NIK:</span>
                            <span class="font-medium">{{ auth()->user()->nik }}</span>
                        </div>
                        @if($karyawan)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Posisi:</span>
                            <span class="font-medium text-xs">{{ $karyawan->V_SHORT_POSISI }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Unit:</span>
                            <span class="font-medium text-xs">{{ $karyawan->V_SHORT_UNIT }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Lokasi:</span>
                            <span class="font-medium">{{ $karyawan->V_KOTA_GEDUNG }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// [MODIFIED] Data from backend, now more accurate
const availableTargets = @json($availableTargets);
const dpwOptions = @json($dpwOptions);
const dpdOptions = @json($dpdOptions);
const userDPW = @json($userDPW ?? null); // DPW pengguna dari t_karyawan
const userDPD = @json($userDPD ?? null); // DPD pengguna dari t_karyawan

function updateFormBasedOnJenis() {
    const selectedJenis = document.querySelector('input[name="jenis"]:checked');
    const kategoriDiv = document.getElementById('kategoriAdvokasi');
    const stepNumber = document.getElementById('stepNumber');
    const stepNumberDetail = document.getElementById('stepNumberDetail');
    const submitButton = document.getElementById('submitText');
    
    if (!selectedJenis) return;
    
    const jenis = selectedJenis.value.toLowerCase();
    
    // Show/hide kategori advokasi
    if (jenis === 'advokasi') {
        kategoriDiv.classList.remove('hidden');
        stepNumber.textContent = '3';
        stepNumberDetail.textContent = '4';
        submitButton.textContent = 'Ajukan Advokasi';
    } else {
        kategoriDiv.classList.add('hidden');
        stepNumber.textContent = '2';
        stepNumberDetail.textContent = '3';
        submitButton.textContent = 'Ajukan Aspirasi';
    }
    
    // Update target options
    updateTargetOptions(jenis);
}

function updateTargetOptions(jenis) {
    const tujuanOptionsDiv = document.getElementById('tujuanOptions');
    const targets = availableTargets[jenis] || {};
    
    let html = '';
    Object.entries(targets).forEach(([key, value]) => {
        const description = getTargetDescription(key);
        html += `
            <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                <input type="radio" name="tujuan" value="${key}" class="mr-3" required onchange="handleTujuanChange()">
                <div class="flex-1">
                    <span class="font-medium text-gray-900">${value}</span>
                    <p class="text-xs text-gray-500 mt-1">${description}</p>
                </div>
            </label>
        `;
    });
    
    tujuanOptionsDiv.innerHTML = html;
}

function getTargetDescription(target) {
    const descriptions = {
        'DPD': 'Untuk masalah tingkat daerah/lokasi kerja',
        'DPW': 'Untuk masalah tingkat wilayah/provinsi',
        'DPP': 'Untuk masalah tingkat pusat/nasional',
        'GENERAL': 'Untuk aspirasi umum kepada DPP/Pusat'
    };
    
    return descriptions[target] || '';
}

function handleTujuanChange() {
    const selectedTujuan = document.querySelector('input[name="tujuan"]:checked');
    const tujuanSpesifikDiv = document.getElementById('tujuanSpesifik');
    const tujuanSpesifikSelect = document.getElementById('tujuanSpesifikSelect');
    const tujuanSpesifikHint = document.getElementById('tujuanSpesifikHint');
    
    if (!selectedTujuan) {
        tujuanSpesifikDiv.classList.add('hidden');
        return;
    }
    
    const target = selectedTujuan.value;
    
    // Sembunyikan 'Tujuan Spesifik' untuk GENERAL dan DPP
    if (target === 'GENERAL' || target === 'DPP') {
        tujuanSpesifikDiv.classList.add('hidden');
        tujuanSpesifikSelect.value = ''; // Kosongkan nilainya
        tujuanSpesifikSelect.required = false; // Jadikan tidak wajib diisi
        return;
    }
    
    // Tampilkan 'Tujuan Spesifik' untuk DPD/DPW
    tujuanSpesifikDiv.classList.remove('hidden');
    tujuanSpesifikSelect.required = true; // Jadikan wajib diisi
    
    // [MODIFIED] Populate dropdown based on target using accurate user data
    let options = ['<option value="">Pilih Tujuan Spesifik</option>'];
    
    if (target === 'DPW') {
        if (userDPW) {
            // Add auto-mapped option first if exists
            options.push(`<option value="${userDPW}" selected>📍 ${userDPW} (Lokasi Anda)</option>`);
        }
        
        // Add other DPW options, excluding the user's DPW if it was already added
        dpwOptions.forEach(dpw => {
            if (dpw.toUpperCase() !== (userDPW || '').toUpperCase()) {
                options.push(`<option value="${dpw}">${dpw}</option>`);
            }
        });
        
        tujuanSpesifikHint.textContent = userDPW 
            ? `Otomatis terpilih ${userDPW} berdasarkan data lokasi Anda, atau pilih manual`
            : `Pilih DPW tujuan Anda`;
        
    } else if (target === 'DPD') {
        if (userDPD) {
            // Add auto-mapped option first if exists
            options.push(`<option value="${userDPD}" selected>📍 ${userDPD} (Lokasi Anda)</option>`);
        }
        
        // Add other DPD options, excluding the user's DPD if it was already added
        dpdOptions.forEach(dpd => {
            if (dpd.toUpperCase() !== (userDPD || '').toUpperCase()) {
                options.push(`<option value="${dpd}">${dpd}</option>`);
            }
        });
        
        tujuanSpesifikHint.textContent = userDPD
            ? `Otomatis terpilih ${userDPD} berdasarkan data lokasi Anda, atau pilih manual`
            : `Pilih DPD tujuan Anda`;
    }
    
    tujuanSpesifikSelect.innerHTML = options.join('');
}

// Initialize form on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if there's an old jenis value from a failed validation
    const oldJenis = '{{ old("jenis") }}';
    const oldTujuan = '{{ old("tujuan") }}';
    const oldTujuanSpesifik = '{{ old("tujuan_spesifik") }}';
    
    if (oldJenis) {
        const jenisRadio = document.querySelector(`input[name="jenis"][value="${oldJenis}"]`);
        if (jenisRadio) {
            jenisRadio.checked = true;
            updateFormBasedOnJenis();
            
            if (oldTujuan) {
                // Use a small timeout to ensure the target options are rendered first
                setTimeout(() => {
                    const tujuanRadio = document.querySelector(`input[name="tujuan"][value="${oldTujuan}"]`);
                    if (tujuanRadio) {
                        tujuanRadio.checked = true;
                        handleTujuanChange();
                        
                        if(oldTujuanSpesifik) {
                            // Another timeout to ensure the specific dropdown is populated
                            setTimeout(() => {
                                const tujuanSpesifikSelect = document.getElementById('tujuanSpesifikSelect');
                                tujuanSpesifikSelect.value = oldTujuanSpesifik;
                            }, 50);
                        }
                    }
                }, 50);
            }
        }
    }
});
</script>
@endsection