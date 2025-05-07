@props([
    "label",
    "name" => null,
    "value" => null,
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

<div class="w-100" id="{{ $name }}_translatable_text_editor_container">
    <!-- Hidden input for JSON data -->
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $name }}_json"
        value="{{ $jsonValue }}"
    />

    @foreach (config("cubeta-starter.available_locales") as $locale)
        <label
            for="{{ $name }}_{{ $locale }}_textarea"
            class="form-label @error($name) text-danger @enderror"
            @if ($loop->index != 0) style="display: none" @endif
        >
            {{ strtoupper($locale) }} : {{ $label }}
        </label>
    @endforeach

    @foreach (config("cubeta-starter.available_locales") as $locale)
        <textarea
            id="{{ $name }}_{{ $locale }}_textarea"
            class="translatable form-control"
            data-locale="{{ $locale }}"
            data-input-name="{{ $name }}"
            @if ($loop->index != 0) style="display:none" @endif
            name="{{ $name }}_field[{{ $locale }}]"
            {{ $attributes->merge() }}
        >
{{ $value[$locale] ?? null }}</textarea
        >
    @endforeach

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById(
            '{{ $name }}_translatable_text_editor_container',
        );
        const textAreas = container.querySelectorAll('textarea.translatable');
        const jsonInput = document.getElementById('{{ $name }}_json');

        function updateJsonInput() {
            const data = {};
            textAreas.forEach((textarea) => {
                const locale = textarea.dataset.locale;
                data[locale] = textarea.value;
            });
            jsonInput.value = JSON.stringify(data);
        }

        // Initialize JSON input with current values
        updateJsonInput();

        textAreas.forEach((textarea) => {
            textarea.addEventListener('input', updateJsonInput);
            textarea.addEventListener('change', updateJsonInput);

            // For rich text editors like TinyMCE, you might need additional events
            // If TinyMCE is used, add the following:
            // if (typeof tinymce !== 'undefined') {
            //     tinymce.on('AddEditor', function(e) {
            //         if (e.editor.id === textarea.id) {
            //             e.editor.on('Change', updateJsonInput);
            //             e.editor.on('Blur', updateJsonInput);
            //         }
            //     });
            // }
        });
    });
</script>
