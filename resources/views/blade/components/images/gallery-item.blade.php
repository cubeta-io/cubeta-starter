@props ([
    "imageStoragePath",
    "deleteRoute" => null,
    "exact" => false,
])
<div class="gallery">
  <div class="image-container">
    @if ($exact)
      <a
        href="{{ $imageStoragePath }}"
        data-caption="{{ trans('site.dashboard.accessibility.image_preview') }}"
      >
        <img
          src="{{ $imageStoragePath }}"
          alt="{{ trans('site.dashboard.accessibility.image') }}"
          class="grid-img-item m-2 p-3"
        />
      </a>
    @else
      <a
        href="{{ asset("storage/$imageStoragePath") }}"
        data-caption="{{ trans('site.dashboard.accessibility.image_preview') }}"
      >
        <img
          src="{{ asset("storage/$imageStoragePath") }}"
          alt="{{ trans('site.dashboard.accessibility.image') }}"
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
          aria-label="{{ trans('site.dashboard.accessibility.remove_image') }}"
        >
          <i class="bi bi-x"></i>
        </button>
      @else
        <button
          type="button"
          class="btn btn-danger remove-btn remove-image-btn"
          data-deleteurl="{{ $deleteRoute }}"
          data-url="{{ asset("storage/$imageStoragePath") }}"
          aria-label="{{ trans('site.dashboard.accessibility.remove_image') }}"
        >
          <i class="bi bi-x"></i>
        </button>
      @endif
    @endif
  </div>
</div>
