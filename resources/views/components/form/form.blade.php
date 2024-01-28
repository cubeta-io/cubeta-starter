@props(['formTitle' , 'action' , 'method' => 'POST' , 'validationErrorsFromHere' => false])

@extends('layout')
@section('content')
    <main id="main" class="main">
        <div class="page-title">
            <h1>{{$formTitle}}</h1>
        </div>
        <section class="section profile">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body pt-3">

                            <!--validation errors-->
                            @if($validationErrorsFromHere)
                                @if($errors->any())
                                    <div class="card p-1">
                                        <div class="card-body">
                                            <ul>
                                                @foreach($errors->all() as $error)
                                                    <li style="color: red">
                                                        {{ $error }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            <!--end of validation errors-->

                            <form id="form" action="{{$action}}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf

                                @if($method == 'PUT' || $method == 'put')
                                    @method('PUT')
                                @endif

                                <div class="row">


                                    {{$slot}}


                                </div>

                                <div class="text-center">
                                    <button id="submit-btn" type="submit" class="btn btn-primary btn-lg btn-block">
                                        Submit
                                    </button>
                                </div>

                            </form>


                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script type="module">
        const radioButtons = document.querySelectorAll('input[name="selected-language"]');
        if (radioButtons) {
            const translatableInputs = document.querySelectorAll('.translatable');
            if (translatableInputs.length > 0) {
                radioButtons.forEach(function(radioButton) {
                    radioButton.addEventListener('change', function() {
                        const selectedLanguage = this.value; // Get the value of the selected radio button

                        translatableInputs.forEach(function(input) {
                            const inputName = input.name;
                            const inputId = input.id;
                            const languageCode = inputName.match(/\[(.*?)\]/)[1]; // Extract language code from input name


                            if (languageCode === selectedLanguage) {
                                if (input.tagName.toLowerCase() === 'textarea') {
                                    input.labels[0].style.display = 'block';
                                    document.querySelector('label[for="' + inputId + '"]').style.display = '';
                                    tinymce.get(inputId).show()
                                } else {
                                    input.style.display ='block'; // Show input for selected language
                                    input.labels[0].style.display = 'block';
                                }
                            } else {
                                if (input.tagName.toLowerCase() === 'textarea') {
                                    input.labels[0].style.display = "none";

                                    document.querySelector('label[for="' + inputId + '"]').style.display = 'none';

                                    // the order of the next two rows is very important
                                    // check the tinymce docs for hide() method : https://www.tiny.cloud/docs/tinymce/latest/apis/tinymce.editor/#hide
                                    tinymce.get(inputId).hide()
                                    input.style.display = 'none';

                                } else {
                                    input.style.display = "none"; // Hide input for other languages
                                    input.labels[0].style.display = "none";
                                }
                            }
                        });
                    });
                });
            }
        }
    </script>
@endsection
