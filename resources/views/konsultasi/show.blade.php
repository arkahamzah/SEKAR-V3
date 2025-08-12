@extends('layouts.app')

@section('title', 'Detail Konsultasi')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header with back navigation -->
        <div class="mb-6">
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                <a href="{{ route('konsultasi.index') }}" class="hover:text-gray-700">Konsultasi</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-gray-900">Detail</span>
            </nav>
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Konsultasi</h1>
                    <p class="text-gray-600">ID: {{ $konsultasi->ID }}</p>
                </div>
                
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $konsultasi->STATUS === 'OPEN' ? 'bg-yellow-100 text-yellow-800' : ($konsultasi->STATUS === 'IN_PROGRESS' ? 'bg-blue-100 text-blue-800' : ($konsultasi->STATUS === 'CLOSED' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
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
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Deskripsi</h3>
                            <div class="prose prose-sm max-w-none text-gray-700">
                                {!! nl2br(e($konsultasi->DESKRIPSI)) !!}
                            </div>
                        </div>

                        <!-- Files -->
                        @if($konsultasi->FILES)
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">File Lampiran</h3>
                            @php
                                $files = json_decode($konsultasi->FILES, true);
                            @endphp
                            @if($files && is_array($files))
                                <div class="space-y-2">
                                    @foreach($files as $file)
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg border">
                                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-sm text-gray-700 flex-1">{{ $file }}</span>
                                        <a href="{{ route('konsultasi.download', ['id' => $konsultasi->ID, 'file' => $file]) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm">
                                            Download
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Combined Admin Actions Section -->
                @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']) && $konsultasi->STATUS !== 'CLOSED')
                    
                    <!-- Escalation Warning (if available) -->
                    @if(!empty($escalationOptions))
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium text-yellow-800">Opsi Eskalasi Tersedia</h3>
                                <p class="mt-1 text-sm text-yellow-700">Konsultasi ini dapat dieskalasi ke level yang lebih tinggi jika diperlukan.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Combined Admin Actions Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Aksi Admin</h3>
                            <p class="mt-1 text-sm text-gray-600">Pilih tindakan yang ingin dilakukan untuk konsultasi ini</p>
                        </div>
                        
                        <div class="p-6">
                            <div class="flex flex-col sm:flex-row gap-3">
                                
                                <!-- Escalation Button -->
                                @if(!empty($escalationOptions))
                                <button onclick="openEscalationModal()" 
                                        class="inline-flex items-center justify-center px-4 py-2 border border-yellow-600 text-sm font-medium rounded-lg text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                    Eskalasi Konsultasi
                                </button>
                                @endif

                                <!-- Close Button -->
                                <button type="button" onclick="showCloseConfirmModal('{{ $konsultasi->ID }}', '{{ addslashes($konsultasi->JUDUL) }}')"
                                        class="inline-flex items-center justify-center px-4 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Tutup Konsultasi
                                </button>
                                
                            </div>

                            <!-- Admin Guidelines (only show if escalation available) -->
                           @if(!empty($escalationOptions))
                            <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-800 mb-2">Panduan Eskalasi Bertahap</h4>
                                @php
                                    $userRole = auth()->user()->pengurus->role->NAME ?? null;
                                    $userDPW = auth()->user()->pengurus->DPW ?? null;
                                    $userDPD = auth()->user()->pengurus->DPD ?? null;
                                @endphp
                                
                                @if($userRole === 'ADMIN_DPW')
                                <div class="space-y-1 text-xs">
                                    <p><strong>Sebagai Admin DPW ({{ $userDPW }}):</strong></p>
                                    <p>• Dapat eskalasi DPD di wilayah sendiri ke DPD lain atau ke DPW sendiri</p>
                                    <p>• Dapat eskalasi DPW sendiri ke DPW lain atau ke DPP</p>
                                    <p>• Dapat eskalasi balik ke DPD di wilayah sendiri</p>
                                </div>
                                @elseif($userRole === 'ADMIN_DPD')
                                <div class="space-y-1 text-xs">
                                    <p><strong>Sebagai Admin DPD ({{ $userDPD }}):</strong></p>
                                    <p class="text-green-700 font-medium">• Dapat eskalasi ke DPD lain di wilayah DPW yang sama</p>
                                    <p>• Dapat eskalasi ke DPW ({{ $userDPW }})</p>
                                </div>
                                @endif
                                
                                <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-xs">
                                    <p class="font-medium text-blue-800">Hierarki Eskalasi:</p>
                                    <p class="text-blue-700">DPD → DPW → DPP</p>
                                    <p class="text-blue-600 text-[10px] mt-1">* Eskalasi lateral (ke level yang sama) diperbolehkan dalam wilayah yang sesuai</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Comments Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Komentar & Tanggapan</h3>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        @forelse($konsultasi->komentar as $komentar)
                        <div class="flex space-x-3 {{ $komentar->PENGIRIM_ROLE === 'ADMIN' ? 'bg-blue-50 p-4 rounded-lg' : '' }}">
                            <div class="flex-shrink-0">
                                @if($komentar->PENGIRIM_ROLE === 'ADMIN')
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                @else
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                @endif
                            </div>
                            
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-sm font-medium text-gray-900">
                                        @if($komentar->PENGIRIM_ROLE === 'ADMIN')
                                            @if($komentar->user && $komentar->user->pengurus)
                                                {{ $komentar->user->pengurus->role->NAME ?? 'Admin' }}
                                            @else
                                                Admin
                                            @endif
                                        @else
                                            {{ $komentar->user->name ?? 'User' }}
                                        @endif
                                    </span>
                                    @if($komentar->PENGIRIM_ROLE === 'ADMIN')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Tanggapan Resmi
                                    </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-700 mb-2">{{ $komentar->KOMENTAR }}</p>
                                <p class="text-xs text-gray-500">{{ $komentar->CREATED_AT->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <p class="text-sm">Belum ada komentar</p>
                        </div>
                        @endforelse
                    </div>

                    @if($konsultasi->STATUS !== 'CLOSED')
                    <!-- Add Comment Section -->
                    <div class="border-t border-gray-200 p-6">
                        @if(auth()->user()->nik === $konsultasi->N_NIK || (auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD'])))
                            <form action="{{ route('konsultasi.comment', $konsultasi->ID) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="komentar" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tambahkan Komentar
                                    </label>
                                    <textarea name="komentar" id="komentar" rows="3" 
                                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                              placeholder="Tulis komentar atau tanggapan Anda..." required></textarea>
                                    @error('komentar')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
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
                    @endif
                </div>
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
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Pengaju</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($konsultasi->karyawan)
                                    {{ $konsultasi->karyawan->V_NAMA_KARYAWAN }}
                                @else
                                    {{ $konsultasi->N_NIK }}
                                @endif
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Timeline Card -->
                @if($konsultasi->komentar->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Timeline</h3>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <!-- Creation event -->
                                <li>
                                    <div class="relative pb-8">
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div>
                                                    <p class="text-sm text-gray-900 font-medium">Konsultasi dibuat</p>
                                                    <p class="text-sm text-gray-500">{{ $konsultasi->CREATED_AT->format('d M Y, H:i') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <!-- Comments -->
                                @foreach($konsultasi->komentar as $index => $komentar)
                                <li>
                                    <div class="relative {{ $index < $konsultasi->komentar->count() - 1 ? 'pb-8' : '' }}">
                                        @if($index < $konsultasi->komentar->count() - 1)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div class="flex-shrink-0">
                                                @if($komentar->PENGIRIM_ROLE === 'ADMIN')
                                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                </div>
                                                @else
                                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div>
                                                    <p class="text-sm text-gray-900 font-medium">
                                                        @if($komentar->PENGIRIM_ROLE === 'ADMIN')
                                                            Tanggapan Admin
                                                        @else
                                                            Komentar User
                                                        @endif
                                                    </p>
                                                    <p class="text-sm text-gray-500">{{ $komentar->CREATED_AT->format('d M Y, H:i') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Smart Escalation Modal -->
@if(!empty($escalationOptions))
<div id="escalationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-bold text-gray-900">Eskalasi Konsultasi</h3>
            <button onclick="closeEscalationModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="{{ route('konsultasi.escalate', $konsultasi->ID) }}" method="POST" class="mt-4">
            @csrf
            
            <!-- Current Status Info -->
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="text-sm font-medium text-blue-800 mb-2">Status Saat Ini</h4>
                <div class="text-sm text-blue-700">
                    <p><strong>Tujuan:</strong> {{ $konsultasi->TUJUAN }}</p>
                    @if($konsultasi->TUJUAN_SPESIFIK)
                    <p><strong>Spesifik:</strong> {{ $konsultasi->TUJUAN_SPESIFIK }}</p>
                    @endif
                </div>
            </div>

            <!-- Escalation Target Selection -->
            <div class="mb-4">
                <label for="escalate_to" class="block text-sm font-medium text-gray-700 mb-2">
                    Eskalasi ke <span class="text-red-500">*</span>
                </label>
                <select id="escalate_to" name="escalate_to" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        onchange="updateSpecificOptions()">
                    <option value="">-- Pilih Tujuan Eskalasi --</option>
                    @foreach($escalationOptions as $level => $options)
                    <option value="{{ $level }}" data-options="{{ json_encode($options['specific_options']) }}">
                        {{ $options['label'] }}
                    </option>
                    @endforeach
                </select>
                @error('escalate_to')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Specific Target Selection -->
            <div id="specific_selection" class="mb-4 hidden">
                <label for="escalate_to_specific" class="block text-sm font-medium text-gray-700 mb-2">
                    Tujuan Spesifik <span class="text-red-500">*</span>
                </label>
                <select id="escalate_to_specific" name="escalate_to_specific"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">-- Pilih Tujuan Spesifik --</option>
                </select>
                @error('escalate_to_specific')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Escalation Comment -->
            <div class="mb-6">
                <label for="komentar" class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Eskalasi <span class="text-red-500">*</span>
                </label>
                <textarea id="komentar" name="komentar" rows="4" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Jelaskan alasan mengapa konsultasi ini perlu dieskalasi...">{{ old('komentar') }}</textarea>
                @error('komentar')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Minimal 10 karakter</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeEscalationModal()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    Batal
                </button>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                    </svg>
                    Eskalasi Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endif

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

<script>
    let currentKonsultasiId = null;
    
    function openEscalationModal() {
        console.log('Opening escalation modal');
        const modal = document.getElementById('escalationModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            console.error('Escalation modal not found');
        }
    }

    function closeEscalationModal() {
        console.log('Closing escalation modal');
        const modal = document.getElementById('escalationModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            
            // Reset form
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
            }
            
            // Hide specific selection
            const specificDiv = document.getElementById('specific_selection');
            if (specificDiv) {
                specificDiv.classList.add('hidden');
            }
        }
    }

    function updateSpecificOptions() {
        const escalateToSelect = document.getElementById('escalate_to');
        const specificDiv = document.getElementById('specific_selection');
        const specificSelect = document.getElementById('escalate_to_specific');
        
        if (!escalateToSelect || !specificDiv || !specificSelect) {
            console.error('Required elements not found');
            return;
        }
        
        const selectedOption = escalateToSelect.options[escalateToSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const options = JSON.parse(selectedOption.getAttribute('data-options') || '{}');
            
            // Clear existing options
            specificSelect.innerHTML = '<option value="">-- Pilih Tujuan Spesifik --</option>';
            
            // Add new options
            Object.entries(options).forEach(([value, label]) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = label;
                specificSelect.appendChild(option);
            });
            
            // Show/hide specific selection based on whether there are options
            if (Object.keys(options).length > 0) {
                specificDiv.classList.remove('hidden');
                specificSelect.required = true;
            } else {
                specificDiv.classList.add('hidden');
                specificSelect.required = false;
            }
        } else {
            specificDiv.classList.add('hidden');
            specificSelect.required = false;
        }
    }

    // Close Confirmation Modal Functions
    function showCloseConfirmModal(konsultasiId, judulKonsultasi) {
        currentKonsultasiId = konsultasiId;
        
        const modal = document.getElementById('closeConfirmModal');
        const messageElement = document.getElementById('closeMessage');
        
        if (modal && messageElement) {
            messageElement.textContent = `Apakah Anda yakin ingin menutup konsultasi "${judulKonsultasi}"?`;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeCloseConfirmModal() {
        const modal = document.getElementById('closeConfirmModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            currentKonsultasiId = null;
        }
    }

    function confirmClose() {
        console.log('Confirming close for konsultasi:', currentKonsultasiId);
        if (currentKonsultasiId) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/advokasi-aspirasi/' + currentKonsultasiId + '/close';
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            
            console.log('Submitting form to:', form.action);
            form.submit();
        } else {
            console.error('No konsultasi ID set');
        }
    }

    // Modal click outside to close
    document.addEventListener('click', function(event) {
        const escalationModal = document.getElementById('escalationModal');
        const closeModal = document.getElementById('closeConfirmModal');
        
        if (event.target === escalationModal) {
            closeEscalationModal();
        }
        
        if (event.target === closeModal) {
            closeCloseConfirmModal();
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(event) {
        const escalationModal = document.getElementById('escalationModal');
        const closeModal = document.getElementById('closeConfirmModal');
        
        if (event.key === 'Escape') {
            if (escalationModal && !escalationModal.classList.contains('hidden')) {
                closeEscalationModal();
            }
            if (closeModal && !closeModal.classList.contains('hidden')) {
                closeCloseConfirmModal();
            }
        }
    });

    // Make functions globally available
    window.openEscalationModal = openEscalationModal;
    window.closeEscalationModal = closeEscalationModal;
    window.updateSpecificOptions = updateSpecificOptions;
    window.showCloseConfirmModal = showCloseConfirmModal;
    window.closeCloseConfirmModal = closeCloseConfirmModal;
    window.confirmClose = confirmClose;
    </script>

    <style>
    /* Modal animation */
    #escalationModal {
        animation: fadeIn 0.3s ease-out;
    }

    #escalationModal > div {
        animation: slideIn 0.3s ease-out;
    }

    #closeConfirmModal {
        animation: fadeIn 0.3s ease-out;
    }

    #closeConfirmModal > div {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from { 
            transform: translateY(-20px);
            opacity: 0;
        }
        to { 
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Focus states for accessibility */
    button:focus, select:focus, textarea:focus {
        outline: 2px solid #3B82F6;
        outline-offset: 2px;
    }

    /* Button alignment for responsive design */
    .flex.flex-col.sm\\:flex-row.gap-3 > button {
        flex: 1;
        min-width: 0;
    }

    @media (min-width: 640px) {
        .flex.flex-col.sm\\:flex-row.gap-3 > button {
            flex: none;
            min-width: auto;
        }
    }
</style>

@endsection