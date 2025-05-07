@props([
    "label",
    "value" => null,
])

<label for="{{ strtolower(Str::snake($label)) }}-textarea">
    {{ $label }}
    <div
        class="form-control"
        style="white-space: pre-wrap"
        id="{{ strtolower(Str::snake($label)) }}-textarea"
        {{ $attributes->merge() }}
    >
        {!! $value !!}
    </div>
</label>
