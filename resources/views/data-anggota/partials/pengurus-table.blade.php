<div class="border-b border-gray-200">
    @if(Auth::user()->hasRole('ADM'))
    <div class="px-6 pt-6 pb-4 flex justify-end">
        <a href="{{ route('data-anggota.createPengurus') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Pengurus
        </a>
    </div>
    @endif

    <div class="p-6 pt-2">
        <div class="min-w-full align-middle">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">No</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">NIK</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Nama</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Lokasi</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">DPW / DPD</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Role</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Posisi SEKAR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($pengurus as $member)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 text-xs text-gray-600">{{ ($pengurus->currentPage() - 1) * $pengurus->perPage() + $loop->iteration }}</td>
                        <td class="py-3 px-4 text-xs text-gray-600">{{ $member->N_NIK }}</td>
                        <td class="py-3 px-4 text-xs font-medium text-gray-900">{{ $member->V_NAMA_KARYAWAN }}</td>
                        <td class="py-3 px-4 text-xs text-gray-600">{{ $member->V_KOTA_GEDUNG ?: '-' }}</td>
                        <td class="py-3 px-4 text-xs text-gray-600">{{ $member->DPW ?: '-' }} / {{ $member->DPD ?: '-' }}</td>
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
                        <td class="py-3 px-4 text-xs text-gray-600">{{ $member->V_SHORT_POSISI ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 px-4 text-center text-gray-500 text-sm">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <p class="text-gray-600 mb-1">Tidak ada data pengurus yang ditemukan</p>
                                @if(request()->hasAny(['dpw', 'dpd', 'search']))
                                    <p class="text-sm text-gray-500">Coba ubah kriteria pencarian Anda.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- PERBAIKAN: Memanggil komponen pagination dengan variabel $pengurus --}}
<x-pagination-links :paginator="$pengurus" />