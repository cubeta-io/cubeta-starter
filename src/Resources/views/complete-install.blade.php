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
                <div class="card..">
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
                                <h4 class="form-label">Publish For API Usage</h4>
                                <br>
                                <p class="description-font">
                                    This will publish all the helper classes and traits for API development
                                </p>

                                <a id="publish-api"
                                   href="{{route('cubeta-starter.api.publish')}}"
                                   class="btn btn-primary w-auto">
                                    Publish API
                                </a>
                            </div>

                            <div class="col-md-5 border border-2">
                                <h4 class="form-label m-auto">Publish For Web Usage</h4>
                                <br>
                                <p class="description-font">
                                    This will publish all the helper classes , views , blade
                                    components and traits for
                                    Web development
                                </p>
                                <p class="description-font fw-bold text-danger">Don't forget to install the npm packages in the section below</p>
                                <a id="publish-web"
                                   href="{{route('cubeta-starter.web.publish')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Web
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
                                <li class="notes">installing web packages require you to have npm installed on your
                                    machine and may take long time depends on your internet
                                </li>
                                <li class="notes">publishing the web package require you to install the web packages
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
                $('#publish-api').click(function () {
                    document.getElementById('modal-title').innerText = "Publishing Api Based Usage Tools"
                    modal.show();
                })
                $('#publish-web').click(function () {
                    document.getElementById('modal-title').innerText = "Publishing Web Based Usage Tools"
                    modal.show();
                })

                $('#publish-all').click(function () {
                    document.getElementById('modal-title').innerText = "Publishing Api And Web Tools"
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
