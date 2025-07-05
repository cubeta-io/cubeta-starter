@props([
    "name",
    "value",
    "checked" => "false",
    "label" => null,
    "class" => "form-check-input ",
])
<label class="form-check-label" for="{{ $name }}-{{ $value }}-radio">
    @if ($label)
        {{ $label }}
    @elseif (is_bool($value) || $value == 0 || $value == 1)
        @php
            if ($value) {
                $itemLabel = str($name)
                    ->headline()
                    ->ucfirst();
            } else {
                $itemLabel = str($name)->when(
                    str($name)->startsWith("is_"),
                    fn ($s) => $s
                        ->replace("is", "isn't")
                        ->studly()
                        ->headline()
                        ->ucfirst(),
                    fn ($s) => $s
                        ->studly()
                        ->headline()
                        ->ucfirst()
                        ->prepend("Not "),
                );
            }
        @endphp

        {{ $itemLabel }}
    @endif
    <input
        class="{{ $class }} @error($name) is-invalid @enderror"
        type="radio"
        name="{{ $name }}"
        id="{{ $name }}_{{ $value }}_radio"
        value="{{ $value }}"
        {{ $attributes->merge() }}
        @if ($checked == $value)
            checked
        @endif
    />
</label>
