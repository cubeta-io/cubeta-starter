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
    @foreach($errors->get(columnNaming($label)) as $error)
        <div class="invalid-feedback">{{$error}}</div>
    @endforeach
    @enderror
    <!--End Of Handling Validation Errors-->
    
</div>
