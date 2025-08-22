{{-- resources/views/pagination-links.blade.php --}}

<div class="mt-6 border-t border-gray-200 px-6 py-4">
    {{-- Menggunakan view paginasi BARU yang sama dengan Dashboard --}}
    {{ $paginator->appends(request()->query())->links('vendor.pagination.custom-dashboard') }}
</div>