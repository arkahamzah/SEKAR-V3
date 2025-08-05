@extends('layouts.app')

@section('title', 'Dashboard - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Main Content -->
    <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard SEKAR</h1>
            <p class="text-gray-600 text-sm mt-1">Ringkasan data keanggotaan dan pengurus SEKAR</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <!-- Anggota Aktif -->
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-medium uppercase tracking-wide">Anggota Aktif</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($anggotaAktif) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Anggota terdaftar</p>
                    </div>
                    <div class="bg-{{ str_starts_with($growthData['anggota_aktif_growth'], '+') ? 'green' : (str_starts_with($growthData['anggota_aktif_growth'], '-') ? 'red' : 'gray') }}-100 text-{{ str_starts_with($growthData['anggota_aktif_growth'], '+') ? 'green' : (str_starts_with($growthData['anggota_aktif_growth'], '-') ? 'red' : 'gray') }}-700 px-2 py-1 rounded text-xs font-medium">
                        {{ $growthData['anggota_aktif_growth'] }}
                    </div>
                </div>
            </div>

            <!-- Pengurus -->
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-medium uppercase tracking-wide">Pengurus</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalPengurus) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Total pengurus aktif</p>
                    </div>
                    <div class="bg-{{ str_starts_with($growthData['pengurus_growth'], '+') ? 'green' : (str_starts_with($growthData['pengurus_growth'], '-') ? 'red' : 'gray') }}-100 text-{{ str_starts_with($growthData['pengurus_growth'], '+') ? 'green' : (str_starts_with($growthData['pengurus_growth'], '-') ? 'red' : 'gray') }}-700 px-2 py-1 rounded text-xs font-medium">
                        {{ $growthData['pengurus_growth'] }}
                    </div>
                </div>
            </div>

            <!-- Anggota Keluar -->
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-medium uppercase tracking-wide">Anggota Keluar</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($anggotaKeluar) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Ex-anggota</p>
                    </div>
                    <div class="bg-{{ $anggotaKeluar > 0 ? 'yellow' : 'gray' }}-100 text-{{ $anggotaKeluar > 0 ? 'yellow' : 'gray' }}-700 px-2 py-1 rounded text-xs font-medium">
                        {{ $growthData['anggota_keluar_growth'] }}
                    </div>
                </div>
            </div>

            <!-- Non Anggota -->
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-medium uppercase tracking-wide">Non Anggota</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($nonAnggota) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Belum terdaftar</p>
                    </div>
                    <div class="bg-{{ str_starts_with($growthData['non_anggota_growth'], '-') ? 'green' : (str_starts_with($growthData['non_anggota_growth'], '+') ? 'red' : 'gray') }}-100 text-{{ str_starts_with($growthData['non_anggota_growth'], '-') ? 'green' : (str_starts_with($growthData['non_anggota_growth'], '+') ? 'red' : 'gray') }}-700 px-2 py-1 rounded text-xs font-medium">
                        {{ $growthData['non_anggota_growth'] }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-900">Total Karyawan yang Memenuhi Syarat</p>
                    <p class="text-xs text-blue-700">{{ number_format($anggotaAktif + $nonAnggota) }} karyawan (tidak termasuk GPTP)</p>
                </div>
            </div>
        </div>

        <!-- Mapping DPW Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 flex-1 flex flex-col">
            <div class="p-4 border-b border-gray-200 flex-shrink-0">
                <h3 class="text-lg font-semibold text-gray-900">Mapping DPW & Statistik per Wilayah</h3>
                <p class="text-sm text-gray-600 mt-1">Distribusi anggota dan pengurus berdasarkan wilayah kerja</p>
            </div>
            
            <div class="p-4 flex-1 flex flex-col">
                <!-- Filters -->
                <div class="flex space-x-4 mb-4 flex-shrink-0">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Filter DPW:</label>
                        <select id="filterDPW" class="border border-gray-300 rounded px-3 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Semua DPW</option>
                            @foreach($mappingWithStats->pluck('dpw')->unique() as $dpw)
                                <option value="{{ $dpw }}">{{ $dpw }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Cari DPD:</label>
                        <input 
                            type="text" 
                            id="filterDPD"
                            placeholder="Nama DPD..." 
                            class="border border-gray-300 rounded px-3 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                        >
                    </div>
                    <div class="flex items-end">
                        <button id="resetFilter" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded text-xs font-medium transition">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="flex-1 overflow-auto rounded-lg border border-gray-200">
                    <table class="w-full" id="mappingTable">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs border-b">No.</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs border-b">DPW</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs border-b">DPD</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs border-b">Anggota Aktif</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs border-b">Pengurus</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs border-b">Anggota Keluar</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs border-b">Non Anggota</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs border-b">Total Karyawan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($mappingWithStats as $index => $mapping)
                            <tr class="hover:bg-gray-50 transition-colors mapping-row" 
                                data-dpw="{{ $mapping->dpw }}" 
                                data-dpd="{{ $mapping->dpd }}">
                                <td class="py-3 px-4 text-xs text-gray-900">{{ $index + 1 }}</td>
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
                                <td class="py-3 px-4 text-xs text-gray-900">
                                    @if($mapping->anggota_keluar > 0)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">
                                            {{ number_format($mapping->anggota_keluar) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">0</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-900">
                                    @if($mapping->non_anggota > 0)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">
                                            {{ number_format($mapping->non_anggota) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">0</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs font-medium text-gray-900">
                                    {{ number_format($mapping->anggota_aktif + $mapping->non_anggota) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-8 px-4 text-center text-gray-500 text-sm">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <p class="text-gray-600 mb-1">Tidak ada data mapping DPW</p>
                                        <p class="text-xs text-gray-500">Silakan hubungi administrator untuk mengatur mapping wilayah</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($mappingWithStats->count() > 0)
                        <tfoot class="bg-gray-50 border-t">
                            <tr class="font-medium">
                                <td colspan="3" class="py-3 px-4 text-xs text-gray-900">TOTAL</td>
                                <td class="py-3 px-4 text-xs text-gray-900">{{ number_format($mappingWithStats->sum('anggota_aktif')) }}</td>
                                <td class="py-3 px-4 text-xs text-gray-900">{{ number_format($mappingWithStats->sum('pengurus')) }}</td>
                                <td class="py-3 px-4 text-xs text-gray-900">{{ number_format($mappingWithStats->sum('anggota_keluar')) }}</td>
                                <td class="py-3 px-4 text-xs text-gray-900">{{ number_format($mappingWithStats->sum('non_anggota')) }}</td>
                                <td class="py-3 px-4 text-xs text-gray-900">{{ number_format($mappingWithStats->sum('anggota_aktif') + $mappingWithStats->sum('non_anggota')) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
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
        
        // Update row numbers
        let visibleIndex = 1;
        mappingRows.forEach(row => {
            if (row.style.display !== 'none') {
                row.querySelector('td:first-child').textContent = visibleIndex++;
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
});
</script>

<style>
/* Custom scrollbar for table */
.overflow-auto::-webkit-scrollbar {
    height: 6px;
    width: 6px;
}

.overflow-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.overflow-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Hover effects for cards */
.bg-white:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.2s ease;
}
</style>

@endsection