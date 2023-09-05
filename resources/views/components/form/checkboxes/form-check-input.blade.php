@props(['label','name','checked' => null])
@php
    use Illuminate\Support\Str;
@endphp
<div class="form-check">
    <input class="form-check-input @error(strtolower(Str::snake($label))) is-invalid @enderror"
           type="checkbox"
           value="{{strtolower(Str::snake($label))}}"
           id="{{strtolower(Str::snake($label))}}[]"
           name="{{$name}}[]"
        @checked($checked)
        {{$attributes->merge()}}
    >
    <label class="form-check-label" for="{{columnNaming($label)}}[]">
        {{$label}}
    </label>
</div>
