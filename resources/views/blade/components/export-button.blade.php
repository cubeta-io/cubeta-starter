@props(['cols', 'route'])

<div>
    <button type="button" class="btn btn-success {{ $attributes->merge() }}" data-bs-toggle="modal"
            data-bs-target="#export-modal">
        <i class="bi bi-file-earmark-arrow-down"></i>
    </button>
    <div class="modal fade" id="export-modal" tabindex="-1" aria-labelledby="export-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="export-modal-label">Export To Excel</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="exporter-form">
                        <div class="row">
                            @foreach ($cols as $col)
                                <div class="col-m-6 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="{{ $col }}"
                                               name="columns[{{ $loop->index }}]" id="{{ $col }}-check">
                                        <label class="form-check-label" for="{{ $col }}-check">
                                            {{ \Illuminate\Support\Str::title(\Illuminate\Support\Str::replace(['.', '-', '_'], ' ', $col)) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="export-button">Export</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="spinner" tabindex="-1" aria-labelledby="spinnerLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="spinnerLabel">Exporting ...</h1>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="lds-dual-ring"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="display: none" id="redundant-container"></div>
</div>

@push('scripts')
    <script type="module">
        $(document).ready(function () {
            let spinnerModal = document.getElementById('spinner');
            let exportModal = document.getElementById('export-modal');

            exportModal = new Modal(
                exportModal, {
                    keyboard: true,
                    backdrop: "static"
                });
            spinnerModal = new Modal(spinnerModal, {
                keyboard: false,
                backdrop: "static"
            });
            spinnerModal.hide();

            $("#export-button").click(function () {
                const form = $("#exporter-form");
                spinnerModal.show();
                exportModal.hide();

                $.ajax({
                    type: "POST",
                    url: "{{ $route }}",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    data: form.serialize(),
                    xhrFields: {
                        responseType: 'blob' // Specify that the response is a binary blob
                    },
                    success: function (response, status, xhr) {
                        downloadResponseFile(response, xhr);
                        spinnerModal.hide();
                        exportModal.hide();
                    },
                    error: function (err) {
                        console.log(err);
                        spinnerModal.hide();
                        exportModal.hide();
                        triggerSwalError("There Is Been An Error");
                    }
                });
            });

            $(document).on('keydown', function (event) {
                if (event.key === 'Escape') {
                    spinnerModal.hide();
                }
            });
        });
    </script>
@endpush
