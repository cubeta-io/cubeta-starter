@props(['name', 'value', 'checked' => 'false' , 'label' => null , 'class' => 'form-check-input '])
@if (is_array($value))
    @foreach ($value as $v)
        <label class="form-check-label" for="{{ $name }}-{{ $v }}-radio">
            @if (($value === 'true' || $value === 'false' || $value === '1' || $value === '0' || is_bool($value)) && !$label)
                @if ($value === 'true' || $value === '1' || $value === true || $value === 1)
                    {{ ucfirst(Str::headline(Str::studly($name))) }}
                @else
                    {{ Str::startsWith($name, 'is_')
                        ? ucfirst(Str::headline(Str::studly(str_replace('is_', "isn't ", $name))))
                        : 'Not ' . ucfirst(Str::headline(Str::studly($name))) }}
                @endif
            @else
                {{ ucfirst($v) }}
            @endif
            <input class="form-check-input @error(strtolower(Str::snake($name))) is-invalid @enderror"
                   type="radio"
                   name="{{ strtolower(Str::snake($name)) }}"
                   id="{{ $name }}-{{ $v }}-radio"
                   value="{{ $v }}"
                   {{ $attributes->merge() }}
                   @if ($checked == $v)
                       checked
                @endif
            >
        </label>
    @endforeach
@else
    <label class="form-check-label" for="{{ $name }}-{{ $value }}-radio">
        @if (($value === 'true' || $value === 'false' || $value === '1' || $value === '0' || is_bool($value)) && !$label)
            @if ($value === 'true' || $value === '1' || $value === true || $value === 1)
                {{ ucfirst(Str::headline(Str::studly($name))) }}
            @else
                {{ Str::startsWith($name, 'is_') ? ucfirst(Str::headline(Str::studly(str_replace('is_', "isn't ", $name)))) : 'Not' . ucfirst(Str::headline(Str::studly($name))) }}
            @endif
        @else
            {{ $label }}
        @endif
        <input
            class="{{$class}} @error(strtolower(Str::snake($name))) is-invalid @enderror"
            type="radio"
            name="{{ strtolower(Str::snake($name)) }}"
            id="{{ $name }}_{{ $value }}_radio"
            value="{{ $value }}"
            {{ $attributes->merge() }}
            @if ($checked == $value)
                checked
            @endif
        >
    </label>
@endif
