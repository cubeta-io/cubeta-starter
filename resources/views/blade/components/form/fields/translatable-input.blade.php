@props(['label' ,'name'=> null, 'value' => null , 'class' => 'form-control w-full'])
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

<div id="translatable_{{$name}}_input_container" class="w-full">
    @foreach (config('cubeta-starter.available_locales') as $lang)
        <label for="{{ $name }}-{{ $lang }}"
               @if ($loop->index != 0) style="display: none" @endif
               class="form-label"
        >
            {{ strtoupper($lang) }} : {{ $label }}
        </label>
        <input class="{{$class}} translatable @error($name) is-invalid @enderror"
               id="{{ $name }}-{{ $lang }}"
               name="{{ $name }}[{{ $lang }}]"
               value="{{ ($value[$lang] ?? null) }}"
               {{ $attributes->merge() }}
               @if ($loop->index != 0) style="display: none" @endif
        >
    @endforeach
    @error($name)
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
</div>
