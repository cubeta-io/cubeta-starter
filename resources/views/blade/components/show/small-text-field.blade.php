@props([
    "label",
    "value",
    "classes" => "",
])

<div class="row">
    <label
        {{ $attributes->merge(["class" => "col-lg-3 col-md-4 label" . $classes]) }}
        style="font-weight: bolder"
    >
        {{ $label }} :
    </label>

    <p
        {{ $attributes->merge(["class" => "col-lg-9 col-md-8 label" . $classes]) }}
    >
        @if (is_bool($value))
            @if ($value)
                <i class="bi bi-check-circle text-success"></i>
            @else
                <i class="bi bi-x-circle text-danger"></i>
            @endif
        @else
            {{ $value }}
        @endif
    </p>
</div>
