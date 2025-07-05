@props([
    "label",
    "value",
    "classes" => "",
])

<label
    {{ $attributes->merge(["class" => "d-flex align-items-center w-100 justify-content-between " . $classes]) }}
    style="font-weight: bolder"
>
    {{ $label }} :
    <span class="fw-normal">
        @if (is_bool($value))
            @if ($value)
                <i class="bi bi-check-circle-fill text-success"></i>
            @else
                <i class="bi bi-x-circle-fill text-danger"></i>
            @endif
        @else
            {{ $value }}
        @endif
    </span>
</label>
