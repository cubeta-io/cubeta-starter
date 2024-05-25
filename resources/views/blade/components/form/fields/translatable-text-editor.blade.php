@props(['label', 'name' => null, 'value' => null])

@php

    if (!$name) {
        $name = strtolower(\Illuminate\Support\Str::snake($label));
    }

    if (old($name)) {
        $value = old($name);
    } elseif ($value) {
        $value = json_decode($value, true);
    }

@endphp

<div class="col-md-12 p-2">

    @foreach (config('cubeta-starter.available_locales') as $locale)
        <label for="{{ $name }}-{{ $locale }}"
               @if ($loop->index != 0) style="display: none" @endif>
            {{ strtoupper($locale) }} : {{ $label }}
        </label>
    @endforeach

    @foreach (config('cubeta-starter.available_locales') as $locale)
        <script type="module">
            $(document).ready(function () {
                let targetedTextArea = $("#{{ $name }}-{{ $locale }}");

                tinymce.init({
                    selector: '#{{ $name }}-{{ $locale }}',
                    content_css: false,
                    skin: false,
                });

                @if ($loop->index != 0)
                tinymce.get("{{ $name }}-{{ $locale }}").hide();
                targetedTextArea.hide();
                @endif
            });
        </script>
        <div {{ $attributes->merge() }}>
            <textarea id="{{ $name }}-{{ $locale }}" class="translatable"
                      @if ($loop->index != 0) style="display:none" @endif name="{{ $name }}[{{ $locale }}]">
                {{ $value[$locale] ?? null }}
            </textarea>
        </div>
    @endforeach

    <!-- Handling Validation Errors -->
    @error($name)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <!-- End of Handling Validation Errors -->

</div>
