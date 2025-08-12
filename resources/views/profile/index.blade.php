@extends('layouts.app')

@section('title', 'Profile - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
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

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg mb-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-2">
            <h1 class="text-2xl font-bold text-gray-900">Profile Anggota</h1>
            <p class="text-gray-600 text-sm">Kelola informasi profil Anda</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-2">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-4 text-center">
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

            <div class="lg:col-span-3 space-y-2">
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

                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">Informasi Iuran</h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm mb-4">
                            <div class="bg-blue-50 p-3 rounded">
                                <p class="text-blue-600 font-medium text-sm">Iuran Wajib</p>
                                <p class="text-blue-900 font-bold text-base">Rp {{ number_format($iuranWajib, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-green-50 p-3 rounded">
                                <p class="text-green-600 font-medium text-sm">Iuran Sukarela</p>
                                <p class="text-green-900 font-bold text-base">Rp {{ number_format($effectiveIuranSukarela, 0, ',', '.') }}</p>
                                @if($pendingChange)
                                    <p class="text-green-600 text-xs mt-1">
                                        (Ubah Iuran diproses: Rp {{ number_format($pendingChange->NOMINAL_BARU, 0, ',', '.') }})
                                    </p>
                                @endif
                            </div>
                            <div class="bg-purple-50 p-3 rounded">
                                <p class="text-purple-600 font-medium text-sm">Total/Bulan</p>
                                <p class="text-purple-900 font-bold text-base">Rp {{ number_format($totalIuranPerBulan, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-indigo-50 p-3 rounded">
                                <p class="text-indigo-600 font-medium text-sm">Total Terbayar</p>
                                <p class="text-indigo-900 font-bold text-base">Rp {{ number_format($totalIuranTerbayar, 0, ',', '.') }}</p>
                                <p class="text-indigo-600 text-xs mt-1">
                                    {{ $bulanTerbayar }} bulan lunas
                                    @if($bulanTunggakan > 0)
                                        <span class="text-yellow-600">({{ $bulanTunggakan }} belum diproses)</span>
                                    @endif
                                </p>
                            </div>
                        </div>

@if($pendingChange)
<div class="mt-4 bg-yellow-50 border border-yellow-200 rounded p-3">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-sm text-yellow-800">
                <strong>Perubahan Iuran diproses:</strong> Iuran sukarela akan berubah dari
                <strong>Rp {{ number_format($iuranSukarela, 0, ',', '.') }}</strong> menjadi
                <strong>Rp {{ number_format($pendingChange->NOMINAL_BARU, 0, ',', '.') }}</strong>
                pada bulan {{ \Carbon\Carbon::parse($pendingChange->TGL_IMPLEMENTASI)->format('F') }}
            </p>
            <p class="text-xs text-yellow-600 mt-1 mb-0.5">
                Diajukan: {{ \Carbon\Carbon::parse($pendingChange->TGL_PERUBAHAN)->format('d F Y H:i') }}
            </p>
            <p class="text-xs text-yellow-700 font-medium">
                (Pembatalan perubahan dapat dilakukan sebelum tanggal 20 pada bulan berjalan)
            </p>
        </div>
        <div class="ml-4">
            <form method="POST" action="{{ route('profile.cancel-iuran') }}"
                  onsubmit="return confirm('Apakah Anda yakin ingin membatalkan perubahan iuran ini?')"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition">
                    Batalkan
                </button>
            </form>
        </div>
    </div>
</div>
@endif

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <button onclick="toggleIuranForm()" id="iuranToggleBtn"
                                        class="w-full text-sm bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                                    @if($pendingChange)
                                        Ubah Nominal Pending
                                    @else
                                        Ubah Iuran Sukarela
                                    @endif
                                </button>

                                <button onclick="openHistoryModal()"
                                        class="w-full text-sm bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Riwayat Perubahan
                                </button>

                                <button onclick="openPaymentModal()"
                                        class="w-full text-sm bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Riwayat Pembayaran
                                </button>
                            </div>

                            <div id="iuranUpdateForm" style="display: none;" class="mt-4">
                                <form method="POST" action="{{ route('profile.update-iuran') }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label for="iuran_sukarela" class="block text-sm font-medium text-gray-700">
                                            @if($pendingChange)
                                                Ubah Nominal Iuran (saat ini: Rp {{ number_format($pendingChange->NOMINAL_BARU, 0, ',', '.') }})
                                            @else
                                                Nominal Iuran Sukarela Baru
                                            @endif
                                        </label>
                                        <input type="number"
                                               name="iuran_sukarela"
                                               id="iuran_sukarela"
                                               value="{{ $effectiveIuranSukarela }}"
                                               min="0"
                                               step="5000"
                                               class="mt-1 w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"
                                               placeholder="0">
                                        <p class="text-sm text-gray-500 mt-1">
                                            Minimal kelipatan Rp 5.000
                                            @if($pendingChange)
                                                <br><span class="text-yellow-600">Note: Pengajuan sebelumnya akan disimpan ke riwayat</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex space-x-3">
                                        <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 transition">
                                            @if($pendingChange)
                                                Perbarui Pengajuan
                                            @else
                                                Ajukan Perubahan
                                            @endif
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

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-base font-semibold text-gray-900">Pengaturan Akun</h3>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800">Pengunduran Diri</h4>
                                    <p class="text-xs text-gray-600 mt-1">Nonaktifkan akun Anda secara permanen dari keanggotaan SEKAR.</p>
                                </div>
                                <button onclick="document.getElementById('resignModal').style.display='flex'"
                                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium">
                                    Undur Diri
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div id="resignModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Pengunduran Diri</h3>
                    <p class="text-sm text-gray-600 mt-1">Apakah Anda benar-benar yakin?</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-700">Tindakan ini akan menonaktifkan akun Anda secara permanen. Data Anda akan dipindahkan ke arsip ex-anggota.</p>

            <div class="mt-4 flex items-start p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center h-5">
                    <input id="confirmResignCheckbox"
                           name="confirm_resign"
                           type="checkbox"
                           onclick="document.getElementById('confirmResignButton').disabled = !this.checked;"
                           class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="confirmResignCheckbox" class="font-medium text-yellow-800">Saya mengerti dan menyetujui konsekuensi dari tindakan ini.</label>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
            <button type="button"
                    onclick="document.getElementById('resignModal').style.display='none'"
                    class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                Batal
            </button>
            <form action="{{ route('profile.resign') }}" method="POST">
                @csrf
                <button type="submit"
                        id="confirmResignButton"
                        disabled
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition disabled:bg-red-300 disabled:cursor-not-allowed">
                    Ya, Saya Yakin
                </button>
            </form>
        </div>
    </div>
</div>


<div id="profilePictureModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Update Foto Profil</h3>

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

<div id="historyModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Riwayat Perubahan Iuran
                    </h3>
                    <p class="text-sm text-gray-600 mt-1" id="historyTotalCount">Memuat data...</p>
                </div>
                <button onclick="closeHistoryModal()"
                        class="text-gray-400 hover:text-gray-600 transition p-2 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
            <div id="historyLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-gray-600 mt-2">Memuat riwayat...</p>
            </div>

            <div id="historyContent" style="display: none;">
                </div>

            <div id="historyEmpty" style="display: none;" class="text-center py-12">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h4 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Riwayat</h4>
                <p class="text-gray-600 mb-4">Anda belum pernah mengubah iuran sukarela.</p>
                <button onclick="closeHistoryModal(); toggleIuranForm();"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Ubah Iuran Pertama
                </button>
            </div>

            <div id="historyPagination" style="display: none;" class="mt-6 border-t border-gray-200 pt-4">
                </div>
        </div>
    </div>
</div>

<div id="paymentModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Riwayat Pembayaran Iuran
                    </h3>
                    <p class="text-sm text-gray-600 mt-1" id="paymentTotalCount">Memuat data...</p>
                </div>
                <button onclick="closePaymentModal()"
                        class="text-gray-400 hover:text-gray-600 transition p-2 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
            <div id="paymentLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                <p class="text-gray-600 mt-2">Memuat riwayat pembayaran...</p>
            </div>

            <div id="paymentContent" style="display: none;">
                </div>

            <div id="paymentEmpty" style="display: none;" class="text-center py-12">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h4 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Riwayat Pembayaran</h4>
                <p class="text-gray-600 mb-4">Riwayat pembayaran iuran akan muncul setelah sistem payroll memproses pemotongan gaji bulanan.</p>
                <p class="text-sm text-gray-500">Pembayaran iuran dipotong otomatis dari gaji setiap tanggal 1.</p>
            </div>

            <div id="paymentPagination" style="display: none;" class="mt-6 border-t border-gray-200 pt-4">
                </div>
        </div>
    </div>
</div>

<script>
let currentHistoryPage = 1;
let totalHistoryPages = 0;
let currentPaymentPage = 1;
let totalPaymentPages = 0;

function toggleIuranForm() {
    const form = document.getElementById('iuranUpdateForm');
    const btn = document.getElementById('iuranToggleBtn');

    if (form.style.display === 'none') {
        form.style.display = 'block';
        btn.textContent = 'Batal';
    } else {
        form.style.display = 'none';
        // Reset button text based on pending status
        @if($pendingChange)
            btn.textContent = 'Ubah Nominal';
        @else
            btn.textContent = 'Ubah Iuran Sukarela';
        @endif
    }
}

// History Modal Functions
function openHistoryModal() {
    document.getElementById('historyModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    loadHistoryData(1);
}

function closeHistoryModal() {
    document.getElementById('historyModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    resetHistoryModal();
}

function resetHistoryModal() {
    document.getElementById('historyLoading').style.display = 'block';
    document.getElementById('historyContent').style.display = 'none';
    document.getElementById('historyEmpty').style.display = 'none';
    document.getElementById('historyPagination').style.display = 'none';
    currentHistoryPage = 1;
}

// NEW: Payment Modal Functions
function openPaymentModal() {
    document.getElementById('paymentModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    loadPaymentData(1);
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    resetPaymentModal();
}

function resetPaymentModal() {
    document.getElementById('paymentLoading').style.display = 'block';
    document.getElementById('paymentContent').style.display = 'none';
    document.getElementById('paymentEmpty').style.display = 'none';
    document.getElementById('paymentPagination').style.display = 'none';
    currentPaymentPage = 1;
}

function loadHistoryData(page = 1) {
    document.getElementById('historyLoading').style.display = 'block';
    document.getElementById('historyContent').style.display = 'none';
    document.getElementById('historyEmpty').style.display = 'none';
    document.getElementById('historyPagination').style.display = 'none';

    fetch(`{{ route('profile.history') }}?page=${page}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderHistoryData(data.data);
        } else {
            console.error('Error loading history data:', data.message);
            showHistoryError();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showHistoryError();
    });
}

// NEW: Load Payment Data
function loadPaymentData(page = 1) {
    document.getElementById('paymentLoading').style.display = 'block';
    document.getElementById('paymentContent').style.display = 'none';
    document.getElementById('paymentEmpty').style.display = 'none';
    document.getElementById('paymentPagination').style.display = 'none';

    fetch(`{{ route('profile.payment-history') }}?page=${page}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderPaymentData(data.data);
        } else {
            console.error('Error loading payment data:', data.message);
            showPaymentError();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showPaymentError();
    });
}

function showHistoryError() {
    document.getElementById('historyLoading').style.display = 'none';
    document.getElementById('historyContent').innerHTML = `
        <div class="text-center py-8">
            <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h4 class="text-lg font-medium text-gray-900 mb-2">Gagal Memuat Data</h4>
            <p class="text-gray-600 mb-4">Terjadi kesalahan saat memuat riwayat perubahan iuran.</p>
            <button onclick="loadHistoryData(${currentHistoryPage})"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Coba Lagi
            </button>
        </div>
    `;
    document.getElementById('historyContent').style.display = 'block';
}

// NEW: Show Payment Error
function showPaymentError() {
    document.getElementById('paymentLoading').style.display = 'none';
    document.getElementById('paymentContent').innerHTML = `
        <div class="text-center py-8">
            <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h4 class="text-lg font-medium text-gray-900 mb-2">Gagal Memuat Data</h4>
            <p class="text-gray-600 mb-4">Terjadi kesalahan saat memuat riwayat pembayaran iuran.</p>
            <button onclick="loadPaymentData(${currentPaymentPage})"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Coba Lagi
            </button>
        </div>
    `;
    document.getElementById('paymentContent').style.display = 'block';
}

function renderHistoryData(data) {
    document.getElementById('historyLoading').style.display = 'none';

    if (data.items.length === 0) {
        document.getElementById('historyEmpty').style.display = 'block';
        return;
    }

    document.getElementById('historyTotalCount').textContent = `Total: ${data.totalItems} perubahan`;

    const contentHTML = data.items.map(item => `
        <div class="border-l-4 border-${item.statusColor}-400 pl-4 py-3 bg-${item.statusColor}-50 rounded-r mb-3">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${item.statusColor}-100 text-${item.statusColor}-800">
                            ${item.statusText}
                        </span>
                        <span class="text-sm font-medium text-gray-900">
                            ${item.jenis} - Rp ${item.nominalLama.toLocaleString('id-ID')} → Rp ${item.nominalBaru.toLocaleString('id-ID')}
                        </span>
                    </div>
                    <p class="text-xs text-gray-600 mb-2">
                        ${item.keterangan}
                    </p>
                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Diajukan: ${new Date(item.tglPerubahan).toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}
                        </span>
                        ${getStatusDateInfo(item)}
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    document.getElementById('historyContent').innerHTML = contentHTML;
    document.getElementById('historyContent').style.display = 'block';

    renderPagination(data, 'history');
    document.getElementById('historyPagination').style.display = 'block';

    currentHistoryPage = data.currentPage;
    totalHistoryPages = data.totalPages;
}

// NEW: Render Payment Data
function renderPaymentData(data) {
    document.getElementById('paymentLoading').style.display = 'none';

    if (data.items.length === 0) {
        document.getElementById('paymentEmpty').style.display = 'block';
        return;
    }

    document.getElementById('paymentTotalCount').textContent = `Total: ${data.totalItems} periode pembayaran`;

    const contentHTML = data.items.map(item => `
        <div class="border-l-4 border-${item.statusColor}-400 pl-4 py-3 bg-${item.statusColor}-50 rounded-r mb-3">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${item.statusColor}-100 text-${item.statusColor}-800">
                            ${item.statusText}
                        </span>
                        <span class="text-sm font-medium text-gray-900">
                            ${item.bulanNama} ${item.tahun}
                        </span>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-xs text-gray-600 mb-2">
                        <div>
                            <span class="font-medium">Iuran Wajib:</span><br>
                            <span class="text-gray-900">Rp ${item.iuranWajib.toLocaleString('id-ID')}</span>
                        </div>
                        <div>
                            <span class="font-medium">Iuran Sukarela:</span><br>
                            <span class="text-gray-900">Rp ${item.iuranSukarela.toLocaleString('id-ID')}</span>
                        </div>
                        <div>
                            <span class="font-medium">Total:</span><br>
                            <span class="text-gray-900 font-semibold">Rp ${item.totalIuran.toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                        ${item.tglBayar ? `
                            <span class="flex items-center text-green-600">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Dipotong Payroll: ${new Date(item.tglBayar).toLocaleDateString('id-ID', {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric'
                                })}
                            </span>
                        ` : `
                            <span class="flex items-center text-blue-600">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Akan dipotong dari gaji
                            </span>
                        `}
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    document.getElementById('paymentContent').innerHTML = contentHTML;
    document.getElementById('paymentContent').style.display = 'block';

    renderPagination(data, 'payment');
    document.getElementById('paymentPagination').style.display = 'block';

    currentPaymentPage = data.currentPage;
    totalPaymentPages = data.totalPages;
}

function getStatusDateInfo(item) {
    switch(item.status) {
        case 'PENDING':
            return `<span class="text-yellow-600 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Target: ${new Date(item.tglImplementasi).toLocaleDateString('id-ID', {month: 'short'})}
            </span>`;
        case 'IMPLEMENTED':
            return `<span class="text-green-600 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Diterapkan: ${new Date(item.tglImplementasi).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}
            </span>`;
        case 'DIBATALKAN':
            return `<span class="text-red-600 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Dibatalkan: ${new Date(item.tglPerubahan).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
            </span>`;
        default:
            return '';
    }
}

function renderPagination(data, type) {
    const { currentPage, totalPages, firstItem, lastItem, totalItems } = data;
    const paginationId = type === 'history' ? 'historyPagination' : 'paymentPagination';
    const loadFunction = type === 'history' ? 'loadHistoryData' : 'loadPaymentData';

    let paginationHTML = `
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Menampilkan ${firstItem} - ${lastItem} dari ${totalItems} ${type === 'history' ? 'perubahan' : 'periode'}
            </div>
            <div class="flex items-center space-x-1">
    `;

    // Previous button
    if (currentPage === 1) {
        paginationHTML += `
            <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Prev
            </span>
        `;
    } else {
        paginationHTML += `
            <button onclick="${loadFunction}(${currentPage - 1})"
                    class="px-3 py-1 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Prev
            </button>
        `;
    }

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            paginationHTML += `
                <span class="px-3 py-1 text-sm text-white bg-${type === 'history' ? 'blue' : 'indigo'}-600 rounded-md font-medium">${i}</span>
            `;
        } else if (i <= 3 || i > totalPages - 3 || Math.abs(i - currentPage) <= 1) {
            paginationHTML += `
                <button onclick="${loadFunction}(${i})"
                        class="px-3 py-1 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                    ${i}
                </button>
            `;
        } else if ((i === 4 && currentPage > 5) || (i === totalPages - 3 && currentPage < totalPages - 4)) {
            paginationHTML += `<span class="px-2 py-1 text-sm text-gray-400">...</span>`;
        }
    }

    // Next button
    if (currentPage === totalPages) {
        paginationHTML += `
            <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">
                Next
                <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        `;
    } else {
        paginationHTML += `
            <button onclick="${loadFunction}(${currentPage + 1})"
                    class="px-3 py-1 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                Next
                <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        `;
    }

    paginationHTML += `</div></div>`;

    document.getElementById(paginationId).innerHTML = paginationHTML;
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    ['historyModal', 'paymentModal', 'profilePictureModal', 'resignModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });

    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = ['historyModal', 'paymentModal', 'profilePictureModal', 'resignModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && (modal.style.display === 'flex' || modal.style.display === 'block')) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });
});
</script>
@endsection