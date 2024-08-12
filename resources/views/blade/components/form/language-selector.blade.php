<div id="form-language-selector">
    <div class="d-flex align-items-center lang-btn-holder">
        @foreach (config('cubeta-starter.available_locales') as $lang)
            <input type="radio" class="btn-check" name="selected-language" id="lang-{{ $lang }}" autocomplete="off"
                   value="{{ $lang }}" @if ($loop->index == 0) checked @endif>
            <label class="btn btn-primary lang-btn" for="lang-{{ $lang }}">{{ strtoupper($lang) }}</label>
        @endforeach
    </div>
</div>

@push('scripts')

    <script type="module">
        const radioButtons = document.querySelectorAll('input[name="selected-language"]');
        if (radioButtons) {
            const translatableInputs = document.querySelectorAll('.translatable');
            if (translatableInputs.length > 0) {
                radioButtons.forEach(function (radioButton) {
                    radioButton.addEventListener('change', function () {
                        const selectedLanguage = this.value; // Get the value of the selected radio button

                        translatableInputs.forEach(function (input) {
                            const inputName = input.name;
                            const languageCode = inputName.match(/\[(.*?)]/)[1]; // Extract language code from input name

                            if (languageCode === selectedLanguage) {
                                input.style.display =
                                    'block'; // Show input for selected language
                                input.labels[0].style.display = 'block';
                            } else {
                                input.style.display = "none"; // Hide input for other languages
                                input.labels[0].style.display = "none";
                            }
                        });
                    });
                });
            }
        }
    </script>
@endpush
