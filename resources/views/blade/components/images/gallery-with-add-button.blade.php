<div class="p-2">
  <div class="gallery">
    {{ $slot }}
    <div class="image-container" hidden>
      <a
        id="imageThumb"
        data-caption="{{ trans('site.dashboard.accessibility.image_preview') }}"
      >
        <img
          alt="{{ trans('site.dashboard.accessibility.image') }}"
          id="imagePreview"
          class="grid-img-item m-2 p-3"
          src=""
        />
      </a>
      <button
        id="remove-button-for-temp"
        type="button"
        hidden
        class="btn btn-danger remove-btn remove-image-btn"
        data-deleteurl="#"
        data-url=""
        aria-label="{{ trans('site.dashboard.accessibility.remove_image') }}"
      >
        <i class="bi bi-x"></i>
      </button>
    </div>
    <div class="image-container">
      <label
        for="imageUpload"
        class="grid-img-item btn upload-file-button-label m-2 p-3"
        aria-label="{{ trans('site.dashboard.accessibility.add_image') }}"
      >
        <i class="bi bi-plus-circle link-primary upload-file-button-icon"></i>
      </label>
      <input id="imageUpload" type="file" name="image" style="display: none" />
    </div>
  </div>
</div>
