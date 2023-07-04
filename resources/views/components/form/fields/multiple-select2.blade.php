@props(['label' , 'api' , 'optionValue' , 'optionInnerText' , 'selected' => null])
<div class="col-md-6 p-2">
    <label for="{{columnNaming($label)}}">{{$label}}</label>
    <select class="form-select select-2 @error(columnNaming($label)) is-invalid @enderror"
            id="{{columnNaming($label)}}"
            data-placeholder="Chose A {{$label}}"
            name="{{columnNaming($label)}}[]"
            multiple
            onchange="disableSubmitUntilFillRequiredFields()"
        {{$attributes->merge()}}
    >
        @if(old(columnNaming($label)))

            @foreach(old(columnNaming($label)) as $oldValue)
                <option value="{{$oldValue}}" selected>{{$oldValue}}</option>
            @endforeach

        @elseif(isset($selected))

            @foreach($selected as $item)
                <option value="{{ $item->{$optionValue} }}" selected> {{ $item->{$optionInnerText} }}</option>
            @endforeach

        @endif
        {{$slot}}
    </select>
    @push('scripts')
        <script type="module">
            $(document).ready(function () {
                const select2element = $("#{{columnNaming($label)}}");
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
</div>
