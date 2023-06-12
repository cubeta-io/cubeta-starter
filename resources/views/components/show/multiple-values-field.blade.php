@props(['label'])

<div class="row p-2">
    <div class="col-md-12 pt-5 text-center">
        <h5>{{$label}}</h5>
    </div>
    <div class="col-md-12 pt-2 text-justify">
        <p class=class="text-justify" {{$attributes->merge()}}
           style="text-align: justify; text-justify: distribute;">
            {{$slot}}
        </p>
    </div>
</div>
