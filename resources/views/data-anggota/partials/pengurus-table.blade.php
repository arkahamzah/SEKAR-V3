<table class="w-full">
    <thead class="bg-gray-50">
        <tr>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NIK</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NAMA</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NO TELP</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Tanggal Terdaftar</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">DPW</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">DPD</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Role</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Posisi SEKAR</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @forelse($pengurus as $member)
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->NIK }}</td>
            <td class="py-3 px-4 text-xs font-medium text-gray-900">{{ $member->NAMA }}</td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->NO_TELP }}</td>
            <td class="py-3 px-4 text-xs text-gray-900">
                {{ $member->TANGGAL_TERDAFTAR ? \Carbon\Carbon::parse($member->TANGGAL_TERDAFTAR)->format('d-m-Y') : '-' }}
            </td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->DPW ?: '-' }}</td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->DPD ?: '-' }}</td>
            <td class="py-3 px-4 text-xs">
                @if($member->ROLE)
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                        {{ $member->ROLE }}
                    </span>
                @else
                    <span class="text-gray-500">-</span>
                @endif
            </td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->POSISI_SEKAR ?: '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="py-12 px-4 text-center text-gray-500 text-sm">
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <p class="text-gray-600 mb-1">Tidak ada data pengurus yang ditemukan</p>
                    @if(request()->hasAny(['dpw', 'dpd', 'search']))
                        <p class="text-sm text-gray-500">Coba ubah kriteria pencarian Anda</p>
                    @endif
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if(isset($pengurus) && $pengurus->hasPages())
<div class="px-6 py-4 border-t border-gray-200">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Menampilkan {{ $pengurus->firstItem() }} sampai {{ $pengurus->lastItem() }} dari {{ $pengurus->total() }} data
        </div>
        <div class="flex space-x-1">
            {{ $pengurus->appends(request()->query())->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endif