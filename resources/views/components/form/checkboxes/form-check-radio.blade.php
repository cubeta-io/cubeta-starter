@props(['name' , 'value' , 'checked' => null])

<div class="form-check">
    <input class="form-check-input" type="radio" name="{{$name}}"
           id="{{$value}}-radio" value="{{$value}}" {{$attributes->merge()}} @checked($checked)>
    <label class="form-check-inpu" for="{{$value}}-radio">
        {{ucfirst($value)}}
    </label>
</div>
