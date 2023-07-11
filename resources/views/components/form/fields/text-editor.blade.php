@props(['label' , 'value' => null])

<div class="col-md-12 p-2">
    <script type="module">
        $(document).ready(function () {
            tinymce.init({
                selector: '#{{columnNaming($label)}}',
                content_css: false,
                skin: false,
            });
        });
    </script>
    <div {{$attributes->merge()}}>
        <label for="{{columnNaming($label)}}">{{$label}}</label>
        <textarea id="{{columnNaming($label)}}"
                  name="{{columnNaming($label)}}">{{ old(columnNaming($label)) ?? $value }}</textarea>
    </div>

    <!--Handling Validation Errors-->
    @error(columnNaming($label))
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
    <!--End Of Handling Validation Errors-->

</div>
