@props(['label', 'value'])
@php
    $value = json_decode($value, true);
@endphp
@foreach (config('cubeta-starter.available_locales') as $lang)
    <label class="fw-bold d-flex align-items-center justify-content-between"
           id="{{ $lang }}-{{ $label }}-label">
        {{ $label }} : {{ strtoupper($lang) }}
        <span
            class="fw-normal"
            id="{{ $lang }}-{{ $label }}">
            {{ $value[$lang] ?? '' }}
        </span>
    </label>
@endforeach
