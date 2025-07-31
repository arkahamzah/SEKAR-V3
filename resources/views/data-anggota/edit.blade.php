@extends('layouts.app')

@section('title', 'Edit Anggota')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Anggota</h1>
            <p class="text-gray-600 mt-1">Edit data anggota {{ $member->name }}</p>
        </div>
        <a href="{{ route('data-anggota.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Informasi Anggota</h2>
        </div>

        <form action="{{ route('data-anggota.update', $member->nik) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <!-- Alert Error -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h4 class="font-medium">Terdapat kesalahan dalam form:</h4>
                            <ul class="mt-1 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- NIK (Read Only) -->
                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">
                        NIK
                    </label>
                    <input type="text" id="nik" name="nik" value="{{ $member->nik }}" readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500">NIK tidak dapat diubah</p>
                </div>

                <!-- Nama -->
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $member->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-500 @enderror"
                           placeholder="Masukkan nama lengkap">
                    @error('nama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $member->email) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                           placeholder="Masukkan alamat email">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No Telepon -->
                <div>
                    <label for="no_telp" class="block text-sm font-medium text-gray-700 mb-2">
                        No. Telepon
                    </label>
                    <input type="text" id="no_telp" name="no_telp" value="{{ old('no_telp', $member->NO_TELP) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('no_telp') border-red-500 @enderror"
                           placeholder="Masukkan nomor telepon">
                    @error('no_telp')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- DPW -->
                <div>
                    <label for="dpw" class="block text-sm font-medium text-gray-700 mb-2">
                        DPW (Dewan Pengurus Wilayah)
                    </label>
                    <select id="dpw" name="dpw" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('dpw') border-red-500 @enderror">
                        <option value="">Pilih DPW</option>
                        @foreach($dpwList as $dpw)
                            <option value="{{ $dpw }}" {{ old('dpw', $member->DPW) == $dpw ? 'selected' : '' }}>
                                {{ $dpw }}
                            </option>
                        @endforeach
                    </select>
                    @error('dpw')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- DPD -->
                <div>
                    <label for="dpd" class="block text-sm font-medium text-gray-700 mb-2">
                        DPD (Dewan Pengurus Daerah)
                    </label>
                    <select id="dpd" name="dpd" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('dpd') border-red-500 @enderror">
                        <option value="">Pilih DPD</option>
                        @foreach($dpdList as $dpd)
                            <option value="{{ $dpd }}" {{ old('dpd', $member->DPD) == $dpd ? 'selected' : '' }}>
                                {{ $dpd }}
                            </option>
                        @endforeach
                    </select>
                    @error('dpd')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Iuran Wajib -->
                <div>
                    <label for="iuran_wajib" class="block text-sm font-medium text-gray-700 mb-2">
                        Iuran Wajib (Rp)
                    </label>
                    <input type="number" id="iuran_wajib" name="iuran_wajib" value="{{ old('iuran_wajib', $member->IURAN_WAJIB) }}" min="0" step="1000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('iuran_wajib') border-red-500 @enderror"
                           placeholder="0">
                    @error('iuran_wajib')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Iuran Sukarela -->
                <div>
                    <label for="iuran_sukarela" class="block text-sm font-medium text-gray-700 mb-2">
                        Iuran Sukarela (Rp)
                    </label>
                    <input type="number" id="iuran_sukarela" name="iuran_sukarela" value="{{ old('iuran_sukarela', $member->IURAN_SUKARELA) }}" min="0" step="1000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('iuran_sukarela') border-red-500 @enderror"
                           placeholder="0">
                    @error('iuran_sukarela')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Terdaftar (Read Only) -->
                <div>
                    <label for="tanggal_terdaftar" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Terdaftar
                    </label>
                    <input type="text" id="tanggal_terdaftar" 
                           value="{{ $member->TANGGAL_TERDAFTAR ? \Carbon\Carbon::parse($member->TANGGAL_TERDAFTAR)->format('d-m-Y H:i') : '-' }}" readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500">Tanggal terdaftar tidak dapat diubah</p>
                </div>
            </div>

            <!-- Info Card -->
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm text-yellow-700">
                        <p class="font-medium">Perhatian:</p>
                        <ul class="mt-1 list-disc list-inside">
                            <li>NIK dan tanggal terdaftar tidak dapat diubah</li>
                            <li>Perubahan email akan mempengaruhi login anggota</li>
                            <li>Field yang bertanda (*) wajib diisi</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('data-anggota.index') }}" 
                   class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection