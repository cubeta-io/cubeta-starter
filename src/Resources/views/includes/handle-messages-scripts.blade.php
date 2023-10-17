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
            $('#generator-form').submit(function () {
                modal.show();
            })

            $('#install-spatie').click(function () {
                document.getElementById('modal-title').innerText = "Installing Spatie/Permissions"
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
