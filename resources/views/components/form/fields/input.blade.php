@props(['label' , 'value' => null])

<div class="col-md-6 p-2">
    <label for="{{strtolower(Str::snake($label))}}">{{$label}}</label>
    <input class="form-control @error(strtolower(Str::snake($label))) is-invalid @enderror"
           id="{{strtolower(Str::snake($label))}}"
           name="{{strtolower(Str::snake($label))}}"
           value="{{ old(strtolower(Str::snake($label))) ?? $value ?? null }}"
           step="any"
        {{$attributes->merge()}}
    >
    <!--Handling Validation Errors-->
    @error(strtolower(Str::snake($label)))
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
    <!--End Of Handling Validation Errors-->

</div>
