@props(['label' , 'name' => null , 'api' , 'optionValue' , 'optionInnerText' , 'selected' => null , 'translatable' => false ])

@php
    if (!$name){
        $name = strtolower(\Illuminate\Support\Str::snake($label));
    }
@endphp

<div class="w-100" id="{{$name}}_select2_container">
    <label class="form-label" for="{{$name}}_select2">{{$label}}</label>
    <select class="form-select select-2 @error($name) is-invalid @enderror"
            id="{{$name}}_select2"
            data-placeholder="Chose A {{$label}}"
            name="{{$name}}"
            onchange="disableSubmitUntilFillRequiredFields()"
            {{$attributes->merge()}}
    >
        @if(old($name))
            <option value="{{ old($name) }}">{{old($name)}}</option>
        @elseif(isset($selected))
            <option value="{{ $selected->{$optionValue} }}">{{ $selected->{$optionInnerText} }}</option>
        @endif

        {{$slot}}
    </select>
    @error($name)
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
</div>

@push('scripts')
    <script type="module">
        $(document).ready(function() {
            const select2Element = $("#{{$name}}_select2");
            select2Element.select2({
                theme: "bootstrap-5",
                containerCssClass: "bg-dark",
                placeholder: $(this).data("placeholder"),
                ajax: {
                    url: "{{$api}}",
                    method: "GET",
                    dataType: "json",
                    delay: 250,
                    data: function(params) {
                        return {
                            _token: "{{csrf_token()}}",
                            search: params.term,// search term
                            page: params.page || 1 // current page
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data?.data?.map(function(data) {
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
                                more: !data.pagination_data.is_last_page
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                multiple: false,
                closeOnSelect: false,
                allowClear: true,
                escapeMarkup: function(markup) {
                    return markup;
                }
            });
        });
    </script>
@endpush
