@props(['label' , 'value' => null])

<div class="col-md-6 p-2">
    <label for="{{columnNaming($label)}}">{{$label}}</label>
    <input class="form-control @error(columnNaming($label)) is-invalid @enderror"
           id="{{columnNaming($label)}}"
           name="{{columnNaming($label)}}"
           value="{{ old(columnNaming($label)) ?? $value ?? null }}"
           step="any"
        {{$attributes->merge()}}
    >
    <!--Handling Validation Errors-->
    @error(columnNaming($label))
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
    <!--End Of Handling Validation Errors-->

</div>
