<table class="w-full">
    <thead class="bg-gray-50">
        <tr>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NIK</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NAMA</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NO TELP</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Tanggal Terdaftar</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Status</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Posisi</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">Lokasi</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @forelse($gptp as $member)
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->NIK }}</td>
            <td class="py-3 px-4 text-xs font-medium text-gray-900">{{ $member->NAMA }}</td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->NO_TELP }}</td>
            <td class="py-3 px-4 text-xs text-gray-900">
                {{ $member->TANGGAL_TERDAFTAR ? \Carbon\Carbon::parse($member->TANGGAL_TERDAFTAR)->format('d-m-Y') : '-' }}
            </td>
            <td class="py-3 px-4 text-xs">
                <span class="px-2 py-1 text-xs rounded-full {{ $member->STATUS === 'Terdaftar' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ $member->STATUS }}
                </span>
            </td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->POSISI }}</td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->LOKASI }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="py-12 px-4 text-center text-gray-500 text-sm">
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                    </svg>
                    <p class="text-gray-600 mb-1">Tidak ada data GPTP yang ditemukan</p>
                    @if(request('search'))
                        <p class="text-sm text-gray-500">Coba ubah kriteria pencarian Anda</p>
                    @endif
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if(isset($gptp) && $gptp->hasPages())
<div class="px-6 py-4 border-t border-gray-200">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Menampilkan {{ $gptp->firstItem() }} sampai {{ $gptp->lastItem() }} dari {{ $gptp->total() }} data
        </div>
        <div class="flex space-x-1">
            {{ $gptp->appends(request()->query())->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endif