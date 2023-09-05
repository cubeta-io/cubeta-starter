@props(['label', 'value' => null])
@php
    if (old(strtolower(Str::snake($label)))){
        $value = old(strtolower(Str::snake($label)));
    }
    elseif ($value){
        $value = json_decode($value , true);
    }
@endphp
<div class="col-md-6 p-2">
    @foreach (config('cubeta-starter.available_locales') as $lang)
        <label for="{{ strtolower(Str::snake($label)) }}-{{ $lang }}"
               @if ($loop->index != 0) style="display: none" @endif>
            {{ $lang }} : {{ $label }}</label>
        <input class="form-control translatable @error(strtolower(Str::snake($label))) is-invalid @enderror"
               id="{{ strtolower(Str::snake($label)) }}-{{ $lang }}" name="{{ strtolower(Str::snake($label)) }}[{{ $lang }}]"
               value="{{ ($value[$lang] ?? null) }}" {{ $attributes->merge() }}
               @if ($loop->index != 0) style="display: none" @endif>
    @endforeach
</div>

<!--Handling Validation Errors-->
@error(strtolower(Str::snake($label)))
<div class="invalid-feedback">{{$message}}</div>
@enderror
<!--End Of Handling Validation Errors-->
