@props (["imagePath" => "#", "deleteRoute" => null])
<div class="p-2">
  <div class="gallery">
    <div class="image-container">
      <a
        href="{{ $imagePath }}"
        data-caption="{{ trans('site.dashboard.accessibility.image_preview') }}"
      >
        <img
          src="{{ $imagePath }}"
          alt="{{ trans('site.dashboard.accessibility.image') }}"
          class="grid-img-item m-2 p-3"
        />
      </a>
      @if (isset($deleteRoute))
        <button
          type="button"
          class="btn btn-danger remove-btn remove-image-btn"
          data-deleteurl="{{ $deleteRoute }}"
          data-url="{{ $imagePath }}"
          aria-label="{{ trans('site.dashboard.accessibility.remove_image') }}"
        >
          <i class="bi bi-x"></i>
        </button>
      @endif
    </div>
  </div>
</div>
