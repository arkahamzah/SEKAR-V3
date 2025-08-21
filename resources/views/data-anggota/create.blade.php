@extends('layouts.app')
@section('title', 'Tambah Anggota Baru')
@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Anggota Baru</h1>
            <p class="text-gray-600 mt-1">Tambahkan anggota baru ke dalam sistem SEKAR</p>
        </div>
        <a href="{{ route('data-anggota.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">Kembali</a>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Informasi Anggota</h2>
        </div>
        <form action="{{ route('data-anggota.store') }}" method="POST" class="p-6">
            @csrf
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">...</div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">NIK <span class="text-red-500">*</span></label>
                    <input type="text" id="nik" name="nik" value="{{ old('nik') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                </div>
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                </div>
                <div>
                    <label for="dpw" class="block text-sm font-medium text-gray-700 mb-2">DPW <span class="text-red-500">*</span></label>
                    <select id="dpw" name="dpw" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                        <option value="">Pilih DPW</option>
                        @foreach($dpwList as $dpw)
                            <option value="{{ $dpw }}" {{ old('dpw') == $dpw ? 'selected' : '' }}>{{ $dpw }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="dpd" class="block text-sm font-medium text-gray-700 mb-2">DPD <span class="text-red-500">*</span></label>
                    <select id="dpd" name="dpd" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                        <option value="">Pilih DPD</option>
                        @foreach($dpdList as $dpd)
                            <option value="{{ $dpd }}" {{ old('dpd') == $dpd ? 'selected' : '' }}>{{ $dpd }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="iuran_wajib" class="block text-sm font-medium text-gray-700 mb-2">Iuran Wajib (Rp)</label>
                    <input type="number" id="iuran_wajib" name="iuran_wajib" value="{{ old('iuran_wajib', 25000) }}" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                </div>
                <div>
                    <label for="iuran_sukarela" class="block text-sm font-medium text-gray-700 mb-2">Iuran Sukarela (Rp)</label>
                    <input type="number" id="iuran_sukarela" name="iuran_sukarela" value="{{ old('iuran_sukarela') }}" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">...</div>
            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('data-anggota.index') }}" class="px-4 py-2 border rounded-lg text-sm font-medium">Batal</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Simpan Anggota</button>
            </div>
        </form>
    </div>
</div>
@endsection