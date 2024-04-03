@props(['title', 'editRoute'])
<main id="main" class="main">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="page-title">{{ $title }}</h1>
            @if (isset($editRoute))
                <a href="{{ $editRoute }}">
                    <button class="btn btn-primary">Edit</button>
                </a>
            @endif
        </div>
        <div class="card-body pt-3">
            {{ $slot }}
        </div>
    </div>
</main>
