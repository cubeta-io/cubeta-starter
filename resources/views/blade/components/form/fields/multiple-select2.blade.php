@props([
    'label' ,
    'name' => null ,
    'api' ,
    'optionValue' ,
    'optionInnerText' ,
    'selected' => null,
    'translatable' => false,
 ])

@php
    if (!$name){
        $name = strtolower(\Illuminate\Support\Str::snake($label));
    }
@endphp

<div class="w-100" id="{{$name}}_multi_select_2_container">
    <label for="{{$name}}_multi_select2">{{$label}}</label>
    <select class="form-select select-2 @error($name) is-invalid @enderror"
            id="{{$name}}_multi_select2"
            data-placeholder="Chose A {{$label}}"
            name="{{$name}}[]"
            multiple
            onchange="disableSubmitUntilFillRequiredFields()"
        {{$attributes->merge()}}
    >
        <!--Handling Preselected Options-->
        @if(old($name))

            @foreach(old($name) as $oldValue)
                <option value="{{$oldValue}}" selected>{{$oldValue}}</option>
            @endforeach

        @elseif(isset($selected) && is_countable($selected))

            @foreach($selected as $item)
                <option value="{{ $item->{$optionValue} }}" selected> {{ $item->{$optionInnerText} }}</option>
            @endforeach

        @elseif(isset($selected) && !is_countable($selected))
            <option value="{{ $selected->{$optionValue} }}" selected> {{ $selected->{$optionInnerText} }}</option>
        @endif
        <!--End Of Handling Preselected Options-->

        {{$slot}}
    </select>

    @error($name)
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
</div>

<!--select 2 initialization scripts-->
@push('scripts')
    <script type="module">
        $(document).ready(function () {
            const select2element = $("#{{$name}}_multi_select2");
            select2element.select2({
                theme: 'bootstrap-5',
                placeholder: $(this).data('placeholder'),
                ajax: {
                    url: "{{$api}}",
                    method: "GET",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            _token: "{{csrf_token()}}",
                            search: params.term,// search term
                            page: params.page || 1 // current page
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.data.data.map(function (data) {
                                return {
                                    id: data.{{$optionValue}},
                                    text: @if($translatable)
                                    JSON.parse(data.{{$optionInnerText}} ?? "{}")?.{{app()->getLocale()}}
                                        @else
                                        data.{{$optionInnerText}}
                                        @endif
                                };
                            }),
                            pagination: {
                                more: data.data.current_page < data.data.last_page
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                multiple: true,
                closeOnSelect: false,
                allowClear: true,
                escapeMarkup: function (markup) {
                    return markup;
                }
            });
        });
    </script>
@endpush
<!--end of select 2 initialization scripts-->
