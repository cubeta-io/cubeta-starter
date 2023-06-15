@props(['name' , 'value' , 'checked' => false])
<div class="form-check">
    <input class="form-check-input @error(columnNaming($name)) is-invalid @enderror" type="radio"
           name="{{columnNaming($name)}}"
           id="{{$name}}-{{$value}}-radio" value="{{$value}}" {{$attributes->merge()}} @checked($checked)>
    <label class="form-check-label" for="{{$name}}-{{$value}}-radio">
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

@if(is_array($value))
    @foreach($value as $v)
        <div class="form-check">
            <input class="form-check-input @error(columnNaming($name)) is-invalid @enderror" type="radio"
                   name="{{columnNaming($name)}}"
                   id="{{$name}}-{{$v}}-radio" value="{{$v}}"
                   {{$attributes->merge()}}
                   @if(!is_bool($checked) && $checked == $v)checked@endif>
            <label class="form-check-label" for="{{$name}}-{{$v}}-radio">
                @if(is_bool($v))

                    @if($v)
                        {{ucfirst($name)}}
                    @else
                        Not {{ucfirst($name)}}
                    @endif

                @else
                    {{ucfirst($v)}}
                @endif
            </label>
        </div>
    @endforeach
@endif
