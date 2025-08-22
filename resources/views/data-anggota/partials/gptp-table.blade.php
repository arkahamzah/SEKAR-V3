<div class="p-6">
    <div class="min-w-full align-middle">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">No</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">NIK</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Nama</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Lokasi</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-xs uppercase tracking-wider">Posisi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($gptp as $member)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-4 text-xs text-gray-600">{{ ($gptp->currentPage() - 1) * $gptp->perPage() + $loop->iteration }}</td>
                    <td class="py-3 px-4 text-xs text-gray-600">{{ $member->N_NIK }}</td>
                    <td class="py-3 px-4 text-xs font-medium text-gray-900">{{ $member->V_NAMA_KARYAWAN }}</td>
                    <td class="py-3 px-4 text-xs text-gray-600">{{ $member->V_KOTA_GEDUNG }}</td>
                    <td class="py-3 px-4 text-xs">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $member->STATUS === 'Terdaftar' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ $member->STATUS }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-xs text-gray-600">{{ $member->V_SHORT_POSISI }}</td>
                </tr>
                @empty
                <tr>
                     <td colspan="7" class="py-12 px-4 text-center text-gray-500 text-sm">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                      <p class="text-gray-600 mb-1">Tidak ada data GPTP yang ditemukan</p>
                      @if(request('search'))
                          <p class="text-sm text-gray-500">Coba ubah kriteria pencarian Anda.</p>
                      @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Memanggil komponen pagination terpusat --}}
    <x-pagination-links :paginator="$gptp" />
</div>