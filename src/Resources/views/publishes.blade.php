@extends('CubetaStarter::layout')
@section('content')
    @include('CubetaStarter::includes.sidebar')
    <main>
        <div class="row w-100">
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            Publishes
                        </h3>
                    </div>
                    <div class="card-body w-100">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <p>Publish BaseRepository Classes</p>
                                <a href="{{route('cubeta-starter.repositories-publish')}}">
                                    <button class="btn btn-sm btn-primary">Publish</button>
                                </a>
                                <hr>
                            </div>
                            <div class="col-md-6">
                                <p>Publish BaseService Classes</p>
                                <a href="{{route('cubeta-starter.publish-services')}}">
                                    <button class="btn btn-sm btn-primary">Publish</button>
                                </a>
                                <hr>
                            </div>

                            <div class="col-md-6 mb-2">
                                <p>Publish Middlewares</p>
                                <a href="{{route('cubeta-starter.publish-middlewares')}}">
                                    <button class="btn btn-sm btn-primary">Publish</button>
                                </a>
                                <hr>
                            </div>

                            <div class="col-md-6 mb-2">
                                <p>Publish Helpers</p>
                                <a href="{{route('cubeta-starter.publish-helpers')}}">
                                    <button class="btn btn-sm btn-primary">Publish</button>
                                </a>
                                <hr>
                            </div>

                            <div class="col-md-6 mb-2">
                                <p>Publish Validation Rules</p>
                                <a href="{{route('cubeta-starter.publish-validation-rules')}}">
                                    <button class="btn btn-sm btn-primary">Publish</button>
                                </a>
                                <hr>
                            </div>

                            <div class="col-md-6 mb-2">
                                <p>Publish Traits</p>
                                <a href="{{route('cubeta-starter.publish-traits')}}">
                                    <button class="btn btn-sm btn-primary">Publish</button>
                                </a>
                                <hr>
                            </div>

                            <div class="col-md-6 mb-2">
                                <p>Publish Service Providers</p>
                                <p class="description-font">Note : Don't forget to register them in config/app.php</p>
                                <a href="{{route('cubeta-starter.publish-providers')}}">
                                    <button class="btn btn-sm btn-primary">Publish</button>
                                </a>
                                <hr>
                            </div>
                            <div class="col-md-6 mb-2">
                                <p>Publish ApiController Class</p>
                                <p class="description-font">Note : this class is required if you are trying to create an
                                    api</p>
                                <a href="{{route('cubeta-starter.publish-api-controller')}}">
                                    <button class="btn btn-sm btn-primary">Publish</button>
                                </a>
                                <hr>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center align-items-center">
                            <a href="{{route('cubeta-starter.publish-all')}}">
                                <button class="btn btn-sm btn-primary">Publish All</button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

@endsection

