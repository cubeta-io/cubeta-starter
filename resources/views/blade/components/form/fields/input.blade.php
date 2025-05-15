@props([
    "label",
    "name" => null,
    "value" => null,
    "class" => "form-control",
    "type" => "text",
])

@php
  if (! $name) {
      $name = str($label)->lower()->snake();
  }
@endphp

<div id="{{ $name }}_input_container" class="w-100">
  <label for="{{ $name }}" class="form-label">
    {{ $label }}
  </label>
  @if ($type == "datetime-local")
    <input
      class="{{ $class }} @error($name) is-invalid @enderror"
      id="{{ $name }}"
      name="{{ $name }}_unformatted_date_input"
      value="{{ old($name) ?? ($value ?? null) }}"
      step="any"
      type="{{ $type }}"
      {{ $attributes->merge() }}
    />
    <input
      type="hidden"
      id="{{ $name }}_formatted"
      name="{{ $name }}"
      value="{{ old($name) ?? ($value ?? null) }}"
    />
  @else
    <input
      class="{{ $class }} @error($name) is-invalid @enderror"
      id="{{ $name }}"
      name="{{ $name }}"
      value="{{ old($name) ?? ($value ?? null) }}"
      step="any"
      type="{{ $type }}"
      {{ $attributes->merge() }}
    />
  @endif

  @error($name)
  <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>
@push("scripts")
  <script type="module">
    const $datetimeInput = $('#{{ $name }}');
    const $hiddenInput = $('#{{ $name }}_formatted');

    // Update the hidden input with the formatted value on form submission
    $datetimeInput.closest('form').on('submit', function () {
      const inputValue = $datetimeInput.val(); // Original value
      if (inputValue) {
        // Format as "Y-m-d H:i"
        const formattedValue = inputValue
          .replace('T', ' ')
          .slice(0, 16);
        $hiddenInput.val(formattedValue); // Update hidden input
      }
    });
  </script>
@endpush
