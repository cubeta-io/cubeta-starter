@extends('CubetaStarter::layout')
@section('content')
    @include('CubetaStarter::includes.sidebar')

    @php
        $frontend = \Cubeta\CubetaStarter\App\Models\Settings\Settings::make()->getFrontendType();
    @endphp

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
                            <p>publish the package assets and install the desired packages based on your use of it</p>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($frontend)
                            <div class="row">
                                <div class="col-md-5 border border-2">
                                    <h4 class="form-label">Install For API Usage</h4>
                                    <br>
                                    <p class="description-font">
                                        This will install all the helper classes and traits for api based development
                                        within
                                        your
                                        project
                                    </p>

                                    <a id="publish-api"
                                       href="{{route('cubeta-starter.api.publish')}}"
                                       class="btn btn-primary w-auto">
                                        Install API
                                    </a>
                                </div>

                                @if($frontend == \Cubeta\CubetaStarter\Enums\FrontendTypeEnum::BLADE)
                                    <div class="col-md-5 border border-2">
                                        <h4 class="form-label m-auto">Install For Web Usage</h4>
                                        <br>
                                        <p class="description-font">
                                            This will install all the helper classes and traits for web based
                                            development within
                                            your
                                            project
                                        </p>
                                        <a id="publish-web"
                                           href="{{route('cubeta-starter.web.publish')}}"
                                           class="btn btn-primary w-auto">
                                            Install Web
                                        </a>
                                    </div>

                                    <div class="col-md-12 border border-2">
                                        <h3 class="form-label m-auto">Install npm packages</h3>
                                        <br>
                                        <p class="description-font fw-bold">
                                            Required when installing web based usage tools
                                        </p>
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
                                            install npm packages
                                        </a>
                                    </div>
                                @endif


                                @if($frontend == \Cubeta\CubetaStarter\Enums\FrontendTypeEnum::REACT_TS)
                                    <div class="col-md-5 border border-2">
                                        <h4 class="form-label m-auto">Install React TS Stack Tools</h4>
                                        <br>
                                        <p class="description-font">
                                            This will install all the helper classes and traits for React , Typescript ,
                                            Tailwind , Inertia in addition to the ui components which will help you have
                                            a better development experience
                                        </p>
                                        <a id="publish-react-ts"
                                           href="{{route('cubeta-starter.react.ts.publish')}}"
                                           class="btn btn-primary w-auto">
                                            Install React
                                        </a>
                                    </div>

                                    <div class="col-md-12 border border-2">
                                        <h3 class="form-label m-auto">Install react,typescript packages</h3>
                                        <br>
                                        <p class="description-font fw-bold">
                                            Required when installing React.ts Stack usage tools
                                        </p>
                                        <p class="description-font">
                                            this will install the following :
                                        </p>
                                        <div class="row">
                                            <div class="col-md-5">
                                                <ul>
                                                    <li>inertiajs/inertia-laravel</li>
                                                    <li>tightenco/ziggy</li>
                                                    <li>inertiajs/react</li>
                                                    <li>tailwindcss</li>
                                                    <li>tailwindcss/forms</li>
                                                    <li>types/node</li>
                                                    <li>types/react</li>
                                                    <li>types/react-dom</li>
                                                    <li>vitejs/plugin-react</li>
                                                    <li>postcss</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-5">
                                                <ul>
                                                    <li>react</li>
                                                    <li>react-dom</li>
                                                    <li>typescript</li>
                                                    <li>tinymce/tinymce-react</li>
                                                    <li>vitejs/plugin-react-refresh</li>
                                                    <li>autoprefixer</li>
                                                    <li>sweetalert2</li>
                                                    <li>sweetalert2-react-content</li>
                                                    <li>react-toastify</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <a id="install-react-ts-packages"
                                           href="{{route('cubeta-starter.install-react-ts-packages')}}"
                                           class="btn btn-primary w-auto">
                                            install react.ts packages
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <form method="POST" action="{{route('cubeta-starter.chose-frontend-stack')}}">
                                <label class="label">Chose Your Frontend Stack
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="stack"
                                               id="blade"
                                               value="{{\Cubeta\CubetaStarter\Enums\FrontendTypeEnum::BLADE->value}}">
                                        <label class="form-check-label" for="blade">
                                            Blade , Bootstrap , JQuery
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="stack"
                                               id="react_ts"
                                               value="{{\Cubeta\CubetaStarter\Enums\FrontendTypeEnum::REACT_TS->value}}"
                                        >
                                        <label class="form-check-label" for="react_ts">
                                            Inertia , React , Typescript , Tailwind
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="stack"
                                               id="none"
                                               value="{{\Cubeta\CubetaStarter\Enums\FrontendTypeEnum::NONE->value}}"
                                               checked>
                                        <label class="form-check-label" for="none">
                                            No Frontend Just API
                                        </label>
                                    </div>
                                </label>
                                <div class="d-flex justify-content-center align-items-center">
                                    <button class="btn btn-primary" type="submit">
                                        Submit
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="d-flex align-items-center justify-content-center">
                            <ul>
                                <li class="notes">installing web packages require you to have npm installed on your
                                    machine and may take long time depends on your internet
                                </li>
                                <li class="notes">Installing web usage tools requires you to install the provided npm
                                    packages
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

                $('#publish-react-ts').click(function () {
                    document.getElementById('modal-title').innerText = "installing react , typescript Based Usage Tools"
                    modal.show();
                })

                $('#install-react-ts-packages').click(function () {
                    document.getElementById('modal-title').innerText = "installing react , typescript packages"
                    modal.show();
                })

                $('#install-web-packages').click(function () {
                    document.getElementById('modal-title').innerText = "installing web packages"
                    modal.show();
                })

                $('#publish-api').click(function () {
                    document.getElementById('modal-title').innerText = "Installing Api Based Usage Tools"
                    modal.show();
                })
                $('#publish-web').click(function () {
                    document.getElementById('modal-title').innerText = "Installing Web Based Usage Tools"
                    modal.show();
                })

                $('#publish-all').click(function () {
                    document.getElementById('modal-title').innerText = "Installing Api And Web Tools"
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
