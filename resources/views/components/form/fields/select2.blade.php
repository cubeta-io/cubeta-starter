@props(['label'])

<div class="col-md-6 p-2">
    <label for="{{\Illuminate\Support\Str::snake($label)}}">{{$label}}</label>
    <select class="form-select select-2"
            id="{{\Illuminate\Support\Str::snake($label)}}"
            data-placeholder="Chose A {{$label}}"
            name="{{\Illuminate\Support\Str::snake($label)}}"
            {{$attributes->merge()}}
    >
        {{$slot}}
    </select>
</div>
