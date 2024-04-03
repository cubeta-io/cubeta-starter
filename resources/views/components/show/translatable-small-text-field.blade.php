@props(['label', 'value', 'classes' => ''])
@php
    $value = json_decode($value, true);
@endphp
@foreach (config('cubeta-starter.available_locales') as $lang)
    <div class="row">
        <label {{ $attributes->merge(['class' => 'col-lg-3 col-md-4 label ' . $classes]) }}
             id="{{ $lang }}-{{ $label }}-label">
            {{ $label }} : {{ strtoupper($lang) }}
        </label>

        <p {{ $attributes->merge(['class' => 'col-lg-9 col-md-8 label ' . $classes]) }}
             id="{{ $lang }}-{{ $label }}">
            {{ $value[$lang] ?? '' }}
        </p>
    </div>
@endforeach
