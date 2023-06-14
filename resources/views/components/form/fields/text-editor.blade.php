@props(['label' , 'value' => null])

<div class="col-md-12 p-2">
    <label for="{{columnNaming($label)}}">{{$label}}</label>
    <textarea id="{{columnNaming($label)}}"
              class="Trumbowyg-text-editor @error(columnNaming($label)) is-invalid @enderror"
              name="{{columnNaming($label)}}" {{$attributes->merge()}}>{!! $value !!}
    </textarea>
</div>
