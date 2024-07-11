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

<div class="w-100" id="{{$name}}_translatable_text_editor_container">
    @foreach (config('cubeta-starter.available_locales') as $locale)
        <label for={{ $name }}_{{ $locale }}_textarea
               class="form-label @error($name) text-danger @enderror"
               @if ($loop->index != 0) style="display: none" @endif
        >
            {{strtoupper($locale)}} : {{ $label }}
        </label>
    @endforeach

    @foreach (config('cubeta-starter.available_locales') as $locale)
        <textarea
            id="{{ $name }}_{{ $locale }}_textarea"
            class="translatable form-control"
            data-locale="{{$locale}}"
            @if ($loop->index != 0) style="display:none" @endif
            name="{{ $name }}[{{ $locale }}]"
                {{ $attributes->merge() }}
            >{{ $value[$locale] ?? null }}</textarea>
    @endforeach

    @error($name)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
