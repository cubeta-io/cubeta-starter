@props(['label' , 'value' , 'classes' => ''])

<div class="row">
    <div
        {{ $attributes->merge(['class' => 'col-lg-3 col-md-4 label border border-dark-subtle '.$classes]) }}
    >
        {{$label}} :
    </div>

    <div
        {{ $attributes->merge(['class' => 'col-lg-9 col-md-8 label border border-dark-subtle '.$classes]) }}
    >
        {{$value}}
    </div>
</div>
