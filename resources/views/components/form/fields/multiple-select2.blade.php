@props(['label'])

<div class="col-md-6 p-2">
    <label for="{{\Illuminate\Support\Str::snake($label)}}">{{$label}}</label>
    <select class="form-select multiple-select-2"
            id="{{\Illuminate\Support\Str::snake($label)}}"
            data-placeholder="Choose {{\Illuminate\Support\Str::plural($label)}}"
            name="{{\Illuminate\Support\Str::snake($label)}}[]"
            multiple
        {{$attributes->merge()}}
    >
        {{$slot}}
    </select>
</div>
