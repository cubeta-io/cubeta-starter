@props(['label', 'value', 'classes' => ''])
@php
    $value = json_decode($value, true);
@endphp
@foreach (config('cubeta-starter.available_locales') as $lang)
    <div class="row">
        <div {{ $attributes->merge(['class' => 'col-lg-3 col-md-4 label border border-dark-subtle ' . $classes]) }}
             style="display:none;" id="{{ $lang }}-{{ $label }}-label">
            {{ $label }} : {{ $lang }}
        </div>

        <div {{ $attributes->merge(['class' => 'col-lg-9 col-md-8 label border border-dark-subtle ' . $classes]) }}
             style="display:none;" id="{{ $lang }}-{{ $label }}">
            {{ $value[$lang] ?? '' }}
        </div>
    </div>
@endforeach

<script type="module">
    $(document).ready(function() {
        let languageSelector = $('input[name="selected-language"]:checked');
        if(languageSelector){
            let selectedLanguage = languageSelector.val();
            let selectedLabel = $("#" + selectedLanguage + "-" + "{{$label}}" + "-label").css('display', 'inline-block');
            let selectedField = $("#" + selectedLanguage + "-" + "{{$label}}").css('display', 'inline-block');

            $('input[name="selected-language"]').on('click', function() {
                selectedLabel.css('display' , 'none');
                selectedField.css('display' , 'none');
                selectedLanguage = $(this).val();
                selectedLabel = $("#" + selectedLanguage + "-" + "{{$label}}" + "-label").css('display', 'inline-block');
                selectedField = $("#" + selectedLanguage + "-" + "{{$label}}").css('display', 'inline-block');
            });
        }
    });
</script>
