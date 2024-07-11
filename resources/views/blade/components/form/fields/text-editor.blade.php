@props(['label' , 'name' => null , 'value' => null])

@php
    if (!$name){
        $name = strtolower(\Illuminate\Support\Str::snake($label));
    }
@endphp

<div class="w-100" id="{{$name}}_text_editor">
    <label for="{{$name}}_text_editor" class="form-label">
        {{$label}}
    </label>
    <textarea
        id="{{$name}}_text_editor"
        name="{{$name}}"
        class="form-control"
        {{$attributes->merge()}}
    >{{ old($name) ?? $value }}</textarea>
    @error($name)
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
</div>

