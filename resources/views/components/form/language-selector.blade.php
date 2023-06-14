<div class="col-md-6 m-auto">
    @foreach(config('cubeta-starter.available_locales') as $lang)
        <input type="radio" class="btn-check" name="selected-language" id="lang-{{$lang}}" autocomplete="off"
               value="{{$lang}}"
               @if($loop->index == 0) checked @endif>
        <label class="btn btn-primary" for="lang-{{$lang}}">{{strtoupper($lang)}}</label>
    @endforeach
</div>
<script type="module">
    const radioButtons = document.querySelectorAll('input[name="selected-language"]');
    const translatableInputs = document.querySelectorAll('.translatable');

    radioButtons.forEach(function (radioButton) {
        radioButton.addEventListener('change', function () {
            const selectedLanguage = this.value; // Get the value of the selected radio button

            translatableInputs.forEach(function (input) {
                const inputName = input.name;
                const languageCode = inputName.match(/\[(.*?)\]/)[1]; // Extract language code from input name


                if (languageCode === selectedLanguage) {
                    input.toggleAttribute('hidden', false) // Show input for selected language
                    input.labels[0].toggleAttribute('hidden', false);
                } else {
                    input.toggleAttribute('hidden', true) // Hide input for other languages
                    input.labels[0].toggleAttribute('hidden', true);
                }
            });
        });
    });

</script>
