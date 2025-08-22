@extends('layouts.app')

@section('title', 'Dashboard - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="p-6">

        {{-- Bagian Greeting (Tidak ada perubahan) --}}
        <div class="mb-6 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg text-white">
            {{-- ... Konten Greeting ... --}}
        </div>

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard SEKAR</h1>
            <p class="text-gray-600 text-sm mt-1">Ringkasan data keanggotaan dan pengurus SEKAR</p>
        </div>

        {{-- Bagian Statistik Card (Tidak ada perubahan) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- ... Konten Card Statistik ... --}}
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Pemetaan DPW - DPD</h2>
                <p class="text-sm text-gray-600 mt-1">Data distribusi anggota berdasarkan wilayah</p>
            </div>

            <div class="p-6">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-4 mb-6 items-end">
                    <div>
                        <label for="filterDPW" class="block text-xs font-medium text-gray-700 mb-1">Filter DPW:</label>
                        {{-- Dropdown akan submit form secara otomatis saat nilainya berubah --}}
                        <select name="dpw" id="filterDPW" class="border border-gray-300 rounded px-3 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none min-w-[120px]" onchange="this.form.submit()">
                            <option value="">Semua DPW</option>
                            @foreach($allDpwOptions as $dpw)
                                {{-- Menjaga nilai yang dipilih setelah submit --}}
                                <option value="{{ $dpw }}" {{ request('dpw') == $dpw ? 'selected' : '' }}>
                                    {{ $dpw }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filterDPD" class="block text-xs font-medium text-gray-700 mb-1">Cari DPD:</label>
                         {{-- Menjaga nilai yang diinput setelah submit --}}
                        <input type="text" name="dpd" id="filterDPD" value="{{ request('dpd') }}" placeholder="Nama DPD..." class="border border-gray-300 rounded px-3 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium transition">
                            Filter
                        </button>
                        <a href="{{ route('dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded text-xs font-medium transition">
                            Reset
                        </a>
                    </div>
                </form>

                <div class="flex-1 overflow-auto rounded-lg border border-gray-200">
                    <table class="w-full" id="mappingTable">
                        <thead class="bg-gray-50 sticky top-0">
                            {{-- ... Header Tabel (Tidak ada perubahan) ... --}}
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($mappingWithStats as $index => $mapping)
                            {{-- Data attribute tidak lagi diperlukan untuk filter JS --}}
                            <tr class="hover:bg-gray-50">
                                {{-- Data ditampilkan seperti biasa --}}
                                <td class="py-3 px-4 text-xs text-gray-900">{{ $mappingWithStats->firstItem() + $index }}</td>
                                <td class="py-3 px-4 text-xs font-medium text-gray-900">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                        {{ $mapping->dpw }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-900">{{ $mapping->dpd }}</td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 font-medium">
                                        {{ number_format($mapping->anggota_aktif) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800 font-medium">
                                        {{ number_format($mapping->pengurus) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-100 text-red-800 font-medium">
                                        {{ number_format($mapping->anggota_keluar) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800 font-medium">
                                        {{ number_format($mapping->non_anggota) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800 font-medium">
                                        {{ number_format($mapping->anggota_aktif + $mapping->pengurus + $mapping->non_anggota) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-500 text-sm">
                                    Tidak ada data yang cocok dengan filter Anda.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($mappingWithStats->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{-- Paginator akan otomatis membawa parameter filter --}}
                        {{ $mappingWithStats->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

@endsection