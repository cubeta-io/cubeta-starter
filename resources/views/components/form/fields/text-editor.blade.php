@props(['label' , 'value' => null])

<div class="col-md-12 p-2">
    <label for="{{\Illuminate\Support\Str::snake($label)}}">{{$label}}</label>
    <textarea id="{{\Illuminate\Support\Str::snake($label)}}"
              class="Trunbowyg-text-editor"
              name="{{\Illuminate\Support\Str::snake($label)}}" {{$attributes->merge()}}>{{$value}}
    </textarea>
</div>
