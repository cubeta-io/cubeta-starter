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
        <textarea id="{{columnNaming($label)}}" name="{{columnNaming($label)}}">{{ $value }}</textarea>
    </div>
</div>
