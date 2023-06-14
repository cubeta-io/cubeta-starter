@extends('CubetaStarter::layout')
@section('content')
    @include('CubetaStarter::includes.sidebar')

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
                            <div class="col-md-6 m-auto">
                                <h4 class="form-label m-auto">Publish the Package Config File</h4>
                                <br>
                                <a id="publish-config"
                                   href="{{route('cubeta-starter.config-publish')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Config
                                </a>
                            </div>
                            <div class="col-md-6 m-auto">
                                <h4 class="form-label m-auto">Publish the provided exception handler</h4>
                                <br>
                                <a id="publish-handler"
                                   href="{{route('cubeta-starter.publish-handler')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Handler
                                </a>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h4 class="form-label m-auto">Publish The Package Assets</h4>
                                <br>
                                <p class="description-font">
                                    When using the web controllers on every created controller there will be
                                    corresponding views generated with it, which use some css,js files and our blade
                                    components
                                </p>
                                <a id="publish-assets"
                                   href="{{route('cubeta-starter.publish-assets')}}"
                                   class="btn btn-primary w-auto">
                                    Publish Assets
                                </a>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h4 class="form-label m-auto">Installing web packages</h4>
                                <br>
                                <p class="description-font">
                                    this will install the following :
                                </p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul>
                                            <li>laravel-datatables-vite</li>
                                            <li>select2</li>
                                            <li>select2-bootstrap-5-theme</li>
                                            <li>bootstrap 5.2.3</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul>
                                            <li>baguettebox.js</li>
                                            <li>jquery</li>
                                            <li>trumbowyg</li>
                                            <li>sweetalert2</li>
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
                Swal.fire({
                    title: "Error",
                    text: "{{ request('error') }}",
                    icon: "error",
                    button: "OK",
                }).then(() => {
                    window.location.href = "{{url()->previous()}}";
                });
            </script>
        @endpush
    @endif

    @if(request('success'))
        @push('scripts')
            <script type="module">
                Swal.fire({
                    title: "Success",
                    text: "{{ request('success') }}",
                    icon: "success",
                    button: "OK",
                }).then(() => {
                    window.location.href = "{{url()->previous()}}";
                });
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
