@props(['label' , 'name' => null , 'value' => null])

@php
    if (!$name){
        $name = strtolower(\Illuminate\Support\Str::snake($label));
    }
@endphp

<div class="col-md-12 p-2">
    <script type="module">
        $(document).ready(function () {
            tinymce.init({
                selector: '#{{$name}}',
                content_css: false,
                skin: false,
            });
        });
    </script>
    <div {{$attributes->merge()}}>
        <label for="{{$name}}">{{$label}}</label>
        <textarea id="{{$name}}"
                  name="{{$name}}">{{ old($name) ?? $value }}</textarea>
    </div>

    <!--Handling Validation Errors-->
    @error($name)
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
    <!--End Of Handling Validation Errors-->

</div>
