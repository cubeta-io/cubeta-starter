@props(['label' , 'value' => null])

<div class="row">
    <div class="mb-3 p-3 col-lg-12 col-md-12">
        <div class="form-group">
            <label for="{{strtolower(Str::snake($label))}}-textarea">{{$label}}</label>
            <div class="form-control" style="white-space: pre-wrap;" id="{{strtolower(Str::snake($label))}}-textarea"
                 rows="3" readonly {{$attributes->merge()}}>{!! $value !!}</div>
        </div>
    </div>
</div>
