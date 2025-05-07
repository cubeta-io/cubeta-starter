@props([
    "label",
    "name" => null,
    "value" => null,
    "class" => "form-control w-full",
])
@php
    if (! $name) {
        $name = str($label)
            ->snake()
            ->lower()
            ->toString();
    }
    if (old($name)) {
        $value = old($name);
    } elseif ($value) {
        $value = json_decode($value, true);
    }

    $jsonValue = json_encode($value ?: [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp

<div id="translatable_{{ $name }}_input_container" class="w-full">
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $name }}_json"
        value="{{ $jsonValue }}"
    />

    @foreach (config("cubeta-starter.available_locales") as $lang)
        <label
            for="{{ $name }}-{{ $lang }}"
            @if ($loop->index != 0) style="display: none" @endif
            class="form-label"
        >
            {{ strtoupper($lang) }} : {{ $label }}
        </label>
        <input
            class="{{ $class }} translatable @error($name) is-invalid @enderror"
            id="{{ $name }}-{{ $lang }}"
            name="{{ $name }}_field[{{ $lang }}]"
            value="{{ $value[$lang] ?? null }}"
            data-lang="{{ $lang }}"
            data-input-name="{{ $name }}"
            {{ $attributes->merge() }}
            @if($loop->index != 0) style="display: none" @endif
        />
    @endforeach

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById(
            'translatable_{{ $name }}_input_container',
        );
        const inputs = container.querySelectorAll('input.translatable');
        const jsonInput = document.getElementById('{{ $name }}_json');

        function updateJsonInput() {
            const data = {};
            inputs.forEach((input) => {
                const lang = input.dataset.lang;
                data[lang] = input.value;
            });
            jsonInput.value = JSON.stringify(data);
        }

        // Initialize JSON input
        updateJsonInput();

        inputs.forEach((input) => {
            input.addEventListener('input', updateJsonInput);
            input.addEventListener('change', updateJsonInput);
        });
    });
</script>
