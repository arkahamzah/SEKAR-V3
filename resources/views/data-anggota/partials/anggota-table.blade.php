@php
    $isSuperAdmin = Auth::check() && Auth::user()->hasRole('ADM');
@endphp

<div class="border-b border-gray-200">
    @if($isSuperAdmin)
    <div class="px-6 pt-6 pb-4 flex justify-end">
        <a href="{{ route('data-anggota.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Tambah Anggota
        </a>
    </div>
    @endif

    <div class="p-6 pt-2">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">No</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Nama</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">NIK</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Lokasi</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">DPW</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">DPD</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Iuran Wajib</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Iuran Sukarela</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Tgl Terdaftar</th>
                    @if($isSuperAdmin)
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($anggota as $member)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-4 text-xs text-gray-600">{{ ($anggota->currentPage() - 1) * $anggota->perPage() + $loop->iteration }}</td>
                    <td class="py-3 px-4 text-xs font-medium text-gray-900">{{ $member->V_NAMA_KARYAWAN }}</td>
                    <td class="py-3 px-4 text-xs text-gray-600">{{ $member->N_NIK }}</td>
                    <td class="py-3 px-4 text-xs text-gray-600">{{ $member->V_KOTA_GEDUNG }}</td>
                    <td class="py-3 px-4 text-xs text-gray-600">{{ $member->DPW }}</td>
                    <td class="py-3 px-4 text-xs text-gray-600">{{ $member->DPD }}</td>
                    <td class="py-3 px-4 text-xs text-gray-600">{{ 'Rp ' . number_format($member->IURAN_WAJIB, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-xs text-gray-600">{{ 'Rp ' . number_format($member->IURAN_SUKARELA, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-xs text-gray-600">{{ $member->TGL_TERDAFTAR ? \Carbon\Carbon::parse($member->TGL_TERDAFTAR)->format('d M Y') : '-' }}</td>
                    @if($isSuperAdmin)
                    <td class="py-3 px-4 text-xs">
                        <div class="flex items-center space-x-2">
                            {{-- PERBAIKAN: Menggunakan N_NIK dari objek $member --}}
                            <a href="{{ route('data-anggota.edit', ['nik' => $member->N_NIK]) }}" 
                               class="inline-flex items-center p-2 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200" title="Edit Anggota">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <button onclick="confirmDelete('{{ $member->N_NIK }}', '{{ addslashes($member->V_NAMA_KARYAWAN) }}')" 
                                    class="inline-flex items-center p-2 bg-red-100 text-red-800 rounded-md hover:bg-red-200" title="Hapus Anggota">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $isSuperAdmin ? '10' : '9' }}" class="py-12 px-4 text-center text-gray-500 text-sm">
                        Tidak ada data anggota yang ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-pagination-links :paginator="$anggota" />

@if($isSuperAdmin)
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <div class="flex flex-col items-center text-center">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Konfirmasi Menonaktifkan Anggota</h3>
            <p class="text-sm text-gray-500 mb-4">
                Apakah Anda yakin ingin menonaktifkan anggota <strong id="memberName" class="font-semibold"></strong>?
            </p>
            <p class="text-xs text-gray-500 mb-6">
                Anggota akan dipindahkan ke daftar ex-anggota dan tidak dapat login kembali.
            </p>
        </div>
        
        <div class="flex justify-center space-x-3">
            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                Batal
            </button>
            <form id="deleteForm" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                    Ya, Nonaktifkan
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(nik, nama) {
    document.getElementById('memberName').textContent = nama;
    document.getElementById('deleteForm').action = "{{ url('data-anggota') }}/" + nik;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endif