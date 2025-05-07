@props([
    "title",
    "editRoute",
])
<x-page-card>
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="card-title mt-2">
            {{ $title }}
        </h2>
        @if (isset($editRoute))
            <a href="{{ $editRoute }}" class="btn btn-primary">Edit</a>
        @endif
    </div>
    {{ $slot }}
</x-page-card>
