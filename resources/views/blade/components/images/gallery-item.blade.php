@props([
    "imageStoragePath",
    "deleteRoute" => null,
    "exact" => false,
])
<div class="gallery">
    <div class="image-container">
        @if ($exact)
            <a href="{{ $imageStoragePath }}" data-caption="Image caption">
                <img
                    src="{{ $imageStoragePath }}"
                    alt="First image"
                    class="grid-img-item m-2 p-3"
                />
            </a>
        @else
            <a
                href="{{ asset("storage/$imageStoragePath") }}"
                data-caption="Image caption"
            >
                <img
                    src="{{ asset("storage/$imageStoragePath") }}"
                    alt="First image"
                    class="grid-img-item m-2 p-3"
                />
            </a>
        @endif

        @if (isset($deleteRoute))
            @if ($exact)
                <button
                    type="button"
                    class="btn btn-danger remove-btn remove-image-btn"
                    data-deleteurl="{{ $deleteRoute }}"
                    data-url="{{ $imageStoragePath }}"
                >
                    <i class="bi bi-x"></i>
                </button>
            @else
                <button
                    type="button"
                    class="btn btn-danger remove-btn remove-image-btn"
                    data-deleteurl="{{ $deleteRoute }}"
                    data-url="{{ asset("storage/$imageStoragePath") }}"
                >
                    <i class="bi bi-x"></i>
                </button>
            @endif
        @endif
    </div>
</div>
