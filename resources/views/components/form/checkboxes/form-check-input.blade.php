@props(['label','name','checked' => null])

<div class="form-check">
    <input class="form-check-input @error(Illuminate\Support\Str::snake($label)) is-invalid @enderror"
           type="checkbox"
           value="{{\Illuminate\Support\Str::snake($label)}}"
           id="{{\Illuminate\Support\Str::snake($label)}}[]"
           name="{{$name}}[]"
        @checked($checked)
        {{$attributes->merge()}}
    >
    <label class="form-check-label" for="{{\Illuminate\Support\Str::snake($label)}}[]">
        {{$label}}
    </label>
</div>
