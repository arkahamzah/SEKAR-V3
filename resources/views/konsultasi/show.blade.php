@extends('layouts.app')

@section('title', 'Detail Konsultasi')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $konsultasi->status_color ?? 'gray' }}-100 text-{{ $konsultasi->status_color ?? 'gray' }}-800">
                        {{ $konsultasi->status_text ?? 'Unknown' }}
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
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex-1">
                                <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $konsultasi->JUDUL }}</h2>
                                <div class="flex flex-wrap items-center text-sm text-gray-500 space-x-4">
                                    <span>{{ $konsultasi->TUJUAN }}{{ $konsultasi->TUJUAN_SPESIFIK ? ' - ' . $konsultasi->TUJUAN_SPESIFIK : '' }}</span>
                                    {{-- PERBAIKAN: Menggunakan Carbon::parse() untuk mengubah string menjadi objek tanggal sebelum diformat --}}
                                    <span>{{ $konsultasi->created_at ? \Carbon\Carbon::parse($konsultasi->created_at)->format('d M Y, H:i') : '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Deskripsi</h3>
                            <div class="prose prose-sm max-w-none text-gray-700">
                                {!! nl2br(e($konsultasi->DESKRIPSI)) !!}
                            </div>
                        </div>

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
                                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
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


                @if($isCurrentUserActiveHandler && $konsultasi->STATUS !== 'CLOSED')
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Aksi Admin</h3>
                            <p class="mt-1 text-sm text-gray-600">Pilih tindakan yang ingin dilakukan untuk konsultasi ini</p>
                        </div>
                        <div class="p-6">
                            <div class="flex flex-col sm:flex-row gap-3">
                                @if($konsultasi->JENIS === 'ADVOKASI' && !empty($escalationOptions))
                                <button onclick="openEscalationModal()" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path></svg>
                                    Eskalasi Konsultasi
                                </button>
                                @endif
                                <button type="button" onclick="showCloseConfirmModal('{{ $konsultasi->ID }}', '{{ addslashes($konsultasi->JUDUL) }}')" class="inline-flex items-center justify-center px-4 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Tutup Konsultasi
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Komentar & Tanggapan</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        @forelse($konsultasi->komentar as $komentar)
                        <div class="flex space-x-3 {{ $komentar->PENGIRIM_ROLE === 'ADMIN' ? 'bg-blue-50 p-4 rounded-lg' : '' }}">
                            <div class="flex-shrink-0">
                                @if($komentar->PENGIRIM_ROLE === 'ADMIN')
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center"><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                                @else
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center"><svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-sm font-medium text-gray-900">
                                        @if($komentar->PENGIRIM_ROLE === 'ADMIN')
                                            {{ $komentar->user?->pengurus?->role?->NAME ?? 'Admin' }}
                                        @else
                                            {{ $komentar->user?->name ?? 'User' }}
                                        @endif
                                    </span>
                                    @if($komentar->PENGIRIM_ROLE === 'ADMIN')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Tanggapan Resmi</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-700 mb-2">{{ $komentar->KOMENTAR }}</p>
                                <p class="text-xs text-gray-500">{{ $komentar->created_at ? \Carbon\Carbon::parse($komentar->created_at)->format('d M Y, H:i') : '-' }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            <p class="text-sm">Belum ada komentar</p>
                        </div>
                        @endforelse
                    </div>

                    @if($konsultasi->STATUS !== 'CLOSED')
                    <div class="border-t border-gray-200 p-6">
                        @if(auth()->user()->nik === $konsultasi->N_NIK || (auth()->user()?->pengurus?->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD'])))
                            <form action="{{ route('konsultasi.comment', $konsultasi->ID) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="komentar" class="block text-sm font-medium text-gray-700 mb-2">Tambahkan Komentar</label>
                                    <textarea name="komentar" id="komentar" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Tulis komentar atau tanggapan Anda..." required></textarea>
                                    @error('komentar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                    Kirim Komentar
                                </button>
                            </form>
                        @else
                            <div class="text-center py-4 bg-gray-50 rounded-lg"><p class="text-gray-500 text-sm">Tidak dapat menambahkan komentar baru.</p></div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Informasi</h3></div>
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
                            <dd class="mt-1 text-sm text-gray-900">{{ $konsultasi->created_at ? \Carbon\Carbon::parse($konsultasi->created_at)->format('d F Y, H:i') : '-' }}</dd>
                        </div>

                        @if($konsultasi->updated_at && $konsultasi->updated_at != $konsultasi->created_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Terakhir Diperbarui</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $konsultasi->updated_at ? \Carbon\Carbon::parse($konsultasi->updated_at)->format('d F Y, H:i') : '-' }}</dd>
                        </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Pengaju</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $konsultasi->karyawan?->V_NAMA_KARYAWAN ?? $konsultasi->N_NIK }}</dd>
                        </div>
                    </div>
                </div>

                @if($konsultasi->komentar->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Timeline</h3></div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li>
                                    <div class="relative pb-8">
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div class="flex-shrink-0"><div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center"><svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg></div></div>
                                            <div class="min-w-0 flex-1">
                                                <div>
                                                    <p class="text-sm text-gray-900 font-medium">Konsultasi dibuat</p>
                                                    <p class="text-sm text-gray-500">{{ $konsultasi->created_at ? \Carbon\Carbon::parse($konsultasi->created_at)->format('d M Y, H:i') : '-' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                @foreach($konsultasi->komentar as $index => $komentar)
                                <li>
                                    <div class="relative {{ $index < $konsultasi->komentar->count() - 1 ? 'pb-8' : '' }}">
                                        @if($index < $konsultasi->komentar->count() - 1)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div class="flex-shrink-0">
                                                @if($komentar->PENGIRIM_ROLE === 'ADMIN')
                                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center"><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg></div>
                                                @else
                                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center"><svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg></div>
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
                                                    <p class="text-sm text-gray-500">{{ $komentar->created_at ? \Carbon\Carbon::parse($komentar->created_at)->format('d M Y, H:i') : '-' }}</p>
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

{{-- PERBAIKAN: Modal Eskalasi dengan Tampilan Baru --}}
@if(!empty($escalationOptions))
<div id="escalationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4 transform transition-all"
         role="dialog" aria-modal="true" aria-labelledby="modal-headline">
        <form action="{{ route('konsultasi.escalate', $konsultasi->ID) }}" method="POST">
            @csrf
            <div class="p-6">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-100">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2" id="modal-headline">
                            Eskalasi Konsultasi
                        </h3>
                        <p class="text-sm text-gray-600">
                            Pilih tujuan eskalasi dan berikan alasan yang jelas.
                        </p>
                    </div>
                </div>
                <div class="mt-6 space-y-4">
                    <div>
                        <label for="escalate_to" class="block text-sm font-medium text-gray-700 mb-1">Eskalasi Ke</label>
                        <select id="escalate_to" name="escalate_to" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 sm:text-sm" onchange="updateSpecificOptions()">
                            <option value="">-- Pilih Tujuan --</option>
                            @foreach($escalationOptions as $key => $details)
                                <option value="{{ $key }}" data-options='{{ json_encode($details["specific_options"]) }}'>
                                    {{ $details['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="specific_selection" class="hidden">
                        <label for="escalate_to_specific" class="block text-sm font-medium text-gray-700 mb-1">Tujuan Spesifik</label>
                        <select id="escalate_to_specific" name="escalate_to_specific" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 sm:text-sm">
                            <option value="">-- Pilih Tujuan Spesifik --</option>
                        </select>
                    </div>
                    <div>
                        <label for="escalation_comment" class="block text-sm font-medium text-gray-700 mb-1">Alasan Eskalasi</label>
                        <textarea id="escalation_comment" name="komentar" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 sm:text-sm" placeholder="Jelaskan mengapa konsultasi ini perlu dieskalasi..." required minlength="10"></textarea>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 rounded-b-xl flex justify-end space-x-3">
                <button type="button" onclick="closeEscalationModal()" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Kirim Eskalasi
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<div id="closeConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[100] hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-red-100">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Penutupan</h3>
                    <div class="text-gray-600 text-sm">
                        <p id="closeMessage">Apakah Anda yakin ingin menutup kasus ini?</p>
                        <p class="mt-2 text-xs text-gray-500">Setelah ditutup, kasus tidak dapat dibuka kembali dan tidak dapat menambahkan komentar baru.</p>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closeCloseConfirmModal()" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">Batal</button>
                <button onclick="confirmClose()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">Ya, Tutup Kasus</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentKonsultasiId = null;

    function showCloseConfirmModal(konsultasiId, judulKonsultasi) {
        currentKonsultasiId = konsultasiId;
        const modal = document.getElementById('closeConfirmModal');
        const messageElement = document.getElementById('closeMessage');
        if (modal && messageElement) {
            messageElement.textContent = `Apakah Anda yakin ingin menutup konsultasi "${judulKonsultasi}"?`;
            modal.classList.remove('hidden');
        }
    }

    function closeCloseConfirmModal() {
        const modal = document.getElementById('closeConfirmModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function confirmClose() {
        if (currentKonsultasiId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/advokasi-aspirasi/' + currentKonsultasiId + '/close';
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function openEscalationModal() {
        const modal = document.getElementById('escalationModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeEscalationModal() {
        const modal = document.getElementById('escalationModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }


    function updateSpecificOptions() {
        const escalateToSelect = document.getElementById('escalate_to');
        const specificDiv = document.getElementById('specific_selection');
        const specificSelect = document.getElementById('escalate_to_specific');

        if (!escalateToSelect || !specificDiv || !specificSelect) {
            console.error('Required elements for escalation modal not found');
            return;
        }

        const selectedOption = escalateToSelect.options[escalateToSelect.selectedIndex];
        const escalateToValue = selectedOption.value;

        // Reset state first
        specificDiv.classList.add('hidden');
        specificSelect.required = false;
        specificSelect.innerHTML = '<option value="">-- Pilih Tujuan Spesifik --</option>';

        if (selectedOption && escalateToValue) {
            const options = JSON.parse(selectedOption.getAttribute('data-options') || '{}');

            if (escalateToValue === 'DPP') {
                const dppValue = Object.keys(options).length > 0 ? Object.keys(options)[0] : 'DPP';
                const dppLabel = Object.values(options).length > 0 ? Object.values(options)[0] : 'Dewan Pengurus Pusat';
                specificSelect.innerHTML = `<option value="${dppValue}" selected>${dppLabel}</option>`;
            }
            else if (Object.keys(options).length > 0) {
                Object.entries(options).forEach(([value, label]) => {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = label;
                    specificSelect.appendChild(option);
                });
                specificDiv.classList.remove('hidden');
                specificSelect.required = true;
            }
        }
    }

    function confirmClose() {
        console.log('Confirming close for konsultasi:', currentKonsultasiId);
        if (currentKonsultasiId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/advokasi-aspirasi/' + currentKonsultasiId + '/close';
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

    window.openEscalationModal = openEscalationModal;
    window.closeEscalationModal = closeEscalationModal;
    window.updateSpecificOptions = updateSpecificOptions;
    window.showCloseConfirmModal = showCloseConfirmModal;
    window.closeCloseConfirmModal = closeCloseConfirmModal;
    window.confirmClose = confirmClose;
    </script>

    <style>
    #escalationModal > div {
        animation: slideIn 0.3s ease-out;
    }
    #closeConfirmModal > div {
        animation: slideIn 0.3s ease-out;
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
    </style>
@endsection