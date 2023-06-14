@props(['label'])

<div class="col-md-6 p-2">
    <label for="{{columnNaming($label)}}">{{$label}}</label>
    <select class="form-select multiple-select-2 @error(columnNaming($label)) is-invalid @enderror"
            id="{{columnNaming($label)}}"
            data-placeholder="Choose {{\Illuminate\Support\Str::plural($label)}}"
            name="{{columnNaming($label)}}[]"
            multiple
        {{$attributes->merge()}}
    >
        {{$slot}}
    </select>
</div>
