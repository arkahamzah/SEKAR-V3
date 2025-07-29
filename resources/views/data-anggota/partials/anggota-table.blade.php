<table class="w-full">
    <thead class="bg-gray-50">
        <tr>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NIK</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NAMA</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NO TELP</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Tanggal Terdaftar</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Iuran Wajib</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Iuran Sukarela</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">DPW</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @forelse($anggota as $member)
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->NIK }}</td>
            <td class="py-3 px-4 text-xs font-medium text-gray-900">{{ $member->NAMA }}</td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->NO_TELP }}</td>
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
        </tr>
        @empty
        <tr>
            <td colspan="7" class="py-12 px-4 text-center text-gray-500 text-sm">
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