@extends('layouts.app')

@section('title', 'Edit Anggota')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Anggota</h1>
            {{-- Menggunakan V_NAMA_KARYAWAN dari model Karyawan --}}
            <p class="text-gray-600 mt-1">Edit data anggota {{ $member->V_NAMA_KARYAWAN }}</p>
        </div>
        <a href="{{ route('data-anggota.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">Kembali</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Informasi Anggota</h2>
        </div>

        <form action="{{ route('data-anggota.update', $member->N_NIK) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">...</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">NIK</label>
                    <input type="text" id="nik" value="{{ $member->N_NIK }}" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed">
                </div>
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $member->V_NAMA_KARYAWAN) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                </div>
                <div>
                    <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-2">Lokasi (Kota/Gedung)</label>
                    <input type="text" id="lokasi" value="{{ old('lokasi', $member->V_KOTA_GEDUNG) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500" readonly>
                </div>
                <div>
                    <label for="dpw" class="block text-sm font-medium text-gray-700 mb-2">DPW <span class="text-red-500">*</span></label>
                    <select id="dpw" name="dpw" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                        @foreach($dpwList as $dpw)
                            <option value="{{ $dpw }}" {{ old('dpw', $member->DPW) == $dpw ? 'selected' : '' }}>{{ $dpw }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="dpd" class="block text-sm font-medium text-gray-700 mb-2">DPD <span class="text-red-500">*</span></label>
                    <select id="dpd" name="dpd" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                         @foreach($dpdList as $dpd)
                            <option value="{{ $dpd }}" {{ old('dpd', $member->DPD) == $dpd ? 'selected' : '' }}>{{ $dpd }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="iuran_wajib_display" class="block text-sm font-medium text-gray-700 mb-2">Iuran Wajib</label>
                    <input type="text" value="{{ 'Rp ' . number_format($member->IURAN_WAJIB, 0, ',', '.') }}" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed">
                </div>
                <div>
                    <label for="iuran_sukarela" class="block text-sm font-medium text-gray-700 mb-2">Iuran Sukarela (Rp)</label>
                    <input type="number" id="iuran_sukarela" name="iuran_sukarela" value="{{ old('iuran_sukarela', $member->IURAN_SUKARELA) }}" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                </div>
                <div>
                    <label for="tanggal_terdaftar" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Terdaftar</label>
                    <input type="text" value="{{ $member->TGL_TERDAFTAR ? \Carbon\Carbon::parse($member->TGL_TERDAFTAR)->format('d-m-Y H:i') : '-' }}" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed">
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                <a href="{{ route('data-anggota.index') }}" class="px-4 py-2 border rounded-lg text-sm font-medium">Batal</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection