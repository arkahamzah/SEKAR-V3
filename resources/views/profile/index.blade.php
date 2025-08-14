@extends('layouts.app')

@section('title', 'Profile - SEKAR')

@section('content')
<style>
    /* Custom styles for the redesigned profile page */
    .profile-card {
        transition: all 0.2s ease-in-out;
    }
    .accordion-header {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .accordion-header:hover {
        background-color: #f9fafb;
    }
    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out, padding 0.3s ease-out;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
    .accordion-content.open {
        max-height: 500px; /* Increased height for forms */
        padding-top: 1rem;
        padding-bottom: 1rem;
        transition: max-height 0.4s ease-in, padding 0.4s ease-in;
    }
    .accordion-icon {
        transition: transform 0.3s ease;
    }
    .accordion-header.open .accordion-icon {
        transform: rotate(180deg);
    }
</style>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <div class="space-y-3 mb-6">
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 text-green-700 px-4 py-3 rounded-r-lg text-sm shadow-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-r-lg text-sm shadow-sm">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-r-lg text-sm shadow-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 profile-card flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6">
            <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center border-4 border-white shadow-md">
                <span class="text-3xl font-bold text-blue-600">{{ substr($user->name, 0, 1) }}</span>
            </div>
            <div class="text-center sm:text-left">
                <h1 class="text-xl font-bold text-gray-900">{{ $user->name }}</h1>
                <p class="text-gray-600 text-sm">NIK: {{ $user->nik }}</p>
                @if($karyawan)
                    <p class="text-gray-600 text-sm">{{ $karyawan->V_SHORT_POSISI }}</p>
                @endif
            </div>
        </div>

        <div class="space-y-6">

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 profile-card">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Dasar</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><label class="text-gray-500 font-medium">Nama Lengkap</label><p class="text-gray-800 font-semibold mt-1">{{ $user->name }}</p></div>
                    <div><label class="text-gray-500 font-medium">NIK</label><p class="text-gray-800 font-semibold mt-1">{{ $user->nik }}</p></div>
                    @if($karyawan)
                    <div><label class="text-gray-500 font-medium">Posisi</label><p class="text-gray-800 font-semibold mt-1">{{ $karyawan->V_SHORT_POSISI }}</p></div>
                    <div><label class="text-gray-500 font-medium">Unit Kerja</label><p class="text-gray-800 font-semibold mt-1">{{ $karyawan->V_SHORT_UNIT }}</p></div>
                    <div class="sm:col-span-2"><label class="text-gray-500 font-medium">Divisi</label><p class="text-gray-800 font-semibold mt-1">{{ $karyawan->V_SHORT_DIVISI }}</p></div>
                    <div><label class="text-gray-500 font-medium">Lokasi</label><p class="text-gray-800 font-semibold mt-1">{{ $karyawan->V_KOTA_GEDUNG }}</p></div>
                    <div><label class="text-gray-500 font-medium">DPW</label><p class="text-gray-800 font-semibold mt-1">{{ $karyawan->DPW }}</p></div>
                    <div><label class="text-gray-500 font-medium">DPD</label><p class="text-gray-800 font-semibold mt-1">{{ $karyawan->DPD }}</p></div>
                    @endif
                    <div><label class="text-gray-500 font-medium">Tanggal Bergabung</label><p class="text-gray-800 font-semibold mt-1">{{ $joinDate->format('d F Y') }}</p></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 profile-card">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Informasi Iuran</h3></div>
                <div class="p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center mb-6">
                        <div class="bg-blue-50 p-3 rounded-lg"><p class="text-blue-600 font-medium text-xs sm:text-sm">Iuran Wajib</p><p class="text-blue-900 font-bold text-base sm:text-lg">Rp {{ number_format($iuranWajib, 0, ',', '.') }}</p></div>
                        <div class="bg-green-50 p-3 rounded-lg"><p class="text-green-600 font-medium text-xs sm:text-sm">Iuran Sukarela</p><p class="text-green-900 font-bold text-base sm:text-lg">Rp {{ number_format($effectiveIuranSukarela, 0, ',', '.') }}</p></div>
                        <div class="bg-purple-50 p-3 rounded-lg"><p class="text-purple-600 font-medium text-xs sm:text-sm">Total/Bulan</p><p class="text-purple-900 font-bold text-base sm:text-lg">Rp {{ number_format($totalIuranPerBulan, 0, ',', '.') }}</p></div>
                        <div class="bg-indigo-50 p-3 rounded-lg"><p class="text-indigo-600 font-medium text-xs sm:text-sm">Total Terbayar</p><p class="text-indigo-900 font-bold text-base sm:text-lg">Rp {{ number_format($totalIuranTerbayar, 0, ',', '.') }}</p></div>
                    </div>
                    @if($pendingChange)
                        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                            <strong>Perubahan Iuran diproses:</strong> Sukarela akan menjadi <strong>Rp {{ number_format($pendingChange->NOMINAL_BARU, 0, ',', '.') }}</strong> pada {{ $pendingChange->TGL_IMPLEMENTASI->translatedFormat('F Y') }}.
                        </div>
                    @endif
                    <div class="border border-gray-200 rounded-lg">
                        <div class="accordion-header px-6 py-4 flex justify-between items-center" onclick="toggleAccordion(this)">
                            <h4 class="font-semibold text-gray-800">Ubah Iuran Sukarela</h4>
                            <svg class="w-5 h-5 text-gray-500 accordion-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        <div class="accordion-content border-t border-gray-200">
                             <form method="POST" action="{{ route('profile.update-iuran') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="iuran_sukarela" class="block text-sm font-medium text-gray-700">Nominal Iuran Sukarela Baru</label>
                                    <input type="number" name="iuran_sukarela" id="iuran_sukarela" value="{{ old('iuran_sukarela', $effectiveIuranSukarela) }}" min="0" step="5000" class="mt-1 w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="0">
                                    <p class="text-xs text-gray-500 mt-1">Gunakan kelipatan Rp 5.000. Perubahan akan diterapkan pada bulan berikutnya.</p>
                                </div>
                                <div class="flex space-x-3">
                                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition">Ajukan Perubahan</button>
                                    @if($pendingChange)
                                        <button type="button" onclick="document.getElementById('cancelChangeForm').submit()" class="flex-1 bg-red-500 text-white px-4 py-2 rounded-md text-sm hover:bg-red-600 transition">Batalkan Pending</button>
                                    @endif
                                </div>
                            </form>
                            @if($pendingChange)
                                <form id="cancelChangeForm" method="POST" action="{{ route('profile.cancel-iuran') }}" onsubmit="return confirm('Yakin ingin membatalkan perubahan iuran?')" class="hidden">@csrf @method('DELETE')</form>
                            @endif
                        </div>
                        <div class="accordion-header px-6 py-4 flex justify-between items-center border-t border-gray-200" onclick="openHistoryModal();"><h4 class="font-semibold text-gray-800">Lihat Riwayat Perubahan Iuran</h4><svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg></div>
                        <div class="accordion-header px-6 py-4 flex justify-between items-center border-t border-gray-200" onclick="openPaymentModal();"><h4 class="font-semibold text-gray-800">Lihat Riwayat Pembayaran</h4><svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 profile-card">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Pengaturan Akun</h3></div>
                <div class="p-6"><div class="flex items-center justify-between"><div><h4 class="text-sm font-semibold text-gray-800">Pengunduran Diri</h4><p class="text-xs text-gray-600 mt-1">Nonaktifkan akun Anda secara permanen dari keanggotaan SEKAR.</p></div><button onclick="document.getElementById('resignModal').style.display='flex'" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-medium">Undur Diri</button></div></div>
            </div>
        </div>
    </div>
</div>

<div id="resignModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center"><svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg></div>
                <div class="ml-4"><h3 class="text-lg font-semibold text-gray-900">Konfirmasi Pengunduran Diri</h3><p class="text-sm text-gray-600 mt-1">Apakah Anda benar-benar yakin?</p></div>
            </div>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-700">Tindakan ini akan menonaktifkan akun Anda secara permanen. Data Anda akan dipindahkan ke arsip ex-anggota.</p>
            <div class="mt-4 flex items-start p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center h-5"><input id="confirmResignCheckbox" name="confirm_resign" type="checkbox" onclick="document.getElementById('confirmResignButton').disabled = !this.checked;" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded"></div>
                <div class="ml-3 text-sm"><label for="confirmResignCheckbox" class="font-medium text-yellow-800">Saya mengerti dan menyetujui konsekuensi dari tindakan ini.</label></div>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
            <button type="button" onclick="document.getElementById('resignModal').style.display='none'" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition">Batal</button>
            <form action="{{ route('profile.resign') }}" method="POST">@csrf<button type="submit" id="confirmResignButton" disabled class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition disabled:bg-red-300 disabled:cursor-not-allowed">Ya, Saya Yakin</button></form>
        </div>
    </div>
</div>

<div id="historyModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200"><div class="flex items-center justify-between"><div><h3 class="text-lg font-semibold text-gray-900">Riwayat Perubahan Iuran</h3><p class="text-sm text-gray-600 mt-1" id="historyTotalCount">Memuat data...</p></div><button onclick="closeHistoryModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-full"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div></div>
        <div class="p-6 overflow-y-auto flex-grow"><div id="historyLoading" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="text-gray-600 mt-2">Memuat riwayat...</p></div><div id="historyContent" style="display: none;"></div><div id="historyEmpty" style="display: none;" class="text-center py-12"><h4 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Riwayat</h4><p class="text-gray-600">Anda belum pernah mengubah iuran.</p></div></div>
        <div id="historyPagination" style="display: none;" class="px-6 py-4 border-t border-gray-200 bg-gray-50"></div>
    </div>
</div>

<div id="paymentModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200"><div class="flex items-center justify-between"><div><h3 class="text-lg font-semibold text-gray-900">Riwayat Pembayaran Iuran</h3><p class="text-sm text-gray-600 mt-1" id="paymentTotalCount">Memuat data...</p></div><button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-full"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div></div>
        <div class="p-6 overflow-y-auto flex-grow"><div id="paymentLoading" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div><p class="text-gray-600 mt-2">Memuat riwayat pembayaran...</p></div><div id="paymentContent" style="display: none;"></div><div id="paymentEmpty" style="display: none;" class="text-center py-12"><h4 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Riwayat Pembayaran</h4><p class="text-gray-600">Data pembayaran akan muncul di sini.</p></div></div>
        <div id="paymentPagination" style="display: none;" class="px-6 py-4 border-t border-gray-200 bg-gray-50"></div>
    </div>
</div>

<script>
    // FUNGSI JAVASCRIPT LENGKAP DARI KODE ORIGINAL ANDA
    function toggleAccordion(header) {
        const content = header.nextElementSibling;
        const allContents = document.querySelectorAll('.accordion-content');

        const wasOpen = content.classList.contains('open');

        allContents.forEach(item => {
            item.classList.remove('open');
            item.previousElementSibling.classList.remove('open');
        });

        if (!wasOpen) {
            header.classList.add('open');
            content.classList.add('open');
        }
    }

    let currentHistoryPage = 1, totalHistoryPages = 0;
    let currentPaymentPage = 1, totalPaymentPages = 0;

    function openHistoryModal() {
        document.getElementById('historyModal').style.display = 'flex'; document.body.style.overflow = 'hidden'; loadHistoryData(1);
    }
    function closeHistoryModal() {
        document.getElementById('historyModal').style.display = 'none'; document.body.style.overflow = 'auto';
    }
    function openPaymentModal() {
        document.getElementById('paymentModal').style.display = 'flex'; document.body.style.overflow = 'hidden'; loadPaymentData(1);
    }
    function closePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none'; document.body.style.overflow = 'auto';
    }

    async function fetchData(url, type) {
        const loadingDiv = document.getElementById(`${type}Loading`);
        const contentDiv = document.getElementById(`${type}Content`);
        const emptyDiv = document.getElementById(`${type}Empty`);

        loadingDiv.style.display = 'block';
        contentDiv.style.display = 'none';
        emptyDiv.style.display = 'none';

        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();

            if (data.success) {
                if (type === 'history') renderHistoryData(data.data);
                if (type === 'payment') renderPaymentData(data.data);
            } else {
                contentDiv.innerHTML = `<p class="text-red-500 text-center">Gagal memuat data: ${data.message}</p>`;
                contentDiv.style.display = 'block';
            }
        } catch (error) {
            console.error(`Error fetching ${type}:`, error);
            contentDiv.innerHTML = '<p class="text-red-500 text-center">Terjadi kesalahan koneksi.</p>';
            contentDiv.style.display = 'block';
        } finally {
            loadingDiv.style.display = 'none';
        }
    }

    function loadHistoryData(page = 1) { fetchData(`{{ route('profile.history') }}?page=${page}`, 'history'); }
    function loadPaymentData(page = 1) { fetchData(`{{ route('profile.payment-history') }}?page=${page}`, 'payment'); }

    function renderHistoryData(data) {
        const contentDiv = document.getElementById('historyContent');
        const emptyDiv = document.getElementById('historyEmpty');
        document.getElementById('historyTotalCount').textContent = `Total: ${data.totalItems} perubahan`;

        if (data.items.length === 0) {
            emptyDiv.style.display = 'block';
            return;
        }

        contentDiv.style.display = 'block';
        contentDiv.innerHTML = data.items.map(item => `
            <div class="border-l-4 border-${item.statusColor}-400 pl-4 py-3 bg-${item.statusColor}-50 rounded-r mb-3">
                <div class="flex items-center justify-between"><div><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${item.statusColor}-100 text-${item.statusColor}-800">${item.statusText}</span><p class="text-sm font-medium text-gray-900 mt-1">Rp ${item.nominalLama.toLocaleString('id-ID')} → Rp ${item.nominalBaru.toLocaleString('id-ID')}</p><p class="text-xs text-gray-600 mt-1">${item.keterangan || ''}</p></div><div class="text-xs text-gray-500 text-right"><p>Diajukan: ${new Date(item.tglPerubahan).toLocaleDateString('id-ID')}</p><p>Implementasi: ${new Date(item.tglImplementasi).toLocaleDateString('id-ID')}</p></div></div></div>`).join('');
        renderPagination(data, 'history');
    }

    function renderPaymentData(data) {
        const contentDiv = document.getElementById('paymentContent');
        const emptyDiv = document.getElementById('paymentEmpty');
        document.getElementById('paymentTotalCount').textContent = `Total: ${data.totalItems} periode pembayaran`;

        if (data.items.length === 0) {
            emptyDiv.style.display = 'block';
            return;
        }

        contentDiv.style.display = 'block';
        contentDiv.innerHTML = `<table class="w-full text-sm text-left text-gray-500"><thead class="text-xs text-gray-700 uppercase bg-gray-50"><tr><th scope="col" class="px-4 py-3">Periode</th><th scope="col" class="px-4 py-3 text-right">Total Iuran</th><th scope="col" class="px-4 py-3 text-center">Status</th></tr></thead><tbody>${data.items.map(item => `<tr class="bg-white border-b hover:bg-gray-50"><td class="px-4 py-3 font-medium text-gray-900">${item.bulanNama} ${item.tahun}</td><td class="px-4 py-3 text-right font-semibold">Rp ${item.totalIuran.toLocaleString('id-ID')}</td><td class="px-4 py-3 text-center"><span class="px-2 py-1 text-xs font-medium rounded-full bg-${item.statusColor}-100 text-${item.statusColor}-800">${item.statusText}</span></td></tr>`).join('')}</tbody></table>`;
        renderPagination(data, 'payment');
    }

    function renderPagination(data, type) {
        const { currentPage, totalPages, totalItems, firstItem, lastItem } = data;
        const paginationId = type === 'history' ? 'historyPagination' : 'paymentPagination';
        const loadFunction = type === 'history' ? 'loadHistoryData' : 'loadPaymentData';
        const paginationDiv = document.getElementById(paginationId);

        if (totalPages <= 1) { paginationDiv.style.display = 'none'; return; }
        paginationDiv.style.display = 'flex';
        let html = `<div class="flex items-center justify-between w-full"><div class="text-sm text-gray-700">Menampilkan ${firstItem}-${lastItem} dari ${totalItems}</div><div class="flex items-center space-x-1"><button onclick="${loadFunction}(${currentPage - 1})" class="px-3 py-1 text-sm rounded-md border ${currentPage === 1 ? 'bg-gray-100 cursor-not-allowed' : 'bg-white hover:bg-gray-50'}" ${currentPage === 1 ? 'disabled' : ''}>Prev</button><span class="px-3 py-1 text-sm">${currentPage} / ${totalPages}</span><button onclick="${loadFunction}(${currentPage + 1})" class="px-3 py-1 text-sm rounded-md border ${currentPage === totalPages ? 'bg-gray-100 cursor-not-allowed' : 'bg-white hover:bg-gray-50'}" ${currentPage === totalPages ? 'disabled' : ''}>Next</button></div></div>`;
        paginationDiv.innerHTML = html;
    }

    document.addEventListener('DOMContentLoaded', function() {
        ['historyModal', 'paymentModal', 'resignModal'].forEach(modalId => {
            const modal = document.getElementById(modalId);
            if(modal) modal.addEventListener('click', e => { if (e.target === modal) { e.target.style.display = 'none'; document.body.style.overflow = 'auto'; } });
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                ['historyModal', 'paymentModal', 'resignModal'].forEach(id => {
                    const modal = document.getElementById(id);
                    if(modal) modal.style.display = 'none';
                });
                document.body.style.overflow = 'auto';
            }
        });
    });
</script>
@endsection