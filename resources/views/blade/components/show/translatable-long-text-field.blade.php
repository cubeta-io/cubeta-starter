@props([
    "label",
    "value",
])

@php
    $value = json_decode($value, true);
@endphp

@foreach (config("cubeta-starter.available_locales") as $lang)
    <x-long-text-field
        label="{{ $label }} : {{ strtoupper($lang) }}"
        :value="$value[$lang] ?? ''"
    />
@endforeach
