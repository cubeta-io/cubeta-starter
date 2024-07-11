@props(['label' , 'name' => null , 'value' => null , 'class' => 'form-control'])

@php
    if (!$name){
        $name = strtolower(\Illuminate\Support\Str::snake($label));
    }
@endphp

<div id="{{$name}}_input_container" class="w-100">
    <label for="{{$name}}" class="form-label">
        {{$label}}
    </label>
    <input
        class="{{$class}} @error($name) is-invalid @enderror"
        id="{{$name}}"
        name="{{$name}}"
        value="{{ old($name) ?? $value ?? null }}"
        step="any"
        {{$attributes->merge()}}
    >
    @error($name)
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
</div>
