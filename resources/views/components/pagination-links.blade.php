@props(['paginator'])

@if(isset($paginator) && $paginator->hasPages())
    <div class="mt-6 border-t border-gray-200 px-6 py-4">
        {{-- 
            PERBAIKAN: Menggunakan 'simple-tailwind' yang dirancang khusus 
            untuk 'simplePaginate' agar tidak menyebabkan error.
        --}}
        {{ $paginator->appends(request()->query())->links('pagination::simple-tailwind') }}
    </div>
@endif