@props([
    "label",
    "value",
])
@php
    $value = json_decode($value, true);
@endphp

@foreach (config("cubeta-starter.available_locales") as $lang)
    <x-small-text-field
        :value="$value[$lang] ?? ''"
        label="{{$label}} : {{ strtoupper($lang) }}"
    />
@endforeach
