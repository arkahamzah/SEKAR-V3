@extends('layouts.app')

@section('title', 'Pengaturan - SEKAR')

@section('content')
<style>
    .select2-container--default .select2-selection--single {
        background-color: #fff;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        height: 42px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111827;
        line-height: 42px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 8px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #3b82f6;
    }
    .select2-dropdown {
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
    }
</style>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Alert Messages --}}
        @if(session('success'))
            <div id="successAlert" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 alert">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <p class="font-bold">Terjadi kesalahan:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Pengaturan</h1>
            <p class="text-gray-600 text-sm mt-1">Kelola riwayat tanda tangan sertifikat dan dokumen penting lainnya</p>
        </div>

        @if(Auth::user()->hasRole('ADM'))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Riwayat Tanda Tangan Sertifikat</h3>
                <p class="text-sm text-gray-600 mt-1">Data yang ditambahkan akan digunakan untuk sertifikat anggota sesuai periode.</p>
            </div>
            <form method="POST" action="{{ route('setting.signature.store') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="jajaran_id" class="block text-sm font-medium text-gray-700 mb-2">Pilih Pejabat</label>
                        {{-- Dropdown pencarian pejabat --}}
                        <select id="jajaran_id" name="jajaran_id" class="w-full" required></select>
                        <p class="text-xs text-gray-500 mt-1">Cari berdasarkan nama pejabat yang aktif.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jabatan</label>
                        <input type="text" id="jabatan_display" placeholder="Jabatan akan terisi otomatis" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed" readonly>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai Berlaku</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir Berlaku</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unggah File Tanda Tangan (PNG)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                        <input type="file" name="signature_file" accept="image/png" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Gunakan format PNG dengan background transparan. Maksimal 2MB.</p>
                </div>
                <div class="flex justify-end pt-6 mt-6 border-t border-gray-200">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                        Simpan Tanda Tangan
                    </button>
                </div>
            </form>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
             <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Riwayat Tanda Tangan</h3>
             </div>
             <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Pejabat</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Jabatan</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Periode</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Tanda Tangan</th>
                                @if(Auth::user()->hasRole('ADM'))
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($signatures as $sign)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $sign->nama_pejabat }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $sign->jabatan }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $sign->start_date->format('d M Y') }} - {{ $sign->end_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <img src="{{ asset('storage/signatures/' . $sign->signature_file) }}" alt="Tanda Tangan" class="h-10 inline-block bg-gray-100 p-1 rounded">
                                </td>
                                @if(Auth::user()->hasRole('ADM'))
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="openEditModal({{ $sign->id }})" class="text-yellow-600 hover:text-yellow-900 p-1" title="Edit">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path></svg>
                                        </button>
                                        <button onclick="openDeleteModal({{ $sign->id }}, '{{ addslashes($sign->nama_pejabat) }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                                             <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        </button>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-sm text-gray-500">Belum ada riwayat tanda tangan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
             </div>
        </div>

        @if(Auth::user()->hasRole('ADM'))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pengaturan Dokumen</h3>
                <p class="text-sm text-gray-600 mt-1">Unggah dan kelola dokumen penting untuk anggota (misal: PKB, AD/ART).</p>
            </div>

            <form method="POST" action="{{ route('setting.document.upload') }}" enctype="multipart/form-data" class="p-6 border-b border-gray-200">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="document_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Dokumen <span class="text-red-500">*</span></label>
                        <input type="text" name="document_name" id="document_name" required placeholder="Contoh: Perjanjian Kerja Bersama 2024" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="document_file" class="block text-sm font-medium text-gray-700 mb-2">File (PDF) <span class="text-red-500">*</span></label>
                        <input type="file" name="document_file" id="document_file" accept=".pdf" required class="w-full text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-500 mt-1">Maksimal ukuran: 5MB.</p>
                    </div>
                </div>
                <div class="flex justify-end pt-4 mt-4">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-medium">
                        Upload Dokumen
                    </button>
                </div>
            </form>
            
            <div class="p-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Daftar Dokumen Tersimpan</h4>
                <div class="space-y-3">
                    @forelse($documents as $doc)
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0011.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $doc['name'] }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $doc['size'] }} - Diunggah: {{ $doc['uploaded_at'] }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                             <a href="{{ route('dokumen.show', $doc['filename']) }}" target="_blank" class="text-blue-600 hover:text-blue-800 p-1 rounded-md hover:bg-blue-100" title="Lihat">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                            <form action="{{ route('setting.document.delete', $doc['filename']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 p-1 rounded-md hover:bg-red-100" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-sm text-gray-500">
                        Belum ada dokumen yang diunggah.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="text-lg font-semibold">Edit Tanda Tangan</h3>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        </div>
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="edit_nama_pejabat" class="block text-sm font-medium text-gray-700">Nama Pejabat</label>
                    <input type="text" id="edit_nama_pejabat" name="nama_pejabat" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                 <div>
                    <label for="edit_jabatan" class="block text-sm font-medium text-gray-700">Jabatan</label>
                    <input type="text" id="edit_jabatan" name="jabatan" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit_start_date" class="block text-sm font-medium text-gray-700">Mulai Berlaku</label>
                        <input type="date" id="edit_start_date" name="start_date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="edit_end_date" class="block text-sm font-medium text-gray-700">Akhir Berlaku</label>
                        <input type="date" id="edit_end_date" name="end_date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                 <div>
                    <label for="edit_signature_file" class="block text-sm font-medium text-gray-700">Ganti File Tanda Tangan (Opsional)</label>
                    <input type="file" id="edit_signature_file" name="signature_file" accept="image/png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeEditModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full">
         <h3 class="text-lg font-semibold text-center">Konfirmasi Hapus</h3>
         <p class="text-center text-gray-600 my-4">Apakah Anda yakin ingin menghapus data tanda tangan untuk <strong id="deleteObjectName"></strong>?</p>
         <div class="flex justify-center space-x-4">
             <button onclick="closeDeleteModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">Batal</button>
             <form id="deleteForm" method="POST">
                 @csrf
                 @method('DELETE')
                 <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Ya, Hapus</button>
             </form>
         </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#jajaran_id').select2({
            placeholder: 'Ketik untuk mencari nama pejabat...',
            ajax: {
                url: '{{ route("setting.jajaran.search") }}',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        $('#jajaran_id').on('select2:select', function (e) {
            var data = e.params.data;
            $('#jabatan_display').val(data.jabatan);
        });
    });

    function openEditModal(id) {
        fetch(`/setting/signature/${id}/edit`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('edit_nama_pejabat').value = data.nama_pejabat;
                document.getElementById('edit_jabatan').value = data.jabatan;
                document.getElementById('edit_start_date').value = data.start_date.split('T')[0];
                document.getElementById('edit_end_date').value = data.end_date.split('T')[0];
                
                const form = document.getElementById('editForm');
                form.action = `/setting/signature/${id}`;
                
                document.getElementById('editModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error fetching signature data:', error);
                alert('Gagal memuat data. Silakan coba lagi.');
            });
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function openDeleteModal(id, name) {
        const modal = document.getElementById('deleteModal');
        modal.querySelector('#deleteObjectName').textContent = name;
        const form = modal.querySelector('#deleteForm');
        form.action = `/setting/signature/${id}`;
        modal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>
@endsection