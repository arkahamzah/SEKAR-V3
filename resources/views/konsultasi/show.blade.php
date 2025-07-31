@extends('layouts.app')

@section('title', 'Detail ' . ucfirst(strtolower($konsultasi->JENIS)))

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('konsultasi.index') }}" 
                           class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Kembali
                        </a>
                        <div class="h-6 w-px bg-gray-300"></div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Detail {{ ucfirst(strtolower($konsultasi->JENIS)) }}
                        </h1>
                    </div>
                    
                    <!-- Status Badge -->
                    <div class="flex items-center space-x-3">
                        @php
                            $statusColors = [
                                'OPEN' => 'bg-blue-100 text-blue-800',
                                'IN_PROGRESS' => 'bg-yellow-100 text-yellow-800',
                                'CLOSED' => 'bg-gray-100 text-gray-800',
                                'RESOLVED' => 'bg-green-100 text-green-800'
                            ];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$konsultasi->STATUS] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $konsultasi->STATUS }}
                        </span>
                        
                        @if($konsultasi->JENIS === 'ADVOKASI')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                Advokasi
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                Aspirasi
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Konsultasi Detail -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex-1">
                                <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $konsultasi->JUDUL }}</h2>
                                <div class="flex flex-wrap items-center text-sm text-gray-500 space-x-4">
                                    <span>{{ $konsultasi->TUJUAN }}{{ $konsultasi->TUJUAN_SPESIFIK ? ' - ' . $konsultasi->TUJUAN_SPESIFIK : '' }}</span>
                                    <span>{{ $konsultasi->CREATED_AT->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                            
                            <!-- Admin Actions -->
                            @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                                @php
                                    // Tentukan opsi eskalasi berdasarkan level saat ini
                                    $currentLevel = $konsultasi->TUJUAN;
                                    $escalationOptions = [];
                                    
                                    switch($currentLevel) {
                                        case 'DPD':
                                            $escalationOptions = [
                                                'DPW' => 'DPW (Dewan Pengurus Wilayah)',
                                                'DPP' => 'DPP (Dewan Pengurus Pusat)',
                                                'GENERAL' => 'SEKAR Pusat'
                                            ];
                                            break;
                                        case 'DPW':
                                            $escalationOptions = [
                                                'DPP' => 'DPP (Dewan Pengurus Pusat)',
                                                'GENERAL' => 'SEKAR Pusat'
                                            ];
                                            break;
                                        case 'DPP':
                                            $escalationOptions = [
                                                'GENERAL' => 'SEKAR Pusat'
                                            ];
                                            break;
                                        case 'GENERAL':
                                            $escalationOptions = [];
                                            break;
                                        default:
                                            $escalationOptions = [
                                                'DPW' => 'DPW (Dewan Pengurus Wilayah)',
                                                'DPP' => 'DPP (Dewan Pengurus Pusat)',
                                                'GENERAL' => 'SEKAR Pusat'
                                            ];
                                    }
                                    
                                    $canEscalate = !empty($escalationOptions);
                                @endphp
                                
                                <div class="ml-4 space-y-2">
                                    @if($konsultasi->STATUS !== 'CLOSED')
                                        <!-- Close Button -->
                                        <form id="closeForm" method="POST" action="{{ route('konsultasi.close', $konsultasi->ID) }}" class="inline">
                                            @csrf
                                            <button type="button" 
                                                    onclick="showCloseConfirmation('{{ strtolower($konsultasi->JENIS) }}')"
                                                    class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Tutup {{ ucfirst(strtolower($konsultasi->JENIS)) }}
                                            </button>
                                        </form>
                                        
                                        <!-- Escalate Button -->
                                        <button type="button" 
                                                onclick="openEscalateModal()"
                                                class="inline-flex items-center px-3 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition-colors ml-2 {{ !$canEscalate ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !$canEscalate ? 'disabled' : '' }}>
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            </svg>
                                            Eskalasi
                                            @if(!$canEscalate)
                                                <span class="ml-1 text-xs">(Max Level)</span>
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="prose max-w-none">
                            <p class="text-gray-700 whitespace-pre-line">{{ $konsultasi->DESKRIPSI }}</p>
                        </div>

                        @if($konsultasi->karyawan)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $konsultasi->karyawan->V_NAMA_KARYAWAN }}</p>
                                    <p class="text-sm text-gray-500">NIK: {{ $konsultasi->N_NIK }}</p>
                                    @if($konsultasi->karyawan->V_KOTA_GEDUNG)
                                        <p class="text-sm text-gray-500">{{ $konsultasi->karyawan->V_KOTA_GEDUNG }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Komentar & Tanggapan</h3>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        @forelse($konsultasi->komentar as $komentar)
                        <div class="flex space-x-3 {{ $komentar->JENIS_KOMENTAR === 'ADMIN' ? 'bg-blue-50 -m-6 p-6' : '' }}">
                            <div class="flex-shrink-0">
                                @if($komentar->JENIS_KOMENTAR === 'ADMIN')
                                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7-7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-sm font-medium text-gray-900">
                                        @if($komentar->JENIS_KOMENTAR === 'ADMIN')
                                            Admin SEKAR
                                        @else
                                            {{ $komentar->karyawan->V_NAMA_KARYAWAN ?? 'User' }}
                                        @endif
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $komentar->CREATED_AT->diffForHumans() }}</span>
                                    @if($komentar->JENIS_KOMENTAR === 'ADMIN')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            Admin
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $komentar->KOMENTAR }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.13 8.13 0 01-2.939-.542l-2.122 2.122A.5.5 0 017 21V8a8 8 0 1114 4z"></path>
                            </svg>
                            <p class="text-gray-500">Belum ada komentar atau tanggapan</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Add Comment Form -->
                @if($konsultasi->STATUS !== 'CLOSED')
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Tambah Komentar</h3>
                    </div>
                    
                    <div class="p-6">
                        @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                            <!-- Admin Comment Form -->
                            <form method="POST" action="{{ route('konsultasi.comment', $konsultasi->ID) }}">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggapan Admin</label>
                                    <textarea name="komentar" rows="4" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('komentar') border-red-300 @enderror"
                                              placeholder="Berikan tanggapan atau solusi..." required>{{ old('komentar') }}</textarea>
                                    @error('komentar')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Kirim Tanggapan
                                </button>
                            </form>
                        @elseif(auth()->user()->nik === $konsultasi->N_NIK)
                            <!-- User Comment Form -->
                            <form method="POST" action="{{ route('konsultasi.comment', $konsultasi->ID) }}">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tambah Komentar</label>
                                    <textarea name="komentar" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('komentar') border-red-300 @enderror"
                                              placeholder="Tambahkan informasi tambahan atau pertanyaan..." required>{{ old('komentar') }}</textarea>
                                    @error('komentar')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Kirim Komentar
                                </button>
                            </form>
                        @else
                            <div class="text-center py-4 bg-gray-50 rounded-lg">
                                <p class="text-gray-500 text-sm">Tidak dapat menambahkan komentar baru.</p>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Information Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID Konsultasi</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $konsultasi->ID }}</dd>
                        </div>
                        
                        @if($konsultasi->KATEGORI_ADVOKASI)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Kategori</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $konsultasi->KATEGORI_ADVOKASI }}</dd>
                        </div>
                        @endif
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dibuat</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $konsultasi->CREATED_AT->format('d F Y, H:i') }}</dd>
                        </div>
                        
                        @if($konsultasi->UPDATED_AT && $konsultasi->UPDATED_AT != $konsultasi->CREATED_AT)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Terakhir Diperbarui</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $konsultasi->UPDATED_AT->format('d F Y, H:i') }}</dd>
                        </div>
                        @endif
                        
                        @if($konsultasi->CLOSED_AT)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Ditutup</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($konsultasi->CLOSED_AT)->format('d F Y, H:i') }}</dd>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Close Confirmation Modal -->
<div id="closeConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[100] hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="p-6">
            <!-- Header dengan Icon -->
            <div class="flex items-start space-x-4">
                <!-- Icon Circle -->
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-red-100">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="flex-1">
                    <!-- Title -->
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Konfirmasi Penutupan
                    </h3>
                    
                    <!-- Message -->
                    <div class="text-gray-600 text-sm">
                        <p id="closeMessage">Apakah Anda yakin ingin menutup kasus ini?</p>
                        <p class="mt-2 text-xs text-gray-500">Setelah ditutup, kasus tidak dapat dibuka kembali dan tidak dapat menambahkan komentar baru.</p>
                    </div>
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closeCloseConfirmModal()" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Batal
                </button>
                <button onclick="confirmClose()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Ya, Tutup Kasus
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Escalation Modal - PERBAIKAN DROPDOWN -->
@if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
<div id="escalateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[100] hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Eskalasi {{ ucfirst(strtolower($konsultasi->JENIS)) }}</h3>
            
            @if($canEscalate)
                <form id="escalateForm" method="POST" action="{{ route('konsultasi.escalate', $konsultasi->ID) }}">
                    @csrf
                    
                    <!-- DROPDOWN YANG DIPERBAIKI - TIDAK DOUBLE LAGI -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Eskalasi ke Level</label>
                        <div class="relative">
                            <select name="escalate_to" 
                                    class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white" 
                                    required>
                                <option value="">Pilih Level</option>
                                @foreach($escalationOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <!-- Custom dropdown arrow -->
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Level saat ini: <strong>{{ $konsultasi->TUJUAN }}</strong>
                        </p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Eskalasi <span class="text-red-500">*</span></label>
                        <textarea name="escalation_note" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Jelaskan alasan eskalasi..." required></textarea>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg mb-6">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-yellow-800">
                                    <strong>Perhatian:</strong> Eskalasi akan mengirimkan {{ strtolower($konsultasi->JENIS) }} ini ke admin level yang lebih tinggi untuk penanganan lebih lanjut.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="closeEscalateModal()"
                                class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Batal
                        </button>
                        <button type="button" 
                                onclick="confirmEscalate()"
                                class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                            Eskalasi
                        </button>
                    </div>
                </form>
            @else
                <div class="text-center py-6">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Tidak Dapat Dieskalasi</h4>
                    <p class="text-sm text-gray-600 mb-6">
                        Konsultasi ini sudah berada di level tertinggi (<strong>{{ $konsultasi->TUJUAN }}</strong>) dan tidak dapat dieskalasi lebih lanjut.
                    </p>
                    <button type="button" 
                            onclick="closeEscalateModal()"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        Tutup
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const escalateModal = document.getElementById('escalateModal');
    const escalateForm = document.getElementById('escalateForm');
    const escalateSelect = escalateModal?.querySelector('select[name="escalate_to"]');
    const escalateTextarea = escalateModal?.querySelector('textarea[name="escalation_note"]');
    const closeConfirmModal = document.getElementById('closeConfirmModal');

    // Function to show close confirmation
    window.showCloseConfirmation = function(jenisKonsultasi) {
        const message = document.getElementById('closeMessage');
        message.textContent = `Apakah Anda yakin ingin menutup ${jenisKonsultasi} ini?`;
        closeConfirmModal.classList.remove('hidden');
    };

    // Function to close close confirmation modal
    window.closeCloseConfirmModal = function() {
        closeConfirmModal.classList.add('hidden');
    };

    // Function to confirm close
    window.confirmClose = function() {
        document.getElementById('closeForm').submit();
    };

    // Function to open escalate modal
    window.openEscalateModal = function() {
        if (escalateModal) {
            escalateModal.classList.remove('hidden');
            if (escalateSelect) {
                setTimeout(() => escalateSelect.focus(), 100);
            }
        }
    };

    // Function to close escalate modal
    window.closeEscalateModal = function() {
        if (escalateModal) {
            escalateModal.classList.add('hidden');
            if (escalateForm) {
                escalateForm.reset();
            }
        }
    };

    // Function to confirm escalation
    window.confirmEscalate = function() {
        const selectedLevel = escalateSelect?.value;
        const note = escalateTextarea?.value?.trim();

        if (!selectedLevel) {
            alert('Harap pilih level tujuan eskalasi.');
            escalateSelect?.focus();
            return;
        }

        if (!note || note.length < 10) {
            alert('Harap isi catatan eskalasi minimal 10 karakter.');
            escalateTextarea?.focus();
            return;
        }

        const levelName = escalateSelect.options[escalateSelect.selectedIndex].text;
        const confirmMessage = `Apakah Anda yakin ingin mengekskalasi konsultasi ini ke ${levelName}?\n\nCatatan: ${note}`;
        
        if (confirm(confirmMessage)) {
            escalateForm.submit();
        }
    };

    // Close modals when clicking outside
    [closeConfirmModal, escalateModal].forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    if (modal === closeConfirmModal) {
                        closeCloseConfirmModal();
                    } else {
                        closeEscalateModal();
                    }
                }
            });
        }
    });

    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (closeConfirmModal && !closeConfirmModal.classList.contains('hidden')) {
                closeCloseConfirmModal();
            }
            if (escalateModal && !escalateModal.classList.contains('hidden')) {
                closeEscalateModal();
            }
        }
    });

    // Character counter for textarea
    if (escalateTextarea) {
        const maxLength = 500;
        
        const counter = document.createElement('div');
        counter.className = 'text-xs text-gray-500 text-right mt-1';
        counter.innerHTML = `0/${maxLength}`;
        escalateTextarea.parentNode.appendChild(counter);

        escalateTextarea.addEventListener('input', function() {
            const length = this.value.length;
            counter.innerHTML = `${length}/${maxLength}`;
            
            if (length > maxLength * 0.9) {
                counter.classList.add('text-red-600');
                counter.classList.remove('text-gray-500');
            } else {
                counter.classList.add('text-gray-500');
                counter.classList.remove('text-red-600');
            }
        });
    }

    // Auto-resize textarea
    if (escalateTextarea) {
        escalateTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
});
</script>

<style>
/* STYLING YANG DIPERBAIKI - MENGHILANGKAN DUPLIKASI DROPDOWN */
select[name="escalate_to"] {
    /* Hapus appearance default browser dan background image */
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background: white;
}

/* Styling untuk opsi yang disabled */
select[name="escalate_to"] option:disabled {
    color: #9ca3af;
    font-style: italic;
}

/* Responsive untuk mobile */
@media (max-width: 640px) {
    select[name="escalate_to"] {
        font-size: 14px;
        padding: 0.5rem 2.5rem 0.5rem 0.75rem;
    }
}

/* Smooth modal transitions */
#escalateModal {
    transition: all 0.3s ease;
}

#escalateModal.hidden {
    opacity: 0;
    pointer-events: none;
}

#escalateModal:not(.hidden) {
    opacity: 1;
    pointer-events: auto;
}

/* Focus states untuk accessibility */
select[name="escalate_to"]:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    border-color: #3b82f6;
}

textarea[name="escalation_note"]:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    border-color: #3b82f6;
}
</style>
@endsection