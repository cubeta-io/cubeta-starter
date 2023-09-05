@props(['label' , 'value' => null])

<div class="col-md-12 p-2">
    <script type="module">
        $(document).ready(function () {
            tinymce.init({
                selector: '#{{strtolower(Str::snake($label))}}',
                content_css: false,
                skin: false,
            });
        });
    </script>
    <div {{$attributes->merge()}}>
        <label for="{{strtolower(Str::snake($label))}}">{{$label}}</label>
        <textarea id="{{strtolower(Str::snake($label))}}"
                  name="{{strtolower(Str::snake($label))}}">{{ old(strtolower(Str::snake($label))) ?? $value }}</textarea>
    </div>

    <!--Handling Validation Errors-->
    @error(strtolower(Str::snake($label)))
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
    <!--End Of Handling Validation Errors-->

</div>
