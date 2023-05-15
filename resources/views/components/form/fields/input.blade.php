@props(['label' , 'value' => null])

<div class="col-md-6 p-2">
    <label for="{{Illuminate\Support\Str::snake($label)}}">{{$label}}</label>
    <input class="form-control"
           id="{{Illuminate\Support\Str::snake($label)}}"
           name="{{Illuminate\Support\Str::snake($label)}}"
           value="{{ old('first_name') ?? $value ?? null }}"
        {{$attributes->merge()}}>
</div>
