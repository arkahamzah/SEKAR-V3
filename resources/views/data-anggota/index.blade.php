@extends('layouts.app')

@section('title', 'Data Anggota - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-none px-4 sm:px-6 lg:px-8 py-6">
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

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Data Anggota SEKAR</h1>
            <div class="flex space-x-3">
                <button id="exportBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm font-medium flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Unduh Data Anggota
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <a href="{{ route('data-anggota.index', ['tab' => 'anggota']) }}" 
                       class="border-b-2 py-4 px-1 text-sm font-medium {{ $activeTab === 'anggota' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Data Anggota
                    </a>
                    <a href="{{ route('data-anggota.index', ['tab' => 'gptp']) }}" 
                       class="border-b-2 py-4 px-1 text-sm font-medium {{ $activeTab === 'gptp' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Data Anggota GPTP
                    </a>
                    <a href="{{ route('data-anggota.index', ['tab' => 'pengurus']) }}" 
                       class="border-b-2 py-4 px-1 text-sm font-medium {{ $activeTab === 'pengurus' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Data Pengurus
                    </a>
                    <a href="{{ route('data-anggota.index', ['tab' => 'ex-anggota']) }}" 
                       class="border-b-2 py-4 px-1 text-sm font-medium {{ $activeTab === 'ex-anggota' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Ex Anggota
                    </a>
                </nav>
            </div>

            <div class="p-6 border-b border-gray-200">
                <form method="GET" action="{{ route('data-anggota.index') }}" class="space-y-4">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @if(in_array($activeTab, ['anggota', 'pengurus', 'ex-anggota']))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">DPW</label>
                            <select name="dpw" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                @foreach($dpwOptions as $dpw)
                                    <option value="{{ $dpw }}" {{ request('dpw') === $dpw ? 'selected' : '' }}>
                                        {{ $dpw }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">DPD</label>
                            <select name="dpd" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                @foreach($dpdOptions as $dpd)
                                    <option value="{{ $dpd }}" {{ request('dpd') === $dpd ? 'selected' : '' }}>
                                        {{ $dpd }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="{{ in_array($activeTab, ['anggota', 'pengurus', 'ex-anggota']) ? 'md:col-span-1' : 'md:col-span-3' }}">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cari berdasarkan nama</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Masukkan nama atau NIK" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                                Filter
                            </button>
                        </div>
                    </div>

                    @if(request()->hasAny(['dpw', 'dpd', 'search']))
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            @if(request('search'))
                                Hasil pencarian: "{{ request('search') }}"
                            @endif
                            @if(request('dpw') && request('dpw') !== 'Semua DPW')
                                • DPW: {{ request('dpw') }}
                            @endif
                            @if(request('dpd') && request('dpd') !== 'Semua DPD')
                                • DPD: {{ request('dpd') }}
                            @endif
                        </div>
                        <a href="{{ route('data-anggota.index', ['tab' => $activeTab]) }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm">
                            Reset Filter
                        </a>
                    </div>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                @if($activeTab === 'anggota')
                    @include('data-anggota.partials.anggota-table')
                @elseif($activeTab === 'gptp')
                    @include('data-anggota.partials.gptp-table')
                @elseif($activeTab === 'pengurus')
                    @include('data-anggota.partials.pengurus-table')
                @elseif($activeTab === 'ex-anggota')
                    @include('data-anggota.partials.ex-anggota-table')
                @endif
            </div>
        </div>
    </div>
</div>

<div id="exportModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Data</h3>
        
        <form id="exportForm" action="{{ route('data-anggota.export') }}" method="GET">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <input type="hidden" name="dpw" value="{{ request('dpw') }}">
            <input type="hidden" name="dpd" value="{{ request('dpd') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Data</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="anggota" {{ $activeTab === 'anggota' ? 'selected' : '' }}>Data Anggota</option>
                    <option value="gptp" {{ $activeTab === 'gptp' ? 'selected' : '' }}>Data Anggota GPTP</option>
                    <option value="pengurus" {{ $activeTab === 'pengurus' ? 'selected' : '' }}>Data Pengurus</option>
                    <option value="ex-anggota" {{ $activeTab === 'ex-anggota' ? 'selected' : '' }}>Ex Anggota</option>
                </select>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                <select name="format" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="csv">CSV</option>
                </select>
            </div>
            
            <div class="flex space-x-3">
                <button type="button" id="cancelExportBtn" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                    Download
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('exportBtn');
    const exportModal = document.getElementById('exportModal');
    const cancelExportBtn = document.getElementById('cancelExportBtn');
    
    exportBtn.addEventListener('click', function() {
        exportModal.classList.remove('hidden');
    });
    
    cancelExportBtn.addEventListener('click', function() {
        exportModal.classList.add('hidden');
    });
    
    exportModal.addEventListener('click', function(e) {
        if (e.target === exportModal) {
            exportModal.classList.add('hidden');
        }
    });
});
</script>

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