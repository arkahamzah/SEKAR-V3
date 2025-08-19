@extends('layouts.app')

@section('title', 'Tambah Pengurus Baru')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Pengurus Baru</h1>
            <p class="text-gray-600 mt-1">Masukkan NIK anggota aktif untuk dijadikan pengurus.</p>
        </div>
        <a href="{{ route('data-anggota.index', ['tab' => 'pengurus']) }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Formulir Tambah Pengurus</h2>
        </div>

        <form action="{{ route('data-anggota.storePengurus') }}" method="POST">
            @csrf
            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <strong class="font-bold">Error!</strong>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nik" class="block text-sm font-medium text-gray-700">NIK Karyawan <span class="text-red-500">*</span></label>
                        <input type="text" id="nik" name="nik" value="{{ old('nik') }}" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Masukkan NIK dan tunggu info muncul">
                        <div id="nik-spinner" class="hidden mt-2"><svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>
                        <p id="nik-status" class="mt-2 text-sm"></p>
                    </div>

                    <div>
                        <label for="id_roles" class="block text-sm font-medium text-gray-700">Role <span class="text-red-500">*</span></label>
                        <select id="id_roles" name="id_roles" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->ID }}" {{ old('id_roles') == $role->ID ? 'selected' : '' }}>
                                    {{ $role->NAME }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div id="info-karyawan" class="hidden mt-6 p-4 bg-gray-50 border rounded-lg">
                    <h3 class="font-semibold text-gray-800 mb-2">Informasi Karyawan:</h3>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <dt class="text-gray-500">Nama</dt><dd id="info-nama" class="text-gray-900 font-medium"></dd>
                        <dt class="text-gray-500">Posisi</dt><dd id="info-posisi" class="text-gray-900"></dd>
                        <dt class="text-gray-500">DPW</dt><dd id="info-dpw" class="text-gray-900"></dd>
                        <dt class="text-gray-500">DPD</dt><dd id="info-dpd" class="text-gray-900"></dd>
                    </dl>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-4 pt-6 border-t px-6 pb-6">
                <a href="{{ route('data-anggota.index', ['tab' => 'pengurus']) }}" class="px-4 py-2 border rounded-lg text-sm font-medium">Batal</a>
                <button type="submit" id="submit-button" disabled
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg disabled:bg-blue-300 disabled:cursor-not-allowed">
                    Simpan Pengurus
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nikInput = document.getElementById('nik');
    const nikStatus = document.getElementById('nik-status');
    const spinner = document.getElementById('nik-spinner');
    const infoBox = document.getElementById('info-karyawan');
    const submitButton = document.getElementById('submit-button');

    let fetchTimeout;

    nikInput.addEventListener('input', function () {
        clearTimeout(fetchTimeout);
        infoBox.classList.add('hidden');
        nikStatus.textContent = '';
        nikStatus.className = 'mt-2 text-sm';
        submitButton.disabled = true;

        const nik = this.value.trim();
        if (nik.length >= 5) { 
            spinner.classList.remove('hidden');
            fetchTimeout = setTimeout(() => {
                fetchKaryawanInfo(nik);
            }, 800);
        } else {
            spinner.classList.add('hidden');
        }
    });

    function fetchKaryawanInfo(nik) {
        // =================================================================
        // PERBAIKAN: Menggunakan URL dari route Laravel
        // =================================================================
        const url = `{{ url('/data-anggota/get-karyawan') }}/${nik}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                spinner.classList.add('hidden');
                if (data.status === 'success') {
                    nikStatus.textContent = 'Anggota ditemukan.';
                    nikStatus.classList.add('text-green-600');
                    displayInfo(data.data);
                    submitButton.disabled = false;
                } else {
                    nikStatus.textContent = data.message;
                    nikStatus.classList.add('text-red-600');
                    infoBox.classList.add('hidden');
                    submitButton.disabled = true;
                }
            })
            .catch(error => {
                spinner.classList.add('hidden');
                nikStatus.textContent = 'Terjadi kesalahan saat mengambil data.';
                nikStatus.classList.add('text-red-600');
                console.error('Fetch error:', error);
            });
    }

    function displayInfo(data) {
        document.getElementById('info-nama').textContent = data.V_NAMA_KARYAWAN || '-';
        document.getElementById('info-posisi').textContent = data.V_SHORT_POSISI || '-';
        document.getElementById('info-dpw').textContent = data.DPW || '-';
        document.getElementById('info-dpd').textContent = data.DPD || '-';
        infoBox.classList.remove('hidden');
    }
});
</script>
@endpush
@endsection