@extends('layouts.app')

@section('title', 'Profile Sekar - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-3 text-sm text-gray-600">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                <span>/</span>
                <span class="text-gray-900">Profile Sekar</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
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

        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-6">
                {{ session('info') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- ID Card / Kartu Anggota -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <h3 class="text-white font-semibold text-lg">Kartu Anggota</h3>
                    </div>
                    <div class="p-6 text-center">
                        <div class="w-20 h-20 bg-gray-300 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <span class="text-2xl font-bold text-gray-600">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                        <h4 class="font-semibold text-gray-900 text-lg">{{ $user->name }}</h4>
                        <p class="text-gray-600 text-sm">NIK: {{ $user->nik }}</p>
                        @if($karyawan)
                            <p class="text-gray-600 text-sm">{{ $karyawan->V_SHORT_POSISI }}</p>
                            <p class="text-gray-600 text-xs mt-1">{{ $karyawan->V_SHORT_DIVISI }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Anggota</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <p class="text-gray-900">{{ $user->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                                <p class="text-gray-900">{{ $user->nik }}</p>
                            </div>
                            @if($karyawan)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                                    <p class="text-gray-900">{{ $karyawan->V_SHORT_POSISI }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                                    <p class="text-gray-900">{{ $karyawan->V_SHORT_UNIT }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Divisi</label>
                                    <p class="text-gray-900">{{ $karyawan->V_SHORT_DIVISI }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Kerja</label>
                                    <p class="text-gray-900">{{ $karyawan->V_KOTA_GEDUNG }}</p>
                                </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Bergabung</label>
                                <p class="text-gray-900">{{ $joinDate->format('d F Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Management -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Email</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Terdaftar</label>
                                <div class="flex items-center">
                                    <p class="text-gray-900 mr-3">{{ $user->email }}</p>
                                    @if($isDummyEmail)
                                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Dummy Email</span>
                                    @else
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Email Aktif</span>
                                    @endif
                                </div>
                                @if($isDummyEmail)
                                    <p class="text-xs text-yellow-600 mt-1">⚠️ Gunakan email pribadi untuk fitur reset password dan notifikasi</p>
                                @else
                                    <p class="text-xs text-green-600 mt-1">✅ Email valid untuk notifikasi dan reset password</p>
                                @endif
                            </div>
                            <button id="editEmailBtn"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                                {{ $isDummyEmail ? 'Tambah Email' : 'Ubah Email' }}
                            </button>
                        </div>

                        @if($isDummyEmail)
                        <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-blue-900 mb-1">Manfaat Email Pribadi</h4>
                                    <ul class="text-xs text-blue-700 space-y-1">
                                        <li>• Reset password sendiri tanpa bantuan admin</li>
                                        <li>• Notifikasi langsung untuk aktivitas akun</li>
                                        <li>• Komunikasi resmi dari SEKAR</li>
                                        <li>• Keamanan akun yang lebih baik</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Iuran Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Iuran</h3>
                    </div>
                    <div class="p-6">
                        <!-- Status Pending -->
                        @if(isset($pendingChange) && $pendingChange)
                        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg mb-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800">Perubahan Iuran Sedang Diproses</p>
                                    <p class="text-xs text-yellow-700">
                                        Dari Rp {{ number_format($pendingChange->NOMINAL_LAMA, 0, ',', '.') }}
                                        menjadi Rp {{ number_format($pendingChange->NOMINAL_BARU, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-yellow-700">
                                        Akan diproses: {{ $pendingChange->TGL_PROSES->format('d M Y') }} |
                                        Diterapkan: {{ $pendingChange->TGL_IMPLEMENTASI->format('d M Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-blue-700 mb-1">Iuran Wajib</label>
                                <p class="text-2xl font-bold text-blue-900">Rp {{ number_format($iuranWajib, 0, ',', '.') }}</p>
                                <p class="text-xs text-blue-600">/bulan</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-green-700 mb-1">Iuran Sukarela</label>
                                <p class="text-2xl font-bold text-green-900">Rp {{ number_format(isset($effectiveIuranSukarela) ? $effectiveIuranSukarela : $iuranSukarela, 0, ',', '.') }}</p>
                                <p class="text-xs text-green-600">/bulan</p>
                                @if(isset($pendingChange) && $pendingChange)
                                    <p class="text-xs text-yellow-600">(sedang diproses)</p>
                                @endif
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total per Bulan</label>
                                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($iuranWajib + (isset($effectiveIuranSukarela) ? $effectiveIuranSukarela : $iuranSukarela), 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-600">wajib + sukarela</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Iuran Terbayar</label>
                            <p class="text-xl font-semibold text-gray-900">Rp {{ number_format($totalIuran, 0, ',', '.') }}</p>
                            <p class="text-sm text-gray-600">Bergabung sejak {{ $joinDate->format('F Y') }} | Berdasarkan iuran aktif saat ini</p>
                        </div>

                        <!-- Edit Iuran Sukarela -->
                        <div class="border-t pt-4">
                            <button id="editIuranBtn"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                                Edit Iuran Sukarela
                            </button>
                        </div>
                    </div>
                </div>

                <!-- History Iuran -->
                @if(isset($iuranHistory) && $iuranHistory->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Riwayat Perubahan Iuran</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($iuranHistory as $history)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $history->JENIS }} -
                                                Rp {{ number_format($history->NOMINAL_LAMA, 0, ',', '.') }}
                                                → Rp {{ number_format($history->NOMINAL_BARU, 0, ',', '.') }}
                                            </span>
                                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $history->status_color }}-100 text-{{ $history->status_color }}-700">
                                                {{ $history->status_text }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-600">
                                            Tanggal Perubahan: {{ $history->TGL_PERUBAHAN->format('d M Y, H:i') }}
                                        </p>
                                        @if($history->TGL_PROSES)
                                            <p class="text-xs text-gray-600">
                                                Diproses: {{ $history->TGL_PROSES->format('d M Y') }}
                                            </p>
                                        @endif
                                        @if($history->TGL_IMPLEMENTASI)
                                            <p class="text-xs text-gray-600">
                                                Diterapkan: {{ $history->TGL_IMPLEMENTASI->format('d M Y') }}
                                            </p>
                                        @endif
                                        @if($history->KETERANGAN)
                                            <p class="text-xs text-gray-500 mt-1">{{ $history->KETERANGAN }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Email -->
<div id="editEmailModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $isDummyEmail ? 'Tambah Email Pribadi' : 'Ubah Email' }}</h3>

        <form method="POST" action="{{ route('profile.update-email') }}" id="emailForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Pribadi</label>
                <input
                    type="email"
                    name="email"
                    value="{{ $isDummyEmail ? '' : $user->email }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="email.pribadi@gmail.com"
                    required
                >
                <p class="text-xs text-gray-500 mt-1">Email untuk notifikasi dan reset password</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Password Saat Ini</label>
                <input
                    type="password"
                    name="current_password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Password untuk konfirmasi"
                    required
                >
                <p class="text-xs text-gray-500 mt-1">Diperlukan untuk keamanan</p>
            </div>

            <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg mb-4">
                <h4 class="text-sm font-medium text-blue-800 mb-2">Informasi Penting:</h4>
                <ul class="text-xs text-blue-700 space-y-1">
                    <li>• Email akan digunakan untuk reset password</li>
                    <li>• Notifikasi sistem akan dikirim ke email ini</li>
                    <li>• Pastikan email dapat diakses dengan mudah</li>
                    <li>• Email konfirmasi akan dikirim setelah perubahan</li>
                </ul>
            </div>

            <div class="flex space-x-3">
                <button type="button" id="cancelEmailBtn" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                    {{ $isDummyEmail ? 'Tambah Email' : 'Perbarui Email' }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Iuran Sukarela -->
<div id="editIuranModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Iuran Sukarela</h3>

        <form method="POST" action="{{ route('profile.update-iuran') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nominal Iuran Sukarela</label>
                <input
                    type="number"
                    name="iuran_sukarela"
                    value="{{ isset($effectiveIuranSukarela) ? $effectiveIuranSukarela : $iuranSukarela }}"
                    min="0"
                    step="5000"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="0"
                >
                <p class="text-xs text-gray-500 mt-1">Dalam kelipatan Rp 5.000</p>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg mb-4">
                <h4 class="text-sm font-medium text-yellow-800 mb-2">Alur Proses Perubahan Iuran:</h4>
                <div class="text-xs text-yellow-700 space-y-1">
                    <p>• <strong>Hari ini:</strong> Permohonan diajukan</p>
                    <p>• <strong>Tanggal 20 bulan depan:</strong> Diproses oleh HC</p>
                    <p>• <strong>Tanggal 1 bulan ke-2:</strong> Diterapkan di payroll</p>
                </div>
                <p class="text-xs text-yellow-800 mt-2">
                    <strong>Contoh:</strong> Jika mengajukan hari ini ({{ now()->format('d M Y') }}),
                    akan diproses {{ now()->addMonth()->day(20)->format('d M Y') }}
                    dan diterapkan {{ now()->addMonths(2)->day(1)->format('d M Y') }}.
                </p>
            </div>

            <div class="flex space-x-3">
                <button type="button" id="cancelEditBtn" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editEmailBtn = document.getElementById('editEmailBtn');
    const emailModal = document.getElementById('editEmailModal');
    const cancelEmailBtn = document.getElementById('cancelEmailBtn');

    const editBtn = document.getElementById('editIuranBtn');
    const modal = document.getElementById('editIuranModal');
    const cancelBtn = document.getElementById('cancelEditBtn');

    // Email modal handlers
    if (editEmailBtn) {
        editEmailBtn.addEventListener('click', function() {
            emailModal.classList.remove('hidden');
        });
    }

    if (cancelEmailBtn) {
        cancelEmailBtn.addEventListener('click', function() {
            emailModal.classList.add('hidden');
        });
    }

    if (emailModal) {
        emailModal.addEventListener('click', function(e) {
            if (e.target === emailModal) {
                emailModal.classList.add('hidden');
            }
        });
    }

    // Iuran modal handlers
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            modal.classList.remove('hidden');
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
        });
    }

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    }

    // Show validation errors if any
    @if($errors->any())
        @if($errors->has('email') || $errors->has('current_password'))
            emailModal.classList.remove('hidden');
        @endif
    @endif
});
</script>
@endsection