@props(['label' , 'name' => null , 'api' , 'optionValue' , 'optionInnerText' , 'selected' => null])

@php
    if (!$name){
        $name = strtolower(\Illuminate\Support\Str::snake($label));
    }
@endphp

<div class="col-md-6 p-2">
    <label for="{{$name}}">{{$label}}</label>
    <select class="form-select select-2 @error($name) is-invalid @enderror"
            id="{{$name}}"
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
    <!--Handling Validation Errors-->
    @error($name)
    <div class="invalid-feedback">{{$message}}</div>
    @enderror
    <!--End Of Handling Validation Errors-->
    @push('scripts')
        <script type="module">
            $(document).ready(function () {
                const select2Element = $("#{{$name}}");
                select2Element.select2({
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
                                    return {id: data.{{$optionValue}}, text: data.{{$optionInnerText}}};
                                }),
                                pagination: {
                                    more: data.data.current_page < data.data.last_page
                                }
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0,
                    multiple: false,
                    closeOnSelect: false,
                    allowClear: true,
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
            });
        </script>
    @endpush
</div>
