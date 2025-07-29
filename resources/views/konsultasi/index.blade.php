@extends('layouts.app')

@section('title', 'Advokasi & Aspirasi - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                        Semua Advokasi & Aspirasi (Admin)
                    @else
                        Advokasi & Aspirasi Saya
                    @endif
                </h1>
                <p class="text-gray-600 text-sm mt-1">
                    @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                        Kelola semua pengajuan advokasi dan aspirasi anggota SEKAR
                    @else
                        Kelola pengajuan advokasi dan aspirasi Anda
                    @endif
                </p>
            </div>
            <div class="flex space-x-3">
                @if(!auth()->user()->pengurus || !auth()->user()->pengurus->role || !in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                <a href="{{ route('konsultasi.create') }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat Baru
                </a>
                @endif
            </div>
        </div>

        <!-- Admin Statistics -->
        @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Total</p>
                        <p class="text-xl font-bold text-gray-900">{{ $konsultasi->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Menunggu</p>
                        <p class="text-xl font-bold text-gray-900">{{ $konsultasi->where('STATUS', 'OPEN')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Diproses</p>
                        <p class="text-xl font-bold text-gray-900">{{ $konsultasi->where('STATUS', 'IN_PROGRESS')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Selesai</p>
                        <p class="text-xl font-bold text-gray-900">{{ $konsultasi->where('STATUS', 'CLOSED')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Advokasi</p>
                        <p class="text-xl font-bold text-gray-900">{{ $konsultasi->where('JENIS', 'ADVOKASI')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Regular User Statistics -->
        @if($konsultasi->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Total</p>
                        <p class="text-xl font-bold text-gray-900">{{ $konsultasi->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Menunggu</p>
                        <p class="text-xl font-bold text-gray-900">{{ $konsultasi->where('STATUS', 'OPEN')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Diproses</p>
                        <p class="text-xl font-bold text-gray-900">{{ $konsultasi->where('STATUS', 'IN_PROGRESS')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Selesai</p>
                        <p class="text-xl font-bold text-gray-900">{{ $konsultasi->where('STATUS', 'CLOSED')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button onclick="filterByStatus('all')" 
                            class="filter-tab border-b-2 py-4 px-1 text-sm font-medium border-blue-500 text-blue-600" 
                            data-status="all">
                        Semua
                    </button>
                    <button onclick="filterByStatus('OPEN')" 
                            class="filter-tab border-b-2 py-4 px-1 text-sm font-medium border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                            data-status="OPEN">
                        Menunggu
                    </button>
                    <button onclick="filterByStatus('IN_PROGRESS')" 
                            class="filter-tab border-b-2 py-4 px-1 text-sm font-medium border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                            data-status="IN_PROGRESS">
                        Diproses
                    </button>
                    <button onclick="filterByStatus('CLOSED')" 
                            class="filter-tab border-b-2 py-4 px-1 text-sm font-medium border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                            data-status="CLOSED">
                        Selesai
                    </button>
                    @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                    <button onclick="filterByJenis('ADVOKASI')" 
                            class="filter-tab border-b-2 py-4 px-1 text-sm font-medium border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                            data-jenis="ADVOKASI">
                        Advokasi
                    </button>
                    <button onclick="filterByJenis('ASPIRASI')" 
                            class="filter-tab border-b-2 py-4 px-1 text-sm font-medium border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                            data-jenis="ASPIRASI">
                        Aspirasi
                    </button>
                    @endif
                </nav>
            </div>

            <!-- Search -->
            <div class="p-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           id="searchInput"
                           placeholder="Cari berdasarkan judul, deskripsi, atau nama anggota..." 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                           onkeyup="searchKonsultasi()">
                </div>
            </div>
        </div>

        <!-- List Advokasi & Aspirasi -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div id="konsultasiList">
                @forelse($konsultasi as $item)
                <div class="konsultasi-item border-b border-gray-200 last:border-b-0 p-6 hover:bg-gray-50 transition" 
                     data-status="{{ $item->STATUS }}"
                     data-jenis="{{ $item->JENIS }}"
                     data-title="{{ strtolower($item->JUDUL) }}"
                     data-description="{{ strtolower($item->DESKRIPSI) }}"
                     data-nama="{{ $item->karyawan ? strtolower($item->karyawan->V_NAMA_KARYAWAN) : '' }}">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    {{ $item->JENIS === 'ADVOKASI' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $item->JENIS }}
                                </span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $item->status_color }}-100 text-{{ $item->status_color }}-700">
                                    {{ $item->status_text }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $item->TUJUAN }} {{ $item->TUJUAN_SPESIFIK ? '- ' . $item->TUJUAN_SPESIFIK : '' }}
                                </span>
                                @if($item->KATEGORI_ADVOKASI)
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">
                                        {{ $item->KATEGORI_ADVOKASI }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $item->JUDUL }}</h3>
                            <p class="text-gray-600 text-sm mb-2">{{ Str::limit($item->DESKRIPSI, 150) }}</p>
                            
                            <!-- Admin: Show member info -->
                            @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                                @if($item->karyawan)
                                <div class="flex items-center text-xs text-blue-600 mb-2 bg-blue-50 p-2 rounded">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <strong>{{ $item->karyawan->V_NAMA_KARYAWAN }}</strong>
                                    <span class="mx-2">•</span>
                                    <span>NIK: {{ $item->N_NIK }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ $item->karyawan->V_SHORT_UNIT }} - {{ $item->karyawan->V_KOTA_GEDUNG }}</span>
                                </div>
                                @endif
                            @endif
                            
                            <div class="flex items-center text-xs text-gray-500 space-x-4">
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $item->CREATED_AT->format('d M Y, H:i') }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    {{ $item->komentar->count() }} komentar
                                </span>
                                @if($item->UPDATED_AT && $item->UPDATED_AT != $item->CREATED_AT)
                                    <span class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Update: {{ $item->UPDATED_AT->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="ml-4 flex flex-col space-y-2">
                            <a href="{{ route('konsultasi.show', $item->ID) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Lihat Detail
                            </a>
                            @if($item->STATUS !== 'CLOSED')
                                <span class="text-xs text-green-600 font-medium">● Aktif</span>
                            @else
                                <span class="text-xs text-gray-500">● Ditutup</span>
                            @endif
                            
                            <!-- Priority indicator for admin -->
                            @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                                @if($item->JENIS === 'ADVOKASI' && $item->STATUS !== 'CLOSED')
                                    <span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full font-medium">
                                        ⚠️ Prioritas Tinggi
                                    </span>
                                @endif
                                @if($item->CREATED_AT->diffInDays(now()) > 7 && $item->STATUS !== 'CLOSED')
                                    <span class="text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded-full font-medium">
                                        📅 {{ $item->CREATED_AT->diffInDays(now()) }} hari
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                            Belum ada advokasi & aspirasi
                        @else
                            Belum ada advokasi & aspirasi
                        @endif
                    </h3>
                    <p class="text-gray-600 mb-4">
                        @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                            Belum ada anggota yang mengajukan advokasi atau aspirasi.
                        @else
                            Mulai dengan membuat advokasi atau aspirasi pertama Anda.
                        @endif
                    </p>
                    @if(!auth()->user()->pengurus || !auth()->user()->pengurus->role || !in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                    <a href="{{ route('konsultasi.create') }}" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                        Buat Advokasi/Aspirasi
                    </a>
                    @endif
                </div>
                @endforelse
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="p-12 text-center hidden">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada hasil ditemukan</h3>
                <p class="text-gray-600">Coba ubah filter atau kata kunci pencarian Anda.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize filter
    filterByStatus('all');
});

function filterByStatus(status) {
    const items = document.querySelectorAll('.konsultasi-item');
    const tabs = document.querySelectorAll('.filter-tab');
    
    // Update tab styles
    tabs.forEach(tab => {
        tab.classList.remove('border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    const activeTab = document.querySelector(`[data-status="${status}"]`);
    if (activeTab) {
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        activeTab.classList.add('border-blue-500', 'text-blue-600');
    }
    
    // Filter items
    let visibleCount = 0;
    items.forEach(item => {
        if (status === 'all' || item.dataset.status === status) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    toggleNoResults(visibleCount === 0);
    
    // Clear search when filtering
    document.getElementById('searchInput').value = '';
}

function filterByJenis(jenis) {
    const items = document.querySelectorAll('.konsultasi-item');
    const tabs = document.querySelectorAll('.filter-tab');
    
    // Update tab styles
    tabs.forEach(tab => {
        tab.classList.remove('border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    const activeTab = document.querySelector(`[data-jenis="${jenis}"]`);
    if (activeTab) {
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        activeTab.classList.add('border-blue-500', 'text-blue-600');
    }
    
    // Filter items
    let visibleCount = 0;
    items.forEach(item => {
        if (item.dataset.jenis === jenis) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    toggleNoResults(visibleCount === 0);
    
    // Clear search when filtering
    document.getElementById('searchInput').value = '';
}

function searchKonsultasi() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const items = document.querySelectorAll('.konsultasi-item');
    let visibleCount = 0;
    
    items.forEach(item => {
        const title = item.dataset.title;
        const description = item.dataset.description;
        const nama = item.dataset.nama;
        
        if (title.includes(searchTerm) || description.includes(searchTerm) || nama.includes(searchTerm)) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    toggleNoResults(visibleCount === 0);
}

function toggleNoResults(show) {
    const noResults = document.getElementById('noResults');
    const konsultasiList = document.getElementById('konsultasiList');
    
    if (show) {
        noResults.classList.remove('hidden');
        konsultasiList.classList.add('hidden');
    } else {
        noResults.classList.add('hidden');
        konsultasiList.classList.remove('hidden');
    }
}
</script>

<style>
/* Hover effects */
.konsultasi-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* Filter tabs transition */
.filter-tab {
    transition: all 0.2s ease;
}

/* Search input focus effect */
#searchInput:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

/* Status indicators animation */
.konsultasi-item span {
    transition: all 0.2s ease;
}

.konsultasi-item:hover span {
    transform: scale(1.05);
}
</style>
@endsection