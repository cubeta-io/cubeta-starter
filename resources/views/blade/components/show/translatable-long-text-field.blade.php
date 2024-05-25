@props(['label', 'value', 'classes' => ''])

@php
    $value = json_decode($value, true);
@endphp

@foreach (config('cubeta-starter.available_locales') as $lang)
    <div class="row">
        <div class="mb-3 p-3 col-lg-12 col-md-12">
            <div class="form-group">
                <label for="{{strtolower(Str::snake($label))}}-textarea">
                    {{ $label }} : {{ strtoupper($lang) }}
                </label>
                <div class="form-control" style="white-space: pre-wrap;"
                     id="{{strtolower(Str::snake($label))}}-textarea"
                    {{$attributes->merge()}}
                >
                    {!! $value[$lang] ?? '' !!}
                </div>
            </div>
        </div>
    </div>
@endforeach
