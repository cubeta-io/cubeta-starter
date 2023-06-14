@props(['label','name','checked' => null])

<div class="form-check">
    <input class="form-check-input @error(columnNaming($label)) is-invalid @enderror"
           type="checkbox"
           value="{{columnNaming($label)}}"
           id="{{columnNaming($label)}}[]"
           name="{{$name}}[]"
        @checked($checked)
        {{$attributes->merge()}}
    >
    <label class="form-check-label" for="{{columnNaming($label)}}[]">
        {{$label}}
    </label>
</div>
