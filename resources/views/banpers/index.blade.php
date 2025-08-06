@extends('layouts.app')

@section('title', 'Banpers - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bantuan Perusahaan (Banpers)</h1>
                <p class="text-gray-600 text-sm mt-1">Ringkasan bantuan perusahaan untuk anggota SEKAR tahun {{ $tahun }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('banpers.export') }}" 
                   class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm font-medium flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Unduh Data Banpers
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Total Anggota -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">Total Anggota Aktif</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalAnggotaAktif) }}</p>
                        <p class="text-xs text-gray-500">Anggota yang berhak</p>
                    </div>
                </div>
            </div>

            <!-- Nominal per Orang -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">Nominal per Anggota</h3>
                        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($nominalBanpers, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">Per tahun {{ $tahun }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Banpers -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">Total Banpers</h3>
                        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalBanpers, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">Tahun {{ $tahun }}</p>
                    </div>
                </div>
            </div>
        </div>


        <!-- Breakdown by DPW/DPD -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Rincian Banpers per Wilayah</h3>
                <p class="text-sm text-gray-600 mt-1">Distribusi bantuan perusahaan berdasarkan DPW dan DPD</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">No.</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">DPW</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">DPD</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700 text-xs">Jumlah Anggota</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700 text-xs">Nominal per Orang</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700 text-xs">Total Banpers</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($banpersByWilayah as $index => $wilayah)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 text-xs text-gray-900">{{ $index + 1 }}</td>
                            <td class="py-3 px-4 text-xs">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 font-medium">
                                    {{ $wilayah->dpw }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs text-gray-900">{{ $wilayah->dpd }}</td>
                            <td class="py-3 px-4 text-xs text-gray-900 text-right">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 font-medium">
                                    {{ number_format($wilayah->jumlah_anggota) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs text-gray-900 text-right">
                                Rp {{ number_format($nominalBanpers, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-xs font-bold text-gray-900 text-right">
                                Rp {{ number_format($wilayah->total_banpers, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center text-gray-500 text-sm">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-gray-600 mb-1">Tidak ada data banpers</p>
                                    <p class="text-xs text-gray-500">Belum ada anggota yang terdaftar</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($banpersByWilayah->count() > 0)
                    <tfoot class="bg-gray-50 border-t">
                        <tr class="font-bold">
                            <td colspan="3" class="py-3 px-4 text-xs text-gray-900">TOTAL</td>
                            <td class="py-3 px-4 text-xs text-gray-900 text-right">
                                {{ number_format($totalAnggotaAktif) }}
                            </td>
                            <td class="py-3 px-4 text-xs text-gray-900 text-right">
                                Rp {{ number_format($nominalBanpers, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-xs text-gray-900 text-right">
                                Rp {{ number_format($totalBanpers, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

@endsection