@props(['paginator'])

@if(isset($paginator) && $paginator->hasPages())
    <div class="mt-6 border-t border-gray-200 px-6 py-4">
        {{--
            PERBAIKAN: Menggunakan view 'custom' yang telah dirancang ulang
            untuk paginasi standar yang lebih informatif.
        --}}
        {{ $paginator->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
@endif