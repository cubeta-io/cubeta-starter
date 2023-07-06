@props(['label', 'value' => null])
@php
    if (old(columnNaming($label))){
        $value = old(columnNaming($label));
    }
    elseif ($value){
        $value = json_decode($value , true);
    }
@endphp
<div class="col-md-6 p-2">
    @foreach (config('cubeta-starter.available_locales') as $lang)
        <label for="{{ columnNaming($label) }}-{{ $lang }}"
               @if ($loop->index != 0) style="display: none" @endif>
            {{ $lang }} : {{ $label }}</label>
        <input class="form-control translatable @error(columnNaming($label)) is-invalid @enderror"
               id="{{ columnNaming($label) }}-{{ $lang }}" name="{{ columnNaming($label) }}[{{ $lang }}]"
               value="{{ ($value[$lang] ?? null) }}" {{ $attributes->merge() }}
               @if ($loop->index != 0) style="display: none" @endif>
    @endforeach
</div>

<!--Handling Validation Errors-->
@error(columnNaming($label))
@foreach($errors->get(columnNaming($label)) as $error)
    <div class="invalid-feedback">{{$error}}</div>
@endforeach
@enderror
<!--End Of Handling Validation Errors-->
