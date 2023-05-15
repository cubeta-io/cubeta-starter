@props(['imageStoragePath' , 'deleteRoute'])

<div class="image-container">
    <a href="{{asset("storage/$imageStoragePath")}}" data-caption="Image caption">
        <img src="{{asset("storage/$imageStoragePath")}}" alt="First image"
             class="grid-img-item p-3 m-2">
    </a>
    @if(isset($deleteRoute))
        <button type="button"
                class="btn btn-danger remove-btn remove-image-btn"
                data-deleteurl="{{$deleteRoute}}"
                data-url="{{ asset("storage/$imageStoragePath") }}"><i
                class="bi bi-x"></i>
        </button>
    @endif
</div>
