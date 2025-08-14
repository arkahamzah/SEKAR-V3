@php
    // Check if current user is super admin
    $isSuperAdmin = false;
    if (Auth::check()) {
        $isSuperAdmin = DB::table('t_sekar_pengurus as sp')
            ->join('t_sekar_roles as sr', 'sp.ID_ROLES', '=', 'sr.ID')
            ->where('sp.N_NIK', Auth::user()->nik)
            ->where('sr.NAME', 'ADM')
            ->exists();
    }
@endphp

@if($isSuperAdmin)
<div class="mb-4 flex justify-end">
    <a href="{{ route('data-anggota.create') }}" 
       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Tambah Anggota
    </a>
</div>
@endif

<table class="w-full">
    <thead class="bg-gray-50">
        <tr>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NIK</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NAMA</th>
            {{-- UPDATED: Changed from NO TELP to LOKASI --}}
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">LOKASI</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Tanggal Terdaftar</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Iuran Wajib</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Iuran Sukarela</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">DPW</th>
            {{-- ADDED: DPD Column --}}
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">DPD</th>
            @if($isSuperAdmin)
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Aksi</th>
            @endif
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @forelse($anggota as $member)
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->NIK }}</td>
            <td class="py-3 px-4 text-xs font-medium text-gray-900">{{ $member->NAMA }}</td>
            {{-- UPDATED: Changed from NO_TELP to LOKASI --}}
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->LOKASI }}</td>
            <td class="py-3 px-4 text-xs text-gray-900">
                {{ $member->TANGGAL_TERDAFTAR ? \Carbon\Carbon::parse($member->TANGGAL_TERDAFTAR)->format('d-m-Y') : '-' }}
            </td>
            <td class="py-3 px-4 text-xs text-gray-900">
                {{ $member->IURAN_WAJIB ? 'Rp ' . number_format($member->IURAN_WAJIB, 0, ',', '.') : '-' }}
            </td>
            <td class="py-3 px-4 text-xs text-gray-900">
                {{ $member->IURAN_SUKARELA ? 'Rp ' . number_format($member->IURAN_SUKARELA, 0, ',', '.') : 'Rp 0' }}
            </td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->DPW }}</td>
            {{-- ADDED: DPD Column data --}}
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->DPD }}</td>
            @if($isSuperAdmin)
            <td class="py-3 px-4 text-xs">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('data-anggota.edit', $member->NIK) }}" 
                       class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded hover:bg-yellow-200 transition-colors"
                       title="Edit Anggota">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                </div>
            </td>
            @endif
        </tr>
        @empty
        <tr>
            {{-- UPDATED: Colspan adjusted for new column --}}
            <td colspan="{{ $isSuperAdmin ? '9' : '8' }}" class="py-12 px-4 text-center text-gray-500 text-sm">
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="text-gray-600 mb-1">Tidak ada data anggota yang ditemukan</p>
                    @if(request()->hasAny(['dpw', 'dpd', 'search']))
                        <p class="text-sm text-gray-500">Coba ubah kriteria pencarian Anda</p>
                    @endif
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if(isset($anggota) && $anggota->hasPages())
<div class="px-6 py-4 border-t border-gray-200">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Menampilkan {{ $anggota->firstItem() }} sampai {{ $anggota->lastItem() }} dari {{ $anggota->total() }} data
        </div>
        <div class="flex space-x-1">
            {{ $anggota->appends(request()->query())->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endif

{{-- The delete modal script does not need changes --}}
@if($isSuperAdmin)
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex items-center mb-4">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
        </div>
        
        <div class="text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Konfirmasi Hapus Anggota</h3>
            <p class="text-sm text-gray-500 mb-4">
                Apakah Anda yakin ingin menghapus anggota <strong id="memberName"></strong>?
            </p>
            <p class="text-xs text-red-600 mb-6">
                Anggota akan dipindahkan ke daftar ex-anggota dan tidak dapat login lagi.
            </p>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeDeleteModal()" 
                    class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                Batal
            </button>
            <form id="deleteForm" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                    Hapus Anggota
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(nik, nama) {
    const escapedNama = nama.replace(/'/g, "\\'");
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