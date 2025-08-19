{{-- ================================================================= --}}
{{-- Tombol Tambah Pengurus (Hanya untuk Super Admin) --}}
{{-- ================================================================= --}}
@if(Auth::user()->hasRole('ADM'))
<div class="mb-4">
    <a href="{{ route('data-anggota.createPengurus') }}"
       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Tambah Pengurus
    </a>
</div>
@endif

<table class="w-full">
    <thead class="bg-gray-50">
        <tr>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NIK</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">NAMA</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs">LOKASI</th>
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
            <td class="py-3 px-4 text-xs text-gray-900 font-medium">{{ $member->NAMA }}</td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->LOKASI ?: '-' }}</td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ \Carbon\Carbon::parse($member->TANGGAL_TERDAFTAR)->translatedFormat('d F Y') }}</td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->DPW ?: '-' }}</td>
            <td class="py-3 px-4 text-xs text-gray-900">{{ $member->DPD ?: '-' }}</td>
            <td class="py-3 px-4 text-xs">
                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                    @switch($member->ROLE)
                        @case('ADM') bg-red-100 text-red-800 @break
                        @case('DPP') bg-blue-100 text-blue-800 @break
                        @case('DPW') bg-green-100 text-green-800 @break
                        @case('DPD') bg-yellow-100 text-yellow-800 @break
                        @default bg-gray-100 text-gray-800
                    @endswitch">
                    {{ $member->ROLE }}
                </span>
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