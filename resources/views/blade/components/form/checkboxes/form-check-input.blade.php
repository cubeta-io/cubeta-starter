@props(['label','name','checked' => null])
@php
    use Illuminate\Support\Str;
@endphp
<label class="form-check-label" for="{{strtolower(Str::snake($label))}}[]">
    <input class="form-check-input @error(strtolower(Str::snake($label))) is-invalid @enderror"
           type="checkbox"
           value="{{strtolower(Str::snake($label))}}"
           id="{{strtolower(Str::snake($label))}}[]"
           name="{{$name}}[]"
        @checked($checked)
        {{$attributes->merge()}}
    >
    {{$label}}
</label>
