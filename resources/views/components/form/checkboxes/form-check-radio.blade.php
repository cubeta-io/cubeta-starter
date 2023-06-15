@props(['name', 'value', 'checked' => 'false'])
@if (is_array($value))

    @foreach ($value as $v)
        <div class="form-check">
            <input class="form-check-input @error(columnNaming($name)) is-invalid @enderror" type="radio"
                   name="{{ columnNaming($name) }}" id="{{ $name }}-{{ $v }}-radio"
                   value="{{ $v }}" {{ $attributes->merge() }}
                   @if ($checked == $v)
                       checked
                @endif>
            <label class="form-check-label" for="{{ $name }}-{{ $v }}-radio">
                @if ($v == 'true' || $v == 'false' || $v == '1' || $v == '0')
                    @if ($v == 'true' || $v == '1')
                        {{ ucfirst(Str::headline(Str::studly($name))) }}
                    @else
                        {{ Str::startsWith($name, 'is_') ? ucfirst(Str::headline(Str::studly(str_replace('is_', "isn't ", $name)))) : 'Not' . ucfirst(Str::headline(Str::studly($name))) }}
                    @endif
                @else
                    {{ ucfirst($v) }}
                @endif
            </label>
        </div>
    @endforeach
@else
    <div class="form-check">
        <input class="form-check-input @error(columnNaming($name)) is-invalid @enderror" type="radio"
               name="{{ columnNaming($name) }}" id="{{ $name }}-{{ $value }}-radio"
               value="{{ $value }}" {{ $attributes->merge() }}
               @if ($checked == $value || $checked == 'true' || $checked == '1')
                   checked
            @endif>
        <label class="form-check-label" for="{{ $name }}-{{ $value }}-radio">
            @if ($value == 'true' || $value == 'false' || $value == '1' || $value == '0')

                @if ($value == 'true' || $value == '1')
                    {{ ucfirst(Str::headline(Str::studly($name))) }}
                @else
                    {{ Str::startsWith($name, 'is_') ? ucfirst(Str::headline(Str::studly(str_replace('is_', "isn't ", $name)))) : 'Not' . ucfirst(Str::headline(Str::studly($name))) }}
                @endif
            @else
                {{ ucfirst($value) }}
            @endif
        </label>
    </div>
@endif
