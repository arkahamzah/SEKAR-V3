@extends('layouts.app')

@section('title', 'Dashboard - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="p-6">

        {{-- Bagian Greeting (Tidak ada perubahan) --}}
        <div class="mb-6 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg text-white">
                        <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <span class="text-xl mr-2">{{ $greeting['icon'] }}</span>
                            <h1 class="text-xl font-semibold">{{ $greeting['time_greeting'] }}, {{ $greeting['user_name'] }}!</h1>
                        </div>
                        <p class="text-blue-100 text-sm">{{ $greeting['status_message'] }}</p>
                    </div>
                    <div class="hidden md:block text-right text-blue-100">
                        <p class="text-xs">{{ now()->format('l') }}</p>
                        <p class="text-sm font-medium text-white">{{ $greeting['current_date'] }}</p>
                        <p class="text-xs">{{ $greeting['current_time'] }} WIB</p>
                    </div>
                </div>
                @if(Auth::user()->is_gptp_preorder && !Auth::user()->isMembershipActive())
                <div class="mt-4 pt-4 border-t border-blue-400 border-opacity-30">
                    <p class="text-blue-100 text-xs mb-2">Progress Membership GPTP</p>
                    <div class="w-full bg-blue-500 bg-opacity-30 rounded-full h-1.5">
                        <div class="bg-white h-1.5 rounded-full" style="width: {{ Auth::user()->getGPTPProgress() }}%"></div>
                    </div>
                    <p class="text-blue-200 text-xs mt-1">{{ Auth::user()->getRemainingTimeFormatted() }} lagi</p>
                </div>
                @endif
            </div>
            {{-- ... Konten Greeting ... --}}
        </div>

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard SEKAR</h1>
            <p class="text-gray-600 text-sm mt-1">Ringkasan data keanggotaan dan pengurus SEKAR</p>
        </div>

        {{-- Bagian Statistik Card (Tidak ada perubahan) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- ... Konten Card Statistik ... --}}
            {{-- Card Anggota Aktif --}}
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <div>
                    <p class="text-gray-600 text-xs font-medium uppercase tracking-wide">Anggota Aktif</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($anggotaAktif) }}</p>
                    @if($pertumbuhanAnggotaAktif > 0)
                        <p class="flex items-center text-xs text-green-600 font-semibold mt-1">
                            <svg class="w-3 h-3 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.96 9.27a.75.75 0 01-1.06-1.06l4.25-4.25a.75.75 0 011.06 0l4.25 4.25a.75.75 0 11-1.06 1.06L10.75 5.612V16.25A.75.75 0 0110 17z" clip-rule="evenodd" />
                            </svg>
                            <span>+{{ number_format($pertumbuhanAnggotaAktif) }} bulan ini</span>
                        </p>
                    @else
                        <p class="text-xs text-gray-500 mt-1">Anggota terdaftar</p>
                    @endif
                </div>
            </div>

            {{-- Card Pengurus --}}
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <div>
                    <p class="text-gray-600 text-xs font-medium uppercase tracking-wide">Pengurus</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalPengurus) }}</p>
                    @if($pertumbuhanPengurus > 0)
                        <p class="flex items-center text-xs text-green-600 font-semibold mt-1">
                            <svg class="w-3 h-3 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.96 9.27a.75.75 0 01-1.06-1.06l4.25-4.25a.75.75 0 011.06 0l4.25 4.25a.75.75 0 11-1.06 1.06L10.75 5.612V16.25A.75.75 0 0110 17z" clip-rule="evenodd" />
                            </svg>
                            <span>+{{ number_format($pertumbuhanPengurus) }} bulan ini</span>
                        </p>
                    @else
                        <p class="text-xs text-gray-500 mt-1">Total pengurus aktif</p>
                    @endif
                </div>
            </div>

            {{-- Card Anggota Keluar --}}
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <div>
                    <p class="text-gray-600 text-xs font-medium uppercase tracking-wide">Anggota Keluar</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($anggotaKeluar) }}</p>
                    @if($pertumbuhanAnggotaKeluar > 0 && $anggotaKeluar >= $pertumbuhanAnggotaKeluar)
                        <p class="flex items-center text-xs text-red-600 font-semibold mt-1">
                            <svg class="w-3 h-3 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.96 9.27a.75.75 0 01-1.06-1.06l4.25-4.25a.75.75 0 011.06 0l4.25 4.25a.75.75 0 11-1.06 1.06L10.75 5.612V16.25A.75.75 0 0110 17z" clip-rule="evenodd" />
                            </svg>
                            <span>+{{ number_format($pertumbuhanAnggotaKeluar) }} bulan ini</span>
                        </p>
                    @else
                        <p class="text-xs text-gray-500 mt-1">Total keluar</p>
                    @endif
                </div>
            </div>

            {{-- ## Card Non Anggota (DIUBAH) ## --}}
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <div>
                    <p class="text-gray-600 text-xs font-medium uppercase tracking-wide">Non Anggota</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($nonAnggota) }}</p>
                    {{-- Indikator pertumbuhan dihapus sesuai permintaan --}}
                    <p class="text-xs text-gray-500 mt-1">Karyawan non-anggota</p>
                </div>
            </div>
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

                {{-- ### PERUBAHAN DI BLOK INI ### --}}
@if($mappingWithStats->hasPages())
    <div class="mt-6 border-t border-gray-200 px-6 py-4">
        {{-- Menggunakan view paginasi BARU yang sesuai dengan desain dashboard --}}
        {{ $mappingWithStats->appends(request()->query())->links('vendor.pagination.custom-dashboard') }}
    </div>
@endif

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterDPW = document.getElementById('filterDPW');
    const filterDPD = document.getElementById('filterDPD');
    const resetFilter = document.getElementById('resetFilter');
    const mappingRows = document.querySelectorAll('.mapping-row');

    function filterTable() {
        const dpwValue = filterDPW.value.toLowerCase();
        const dpdValue = filterDPD.value.toLowerCase();

        mappingRows.forEach(row => {
            const rowDPW = row.dataset.dpw.toLowerCase();
            const rowDPD = row.dataset.dpd.toLowerCase();

            const dpwMatch = !dpwValue || rowDPW.includes(dpwValue);
            const dpdMatch = !dpdValue || rowDPD.includes(dpdValue);

            if (dpwMatch && dpdMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        let visibleIndex = 1;
        let startNumber = {{ $mappingWithStats->firstItem() }};
        mappingRows.forEach(row => {
            if (row.style.display !== 'none') {
                // Penomoran disesuaikan dengan item paginasi
                row.querySelector('td:first-child').textContent = startNumber + (visibleIndex - 1);
                visibleIndex++;
            }
        });
    }

    function resetFilters() {
        filterDPW.value = '';
        filterDPD.value = '';
        filterTable();
    }

    filterDPW.addEventListener('change', filterTable);
    filterDPD.addEventListener('input', filterTable);
    resetFilter.addEventListener('click', resetFilters);

    // Penyesuaian penomoran pada script filter
    function reIndexTable() {
        const dpwValue = filterDPW.value.toLowerCase();
        const dpdValue = filterDPD.value.toLowerCase();
        let visibleRows = 0;
        const firstItem = {{ $mappingWithStats->firstItem() ?? 1 }};

        mappingRows.forEach(row => {
            const rowDPW = row.dataset.dpw.toLowerCase();
            const rowDPD = row.dataset.dpd.toLowerCase();

            const dpwMatch = !dpwValue || rowDPW.includes(dpwValue);
            const dpdMatch = !dpdValue || rowDPD.includes(dpdValue);

            if (dpwMatch && dpdMatch) {
                row.style.display = '';
                row.cells[0].textContent = firstItem + visibleRows;
                visibleRows++;
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Ganti fungsi filterTable dengan reIndexTable
    filterDPW.addEventListener('change', reIndexTable);
    filterDPD.addEventListener('input', reIndexTable);
    resetFilter.addEventListener('click', () => {
        filterDPW.value = '';
        filterDPD.value = '';
        reIndexTable();
    });

});
</script>
@endsection