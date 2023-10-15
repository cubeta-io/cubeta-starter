@extends('CubetaStarter::layout')
@section('content')
    @include('CubetaStarter::includes.sidebar')

    <style>
        .border.border-2 {
            margin: 25px !important;
            padding: 10px !important;
        }
    </style>
    <main class="main">
        <section class="section profile">
            <div class="container">
                <div class="modal mt-5" tabindex="-1" role="dialog" id="spinner" data-keyboard="false"
                     data-backdrop="static">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content bg-white">
                            <div class="modal-body">
                                <h3 id="modal-title" class="text-center"></h3>
                                <div class="card d-flex justify-content-center align-items-center bg-white"
                                     style="border: none">
                                    <div class="card-body w-25 h-25 text-center" style="border-radius: 15px">
                                        <div class="lds-dual-ring"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header text-center">
                        <div class="card-header text-center">
                            <h1>Complete the installation</h1>
                            <p>publish the package assets and install the desired packages based on your use of our
                                package</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5 border border-2">
                                <h4 class="form-label m-auto">Publish the Package Config Files</h4>
                                <br>
                                <p class="description-font">
                                    this will publish <span style="font-weight: bold">"cubeta-starter.php"</span>
                                    config file to the project config path in
                                    addition to the Laravel Pint configuration file <span style="font-weight: bold">"pint.json"</span>
                                    to the project root directory which necessary for the package to work properly
                                </p>
                                <br>
                                <a id="publish-config"
                                   href="{{route('cubeta-starter.config-publish')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Config
                                </a>
                            </div>
                            <div class="col-md-5 border border-2">
                                <h4 class="form-label m-auto">Publish the provided exception handler</h4>
                                <br>
                                <p class="description-font">
                                    this will publish our exception handler which will organize your responses
                                </p>
                                <a id="publish-handler"
                                   href="{{route('cubeta-starter.publish-handler')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Handler
                                </a>
                            </div>
                            <div class="col-md-5 border border-2">
                                <h4 class="form-label m-auto">Publish The Package Assets</h4>
                                <br>
                                <p class="description-font">
                                    When using the web controllers on every created controller there will be
                                    corresponding views generated with it, which use some css,js files and our blade
                                    components , in addition to a SetLocaleController to the controller path
                                    <span style="font-weight: bold">(this is for web generating based usage not for the api generating usage)</span>
                                </p>
                                <a id="publish-assets"
                                   href="{{route('cubeta-starter.publish-assets')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Assets
                                </a>
                            </div>
                            <div class="col-md-5 border border-2">
                                <h4 class="form-label m-auto">
                                    Publish BaseRepository Classes
                                </h4>
                                <br>
                                <p class="description-font">
                                    this will publish the BaseRepository class with its interface
                                    this class is required for the generated files
                                </p>
                                <a id="publish-assets"
                                   href="{{route('cubeta-starter.repositories-publish')}}"
                                   class="btn btn-primary w-auto">
                                    Publish BaseRepository class
                                </a>
                            </div>
                            <div class="col-md-5 border border-2">
                                <h4 class="form-label m-auto">Publish BaseService Classes</h4>
                                <br>
                                <p class="description-font">this will publish the BaseService class with its interface
                                    this class is required for the generated files</p>
                                <a id="publish-assets"
                                   href="{{route('cubeta-starter.publish-services')}}"
                                   class="btn btn-primary w-auto">
                                    Publish BaseService class
                                </a>
                            </div>

                            <div class="col-md-5 border border-2">
                                <h4 class="form-label m-auto">Publish Middlewares</h4>
                                <br>
                                <p class="description-font">
                                    this will publish AcceptedLanguageMiddleware which will change the app locale
                                    depends on the defined locales in the package config file and either the
                                    "SetLocaleController" or the "Accept-Language" header
                                    remember to register it in the <code>$middleware</code> array in the <code>app/Http/kernel.php</code>
                                </p>
                                <a id="publish-middlewares"
                                   href="{{route('cubeta-starter.publish-middlewares')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Middlewares
                                </a>
                            </div>

                            <div class="col-md-5 border border-2">
                                <h4 class="form-label m-auto">Publish Validation Rules</h4>
                                <br>
                                <p class="description-font">
                                    For Now this command will publish just one rule, and it is LanguageShape rule class
                                    which is useful when using the Translations trait to make sure that the incoming
                                    translated input value isn't a nested array and its keys exist within the defined
                                    app locales
                                </p>
                                <a id="publish-middlewares"
                                   href="{{route('cubeta-starter.publish-traits')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Traits
                                </a>
                            </div>

                            <div class="col-md-5 border border-2">
                                <h4 class="form-label m-auto">Publish Traits</h4>
                                <br>
                                <p class="description-font">
                                    We've provided plenty of helpful traits you can check on them in the <span
                                        class="bg-secondary">app\Traits</span> directory or go to the documentation
                                </p>
                                <a id="publish-traits"
                                   href="{{route('cubeta-starter.publish-validation-rules')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Validation Rules
                                </a>
                            </div>

                            <div class="col-md-5 border border-2">
                                <h4 class="form-label">Publish Service Providers</h4>
                                <br>
                                <p class="description-font">The package will publish one service provider to make sure
                                    that the package working
                                    under the 0 dependency principle.<br>
                                    You Need to register it in the <span class="bg-secondary">config/app.php</span>
                                    directory
                                </p>
                                <a id="publish-providers"
                                   href="{{route('cubeta-starter.publish-providers')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Service Providers
                                </a>
                            </div>
                            <div class="col-md-5 border border-2">
                                <h4 class="form-label m-auto">Publish ApiController Class</h4>
                                <br>
                                <p class="description-font">Note : this class is required if you are trying to create an
                                    api</p>
                                <a id="publish-providers"
                                   href="{{route('cubeta-starter.publish-api-controller')}}"
                                   class="btn btn-primary w-auto">
                                    Publish ApiController
                                </a>
                            </div>
                            <a href="{{route('cubeta-starter.publish-all')}}" class="my-3">
                                <button class="btn btn-primary w-100">Publish All</button>
                            </a>
                        </div>

                        <div class="col-md-12 border border-2">
                            <h4 class="form-label m-auto">Installing web packages</h4>
                            <br>
                            <p class="description-font">
                                this will install the following :
                            </p>
                            <div class="row">
                                <div class="col-md-5">
                                    <ul>
                                        <li>datatables.net-fixedcolumns-bs5</li>
                                        <li>datatables.net-fixedheader-bs5</li>
                                        <li>select2-bootstrap-5-theme</li>
                                        <li>datatables.net-buttons</li>
                                        <li>laravel-datatables-vite</li>
                                        <li>bootstrap 5.2.3</li>
                                    </ul>
                                </div>
                                <div class="col-md-5">
                                    <ul>
                                        <li>baguettebox.js</li>
                                        <li>sweetalert2</li>
                                        <li>tinymce</li>
                                        <li>select2</li>
                                        <li>jquery</li>
                                        <li>Sass</li>
                                    </ul>
                                </div>
                            </div>
                            <a id="install-web-packages"
                               href="{{route('cubeta-starter.install-web-packages')}}"
                               class="btn btn-primary w-auto">
                                install web packages
                            </a>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex align-items-center justify-content-center">
                            <ul>
                                <li class="notes">publishing the config file and the exception handler is necessary
                                    for the package to work fine
                                </li>
                                <li class="notes">publishing the package assets will publish our assets inside the
                                    resources and the public directories of your project
                                </li>
                                <li class="notes">installing web packages require you to have npm installed on your
                                    machine and may take long time depends on your internet
                                </li>
                                <li class="notes">publishing config files and the exception handler is very important to
                                    the package to work properly
                                </li>
                                <li class="notes">publishing the package assets and installing the web packages is
                                    required when using web controllers
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @if(request('error'))
        @push('scripts')
            <script type="module">
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                    title: 'Error',
                    text: "{{ request('error') }}",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Ok',
                    cancelButtonText: 'Show Log',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{url()->previous()}}";
                    } else if (result.isDismissed) {
                        window.location.href = "{{route('cubeta-starter.output')}}";
                    }
                })
            </script>
        @endpush
    @endif

    @if(request('success'))
        @push('scripts')
            <script type="module">
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                    title: 'Success',
                    text: "{{ request('success') }}",
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Ok',
                    cancelButtonText: 'Show Log',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{url()->previous()}}";
                    } else if (result.isDismissed) {
                        window.location.href = "{{route('cubeta-starter.output')}}";
                    }
                })
            </script>
        @endpush
    @endif

    @if(request('warning'))
        @push('scripts')
            <script type="module">
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                    title: 'Warning',
                    text: "{{ request('warning') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ok',
                    cancelButtonText: 'Show Log',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{url()->previous()}}";
                    } else if (result.isDismissed) {
                        window.location.href = "{{route('cubeta-starter.output')}}";
                    }
                })
            </script>
        @endpush
    @endif

    @push('scripts')
        <script type="module">
            $(document).ready(function () {
                const myModal = document.getElementById('spinner');
                const modal = new bootstrap.Modal(myModal, {
                    keyboard: false,
                    backdrop: "static"
                });
                modal.hide();

                $('#install-web-packages').click(function () {
                    document.getElementById('modal-title').innerText = "installing web packages"
                    modal.show();
                })
                $('#publish-assets').click(function () {
                    document.getElementById('modal-title').innerText = "publishing assets"
                    modal.show();
                })
                $('#publish-handler').click(function () {
                    document.getElementById('modal-title').innerText = "publishing exception handler"
                    modal.show();
                })
                $('#publish-config').click(function () {
                    document.getElementById('modal-title').innerText = "publishing config file"
                    modal.show();
                })

                $(document).on('keydown', function (event) {
                    if (event.key === 'Escape') {
                        modal.hide();
                    }
                });
            });
        </script>
    @endpush

@endsection
