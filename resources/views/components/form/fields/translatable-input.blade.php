@props(['label' ,'name'=> null, 'value' => null])
@php

    if (!$name){
        $name = strtolower(\Illuminate\Support\Str::snake($label));
    }

    if (old($name)){
        $value = old($name);
    }
    elseif ($value){
        $value = json_decode($value , true);
    }
@endphp

<div class="col-md-6 p-2">
    @foreach (config('cubeta-starter.available_locales') as $lang)
        <label for="{{ $name }}-{{ $lang }}"
               @if ($loop->index != 0) style="display: none" @endif>
            {{ $lang }} : {{ $label }}</label>
        <input class="form-control translatable @error($name) is-invalid @enderror"
               id="{{ $name }}-{{ $lang }}"
               name="{{ $name }}[{{ $lang }}]"
               value="{{ ($value[$lang] ?? null) }}" {{ $attributes->merge() }}
               @if ($loop->index != 0) style="display: none" @endif>
    @endforeach
</div>

<!--Handling Validation Errors-->
@error($name)
<div class="invalid-feedback">{{$message}}</div>
@enderror
<!--End Of Handling Validation Errors-->
