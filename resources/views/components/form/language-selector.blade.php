<div class="row">
    <div class="col-md-12 d-flex justify-content-end align-items-center lang-btn-holder">
        @foreach (config('cubeta-starter.available_locales') as $lang)
            <input type="radio" class="btn-check" name="selected-language" id="lang-{{ $lang }}" autocomplete="off"
                   value="{{ $lang }}" @if ($loop->index == 0) checked @endif>
            <label class="btn btn-primary lang-btn" for="lang-{{ $lang }}">{{ strtoupper($lang) }}</label>
        @endforeach
    </div>
</div>
