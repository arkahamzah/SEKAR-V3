@extends('layouts.app')

@section('title', 'Profile - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Success/Error Messages -->
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

        @if(session('warning'))
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg mb-6">
                {{ session('warning') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-6">
                {{ session('info') }}
            </div>
        @endif

        <!-- Validation Errors -->
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Profile Anggota</h1>
            <p class="text-gray-600 text-sm mt-1">Kelola informasi profil Anda</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 text-center">
                        <!-- Profile Picture Section -->
                        <div class="relative inline-block mb-4">
                            @if($user->profile_picture)
                                <img src="{{ asset('storage/profile-pictures/' . $user->profile_picture) }}" 
                                     alt="Profile Picture" 
                                     class="w-20 h-20 bg-gray-300 rounded-full mx-auto object-cover border-4 border-gray-200">
                            @else
                                <div class="w-20 h-20 bg-gray-300 rounded-full mx-auto flex items-center justify-center border-4 border-gray-200">
                                    <span class="text-2xl font-bold text-gray-600">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                            
                            <!-- Edit Profile Picture Button -->
                            <button onclick="document.getElementById('profilePictureModal').style.display='block'" 
                                    class="absolute bottom-0 right-0 bg-blue-600 text-white p-1.5 rounded-full hover:bg-blue-700 transition text-xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>
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
                                <p class="text-gray-900">{{ $karyawan->V_SHORT_POSISI ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Divisi</label>
                                <p class="text-gray-900">{{ $karyawan->V_SHORT_DIVISI ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                                <p class="text-gray-900">{{ $karyawan->V_SHORT_UNIT ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Kerja</label>
                                <p class="text-gray-900">{{ $karyawan->V_KOTA_GEDUNG ?? '-' }}</p>
                            </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Bergabung</label>
                                <p class="text-gray-900">{{ $joinDate->format('d F Y') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Keanggotaan</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Anggota Aktif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Update Section -->
               <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Email</h3>
                </div>
                <div class="p-6">
                    <!-- Email Display Section -->
                    <div id="emailDisplay" class="flex items-center justify-between mb-4">
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
                                <p class="text-xs text-yellow-600 mt-1">⚠ Gunakan email pribadi untuk fitur reset password dan notifikasi</p>
                            @else
                                <p class="text-xs text-green-600 mt-1">✅ Email valid untuk notifikasi dan reset password</p>
                            @endif
                        </div>
                        <button id="editEmailBtn" 
                                onclick="showEmailForm()" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                            {{ $isDummyEmail ? 'Tambah Email' : 'Ubah Email' }}
                        </button>
                    </div>

                    <!-- Email Edit Form (Hidden by default) -->
                    <div id="emailEditForm" style="display: none;">
                        @if($isDummyEmail)
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-yellow-800">Email Belum Diatur</p>
                                        <p class="text-xs text-yellow-700">Silakan atur email Anda untuk mendapatkan notifikasi dan informasi terbaru.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('profile.update-email') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Baru</label>
                                <input type="email" 
                                    name="email" 
                                    id="email" 
                                    value="{{ old('email', $isDummyEmail ? '' : $user->email) }}" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                                    placeholder="Masukkan email Anda"
                                    required>
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Password Saat Ini</label>
                                <input type="password" 
                                    name="current_password" 
                                    id="current_password" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-500 @enderror"
                                    placeholder="Masukkan password untuk konfirmasi"
                                    required>
                                @error('current_password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex space-x-3">
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                                    {{ $isDummyEmail ? 'Tambah Email' : 'Perbarui Email' }}
                                </button>
                                <button type="button" 
                                        onclick="hideEmailForm()" 
                                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition text-sm font-medium">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
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
                            <p class="text-sm text-gray-600">Bergabung sejak {{ $joinDate->format('F Y') }}</p>
                        </div>

                        <!-- Edit Iuran Sukarela -->
                        <div class="border-t pt-4">
                            <button id="editIuranBtn" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm {{ (isset($pendingChange) && $pendingChange) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ (isset($pendingChange) && $pendingChange) ? 'disabled' : '' }}>
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

<!-- Profile Picture Modal -->
<div id="profilePictureModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Ubah Foto Profil</h3>
                <button onclick="document.getElementById('profilePictureModal').style.display='none'" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Current Photo Preview -->
            <div class="text-center mb-4">
                @if($user->profile_picture)
                    <img src="{{ asset('storage/profile-pictures/' . $user->profile_picture) }}" 
                         alt="Current Profile Picture" 
                         class="w-24 h-24 rounded-full mx-auto object-cover border-2 border-gray-200">
                @else
                    <div class="w-24 h-24 bg-gray-300 rounded-full mx-auto flex items-center justify-center border-2 border-gray-200">
                        <span class="text-2xl font-bold text-gray-600">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                @endif
            </div>

            <!-- Upload Form -->
            <form method="POST" action="{{ route('profile.update-picture') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-2">Pilih Foto Baru</label>
                    <input type="file" 
                           name="profile_picture" 
                           id="profile_picture" 
                           accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG. Maksimal 2MB.</p>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                        Upload Foto
                    </button>
                    <button type="button" 
                            onclick="document.getElementById('profilePictureModal').style.display='none'" 
                            class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition text-sm font-medium">
                        Batal
                    </button>
                </div>
            </form>

            <!-- Delete Photo Button -->
            @if($user->profile_picture)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <form method="POST" action="{{ route('profile.delete-picture') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto profil?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-100 text-red-700 px-4 py-2 rounded-lg hover:bg-red-200 transition text-sm font-medium">
                        Hapus Foto
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

    <script>
        function showEmailForm() {
            document.getElementById('emailDisplay').style.display = 'none';
            document.getElementById('emailEditForm').style.display = 'block';
            // Focus pada input email
            document.getElementById('email').focus();
        }

        function hideEmailForm() {
            document.getElementById('emailDisplay').style.display = 'flex';
            document.getElementById('emailEditForm').style.display = 'none';
            // Clear form jika ada error
            document.getElementById('email').value = '{{ old('email', $isDummyEmail ? '' : $user->email) }}';
            document.getElementById('current_password').value = '';
        }

        // Show form if there are validation errors
        @if($errors->has('email') || $errors->has('current_password'))
        document.addEventListener('DOMContentLoaded', function() {
            showEmailForm();
        });
        @endif
    </script>

@endsection