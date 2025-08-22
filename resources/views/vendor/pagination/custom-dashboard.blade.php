@if ($paginator->hasPages())
    <div class="flex items-center justify-between">
        {{-- Kiri: Dropdown jumlah data --}}
        <div class="flex items-center text-sm text-gray-700">
            <span>Tampilkan:</span>
            <select id="perPageSelect" class="mx-2 block w-full pl-3 pr-10 py-1.5 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                @foreach ([10, 25, 50] as $size)
                    <option value="{{ $size }}" {{ request('size', 10) == $size ? 'selected' : '' }}>
                        {{ $size }}
                    </option>
                @endforeach
            </select>
            <span>Data</span>
        </div>

        {{-- Tengah: Info Halaman --}}
        <div class="hidden sm:block">
            <p class="text-sm text-gray-700">
                Halaman <span class="font-medium">{{ $paginator->currentPage() }}</span> dari <span class="font-medium">{{ $paginator->lastPage() }}</span>
            </p>
        </div>

        {{-- Kanan: Tombol Navigasi --}}
        <div class="flex items-center space-x-1">
            {{-- Tombol Pertama --}}
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50 cursor-not-allowed">Pertama</span>
            @else
                <a href="{{ $paginator->url(1) }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Pertama</a>
            @endif

            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50 cursor-not-allowed">«</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">«</a>
            @endif

            {{-- Tombol Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">»</a>
            @else
                <span class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50 cursor-not-allowed">»</span>
            @endif

            {{-- Tombol Terakhir --}}
             @if ($paginator->hasMorePages())
                <a href="{{ $paginator->url($paginator->lastPage()) }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Terakhir</a>
            @else
                 <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50 cursor-not-allowed">Terakhir</span>
            @endif
        </div>
    </div>

    <script>
        document.getElementById('perPageSelect').addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('size', this.value);
            // Selalu kembali ke halaman 1 saat jumlah data diubah
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        });
    </script>
@endif