@extends('CubetaStarter::layout')
@section('content')
    @include('CubetaStarter::includes.sidebar')

    <style>
        .border.border-2 {
            margin: 25px !important;
            padding: 10px !important;
            border-radius: 10px;
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
                                <h4 class="form-label">Publish Service Providers</h4>
                                <br>
                                <p class="description-font">The package will publish one service provider to make sure
                                    that the package working
                                    under the 0 dependency principle.<br>
                                    You Need to register it in the <code>config/app.php</code>
                                    directory
                                </p>
                                <a id="publish-providers"
                                   href="{{route('cubeta-starter.publish-providers')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Service Providers
                                </a>
                            </div>

                            <div class="col-md-5 border border-2">
                                <h4 class="form-label">Publish Response Handlers</h4>
                                <br>
                                <p class="description-font">
                                    This will publish :
                                </p>
                                <ul>
                                    <li>new exception handler to <code>app/Exceptions</code> and replace it with the
                                        default one
                                    </li>

                                    <li>
                                        RestTrait which is a helpful trait that will unify your api responses
                                    </li>

                                    <li>
                                        APIController class which will add more functionality and the ability to unify
                                        your controllers and responses
                                    </li>
                                </ul>
                                <p class="description-font">
                                    make use of this tools will make your development experience easier and more
                                    maintainable
                                </p>
                                <a id="publish-response-handlers"
                                   href="{{route('cubeta-starter.publish-response-handlers')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Response Handlers
                                </a>
                            </div>

                            <div class="col-md-5 border border-2">
                                <h4 class="form-label">Publish CRUD Helper Classes</h4>
                                <br>
                                <p class="description-font">
                                    This will publish :
                                </p>
                                <ul>
                                    <li>BaseRepository class and its interface to :
                                        <code>app/Repositories/Contracts</code></li>

                                    <li>
                                        BaseService class with its interface to : <code>app/Services/Contracts</code>
                                    </li>

                                    <li>
                                        FileHandlerTrait to : <code>app/Traits</code> which will help you to deal with
                                        images and files
                                    </li>
                                </ul>
                                <a id="publish-crud-handlers"
                                   href="{{route('cubeta-starter.publish-crud-handlers')}}"
                                   class="btn btn-primary w-auto">
                                    Publish CRUD Helpers
                                </a>
                            </div>

                            <div class="col-md-5 border border-2">
                                <h4 class="form-label">Publish Locale Handlers</h4>
                                <br>
                                <p class="description-font">
                                    This will publish :
                                </p>
                                <ul>
                                    <li>AcceptedLanguageMiddleware to :
                                        <code>app/Http/Middlewares</code>
                                        <span style="font-weight: bolder;" class="text-danger">
                                            Don't Forget to register it in the
                                            <code>app/Http/kernel.php</code>
                                        </span>
                                    </li>

                                    <li>
                                        LanguageShape validation rule to :
                                        <code>app/Rules</code>
                                        this will help you validate the requested translated field which is an array
                                    </li>

                                    <li>
                                        Translation Trait to :
                                        <code>app/Traits</code>
                                        this will add helper method to your models which has translated columns by just
                                        add it to the desired model
                                    </li>
                                </ul>
                                <a id="publish-locale-handlers"
                                   href="{{route('cubeta-starter.publish-locale-handlers')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Locale Helpers
                                </a>
                            </div>

                            <div class="col-md-5 border border-2">
                                <h4 class="form-label m-auto">Publish Testing Tools</h4>
                                <br>
                                <p class="description-font">Note : this tools required for the generated tests</p>
                                <a id="publish-testing-tools"
                                   href="{{route('cubeta-starter.publish-testing-tools')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Testing Tools
                                </a>
                            </div>


                            <a href="{{route('cubeta-starter.publish-all')}}" class="my-3" id="publish-all">
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
                                <li class="notes">publishing the config file is necessary
                                    for the package to work fine
                                </li>
                                <li class="notes">publishing the package assets will publish our assets inside the
                                    resources and the public directories of your project
                                </li>
                                <li class="notes">installing web packages require you to have npm installed on your
                                    machine and may take long time depends on your internet
                                </li>
                                <li class="notes">publishing the package assets and installing the web packages is
                                    required when using web controllers
                                </li>
                                <li class="notes">
                                    We recommend to publish all publishable files <code>(except the package assets unless
                                        you want to use it for web based generation)</code>
                                    because a full generation process require all of those files
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>


    @include('CubetaStarter::includes.handle-messages-scripts')

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
                $('#publish-response-handlers').click(function () {
                    document.getElementById('modal-title').innerText = "publishing response handlers"
                    modal.show();
                })

                $('#publish-config').click(function () {
                    document.getElementById('modal-title').innerText = "publishing config files"
                    modal.show();
                })

                $('#publish-providers').click(function () {
                    document.getElementById('modal-title').innerText = "Publishing Service Providers"
                    modal.show();
                })

                $('#publish-crud-handlers').click(function () {
                    document.getElementById('modal-title').innerText = "Publishing CRUD Handlers"
                    modal.show();
                })

                $('#publish-locale-handlers').click(function () {
                    document.getElementById('modal-title').innerText = "Publishing Locale Handlers"
                    modal.show();
                })

                $('#publish-testing-tools').click(function () {
                    document.getElementById('modal-title').innerText = "Publishing Testing Tools"
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
