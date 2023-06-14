@props(['label'])

<div class="col-md-6 p-2">
    <label for="{{columnNaming($label)}}">{{$label}}</label>
    <select class="form-select select-2 @error(columnNaming($label)) is-invalid @enderror"
            id="{{columnNaming($label)}}"
            data-placeholder="Chose A {{$label}}"
            name="{{columnNaming($label)}}"
            {{$attributes->merge()}}
    >
        {{$slot}}
    </select>
</div>
