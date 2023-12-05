@props(['label' , 'name' => null , 'value' => null])

@php
    if (!$name){
        $name = strtolower(\Illuminate\Support\Str::snake($label));
    }
@endphp

<div class="col-md-6 p-2">
    <label for="{{$name}}">{{$label}}</label>
    <input class="form-control @error($name) is-invalid @enderror"
           id="{{$name}}"
           name="{{$name}}"
           value="{{ old($name) ?? $value ?? null }}"
           step="any"
        {{$attributes->merge()}}
    >
    <!--Handling Validation Errors-->
    @error($name)
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
    <!--End Of Handling Validation Errors-->
</div>
