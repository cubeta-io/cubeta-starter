@props(['imagePath' => '#' , 'deleteRoute' => null])
<div class="p-2">
    <div class="gallery">
        <div class="image-container">
            <a href="{{$imagePath}}" data-caption="Image caption">
                <img src="{{$imagePath}}" alt="First image"
                     class="grid-img-item p-3 m-2">
            </a>
            @if(isset($deleteRoute))
                <button type="button"
                        class="btn btn-danger remove-btn remove-image-btn"
                        data-deleteurl="{{$deleteRoute}}"
                        data-url="{{ $imagePath }}"><i
                        class="bi bi-x"></i>
                </button>
            @endif
        </div>
    </div>
</div>
