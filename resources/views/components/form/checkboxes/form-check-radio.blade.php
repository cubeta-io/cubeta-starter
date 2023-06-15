@props(['name' , 'value' , 'checked' => false])
<div class="form-check">
    <input class="form-check-input @error(columnNaming($name)) is-invalid @enderror" type="radio"
           name="{{$name}}"
           id="{{$value}}-radio" value="{{$value}}" {{$attributes->merge()}} @checked($checked)>
    <label class="form-check-label" for="{{$value}}-radio">
        @if(is_bool($value))

            @if($value)
                {{ucfirst($name)}}
            @else
                Not {{ucfirst($name)}}
            @endif

        @else
            {{ucfirst($value)}}
        @endif
    </label>
</div>
