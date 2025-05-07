@props([
    "label",
    "value",
    "classes" => "",
])

@php
    $value = json_decode($value, true);
@endphp

@foreach (config("cubeta-starter.available_locales") as $lang)
    <div class="row">
        <div class="col-lg-12 col-md-12 mb-3 p-3">
            <div class="form-group">
                <label for="{{ strtolower(Str::snake($label)) }}-textarea">
                    {{ $label }} : {{ strtoupper($lang) }}
                </label>
                <div
                    class="form-control"
                    style="white-space: pre-wrap"
                    id="{{ strtolower(Str::snake($label)) }}-textarea"
                    {{ $attributes->merge() }}
                >
                    {!! $value[$lang] ?? "" !!}
                </div>
            </div>
        </div>
    </div>
@endforeach
