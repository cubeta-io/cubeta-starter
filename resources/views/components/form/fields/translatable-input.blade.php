@props(['label' , 'value' => null])

@foreach(config('cubeta-starter.available_locales') as $lang)
    <div class="col-md-6 p-2">
        <label for="{{columnNaming($label)}}">{{$lang}} : {{$label}}</label>
        <input class="form-control translatable @error(columnNaming($label)) is-invalid @enderror"
               id="{{columnNaming($label)}}"
               name="{{columnNaming($label)}}[{{$lang}}]"
               value="{{ old('"'.columnNaming($label).'"') ?? $value ?? null }}"
               {{$attributes->merge()}} @if($loop->index != 0) hidden @endif>
    </div>
@endforeach
