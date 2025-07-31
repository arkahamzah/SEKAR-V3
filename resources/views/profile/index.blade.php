@extends('layouts.app')

@section('title', 'Profile - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg mb-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg mb-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-3 py-2 rounded-lg mb-3 text-sm">
                {{ session('warning') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-3 py-2 rounded-lg mb-3 text-sm">
                {{ session('info') }}
            </div>
        @endif

        <!-- Validation Errors -->
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg mb-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header - Compressed -->
        <div class="mb-2">
            <h1 class="text-2xl font-bold text-gray-900">Profile Anggota</h1>
            <p class="text-gray-600 text-sm">Kelola informasi profil Anda</p>
        </div>

        <!-- Main Grid - Reduced gap and better responsive -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-2">
            <!-- Profile Card - Compressed -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-4 text-center">
                        <!-- Profile Picture Section - Smaller -->
                        <div class="relative inline-block mb-3">
                            @if($user->profile_picture)
                                <img src="{{ asset('storage/profile-pictures/' . $user->profile_picture) }}" 
                                     alt="Profile Picture" 
                                     class="w-20 h-20 bg-gray-300 rounded-full mx-auto object-cover border-2 border-gray-200">
                            @else
                                <div class="w-20 h-20 bg-gray-300 rounded-full mx-auto flex items-center justify-center border-2 border-gray-200">
                                    <span class="text-xl font-bold text-gray-600">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                            
                            <!-- Edit Profile Picture Button - Smaller -->
                            <button onclick="document.getElementById('profilePictureModal').style.display='block'" 
                                    class="absolute bottom-0 right-0 bg-blue-600 text-white p-1 rounded-full hover:bg-blue-700 transition text-xs">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>
                        </div>

                        <h4 class="font-semibold text-gray-900 text-base">{{ $user->name }}</h4>
                        <p class="text-gray-600 text-sm">NIK: {{ $user->nik }}</p>
                        @if($karyawan)
                            <p class="text-gray-600 text-sm">{{ $karyawan->V_SHORT_POSISI }}</p>
                            <p class="text-gray-600 text-sm">{{ $karyawan->V_SHORT_DIVISI }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Profile Information - Expanded to 3 columns -->
            <div class="lg:col-span-3 space-y-2">
                <!-- Basic Info - More compact -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Informasi Dasar</h3>
                    </div>
                    <div class="p-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="text-gray-600 font-medium text-sm">Nama Lengkap</label>
                                <p class="text-gray-900 mt-1">{{ $user->name }}</p>
                            </div>
                            <div>
                                <label class="text-gray-600 font-medium text-sm">NIK</label>
                                <p class="text-gray-900 mt-1">{{ $user->nik }}</p>
                            </div>
                            @if($karyawan)
                            <div>
                                <label class="text-gray-600 font-medium text-sm">Posisi</label>
                                <p class="text-gray-900 mt-1">{{ $karyawan->V_SHORT_POSISI }}</p>
                            </div>
                            <div>
                                <label class="text-gray-600 font-medium text-sm">Unit Kerja</label>
                                <p class="text-gray-900 mt-1">{{ $karyawan->V_SHORT_UNIT }}</p>
                            </div>
                            <div>
                                <label class="text-gray-600 font-medium text-sm">Divisi</label>
                                <p class="text-gray-900 mt-1">{{ $karyawan->V_SHORT_DIVISI }}</p>
                            </div>
                            <div>
                                <label class="text-gray-600 font-medium text-sm">Lokasi</label>
                                <p class="text-gray-900 mt-1">{{ $karyawan->V_KOTA_GEDUNG }}</p>
                            </div>
                            @endif
                            <div>
                                <label class="text-gray-600 font-medium text-sm">Tanggal Bergabung</label>
                                <p class="text-gray-900 mt-1">{{ $joinDate->format('d F Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Iuran Section - Compact with grid -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">Informasi Iuran</h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                            <div class="bg-blue-50 p-3 rounded">
                                <p class="text-blue-600 font-medium text-sm">Iuran Wajib</p>
                                <p class="text-blue-900 font-bold text-base">Rp {{ number_format($iuranWajib, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-green-50 p-3 rounded">
                                <p class="text-green-600 font-medium text-sm">Iuran Sukarela</p>
                                <p class="text-green-900 font-bold text-base">Rp {{ number_format($effectiveIuranSukarela, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-purple-50 p-3 rounded">
                                <p class="text-purple-600 font-medium text-sm">Total/Bulan</p>
                                <p class="text-purple-900 font-bold text-base">Rp {{ number_format($totalIuranPerBulan, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded">
                                <p class="text-gray-600 font-medium text-sm">Total Terbayar</p>
                                <p class="text-gray-900 font-bold text-base">Rp {{ number_format($totalIuran, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        @if($pendingChange)
                        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded p-3">
                            <p class="text-sm text-yellow-800">
                                <strong>Perubahan Pending:</strong> Iuran sukarela akan berubah menjadi 
                                <strong>Rp {{ number_format($pendingChange->NOMINAL_BARU, 0, ',', '.') }}</strong> 
                                pada tanggal {{ \Carbon\Carbon::parse($pendingChange->TGL_IMPLEMENTASI)->format('d F Y') }}
                            </p>
                        </div>
                        @endif

                        <!-- Update Iuran Button -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <button onclick="toggleIuranForm()" id="iuranToggleBtn" 
                                    class="w-full text-sm bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                                Ubah Iuran Sukarela
                            </button>

                            <!-- Iuran Update Form -->
                            <div id="iuranUpdateForm" style="display: none;" class="mt-4">
                                <form method="POST" action="{{ route('profile.update-iuran') }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label for="iuran_sukarela" class="block text-sm font-medium text-gray-700">
                                            Nominal Iuran Sukarela Baru
                                        </label>
                                        <input type="number" 
                                            name="iuran_sukarela" 
                                            id="iuran_sukarela" 
                                            value="{{ $effectiveIuranSukarela }}"
                                            min="0" 
                                            step="5000"
                                            class="mt-1 w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"
                                            placeholder="0">
                                        <p class="text-sm text-gray-500 mt-1">Minimal kelipatan Rp 5.000</p>
                                    </div>
                                    <div class="flex space-x-3">
                                        <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 transition">
                                            Ajukan Perubahan
                                        </button>
                                        <button type="button" onclick="toggleIuranForm()" 
                                                class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-400 transition">
                                            Batal
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Picture Modal - Compact -->
<div id="profilePictureModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Update Foto Profil</h3>
        
        <!-- Current Photo Preview -->
        <div class="text-center mb-3">
            @if($user->profile_picture)
                <img src="{{ asset('storage/profile-pictures/' . $user->profile_picture) }}" 
                     alt="Current Profile Picture" 
                     class="w-20 h-20 rounded-full mx-auto object-cover border-2 border-gray-200">
            @else
                <div class="w-20 h-20 bg-gray-300 rounded-full mx-auto flex items-center justify-center border-2 border-gray-200">
                    <span class="text-xl font-bold text-gray-600">{{ substr($user->name, 0, 1) }}</span>
                </div>
            @endif
        </div>

        <!-- Upload Form -->
        <form method="POST" action="{{ route('profile.update-picture') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label for="profile_picture" class="block text-xs font-medium text-gray-700 mb-1">Pilih Foto Baru</label>
                <input type="file" 
                       name="profile_picture" 
                       id="profile_picture" 
                       accept="image/*"
                       class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-0.5">Format: JPEG, PNG, JPG. Maksimal 2MB.</p>
            </div>

            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-3 py-1.5 rounded text-xs hover:bg-blue-700 transition">
                    Upload Foto
                </button>
                <button type="button" 
                        onclick="document.getElementById('profilePictureModal').style.display='none'" 
                        class="flex-1 bg-gray-300 text-gray-700 px-3 py-1.5 rounded text-xs hover:bg-gray-400 transition">
                    Batal
                </button>
            </div>
        </form>

        <!-- Delete Photo Button -->
        @if($user->profile_picture)
        <div class="mt-3 pt-3 border-t border-gray-200">
            <form method="POST" action="{{ route('profile.delete-picture') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto profil?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-600 text-white px-3 py-1.5 rounded text-xs hover:bg-red-700 transition">
                    Hapus Foto Profil
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

<script>
function toggleIuranForm() {
    const form = document.getElementById('iuranUpdateForm');
    const btn = document.getElementById('iuranToggleBtn');
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        btn.textContent = 'Batal';
    } else {
        form.style.display = 'none';
        btn.textContent = 'Ubah Iuran Sukarela';
    }
}

// Close modal when clicking outside
document.getElementById('profilePictureModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});
</script>
@endsection