@props ([
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
    {{ $value ? trans('site.dashboard.boolean.yes') : trans('site.dashboard.boolean.no') }}
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
