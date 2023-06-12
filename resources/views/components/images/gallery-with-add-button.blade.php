<div class="p-2">
    <div class="gallery">
        {{$slot}}
        <div class="image-container" hidden>
            <a id="imageThumb"
               data-caption="Image caption">
                <img alt="First image" id="imagePreview"
                     class="grid-img-item p-3 m-2" src="">
            </a>
            <button id="remove-button-for-temp" type="button" hidden
                    class="btn btn-danger remove-btn remove-image-btn"
                    data-deleteurl="#"
                    data-url=""><i
                    class="bi bi-x"></i>
            </button>
        </div>
        <div class="image-container">
            <label for="imageUpload"
                   class="grid-img-item p-3 m-2 btn upload-file-button-label">
                <i class="bi bi-plus-circle link-primary upload-file-button-icon"></i>
            </label>
            <input id="imageUpload" type="file" name="image" style="display: none;">
        </div>
    </div>
</div>
