@props([
    "route",
    "exroute",
])

<div>
    <button
        type="button"
        class="btn btn-secondary"
        data-bs-toggle="modal"
        data-bs-target="#importModal"
    >
        <i class="bi bi-file-earmark-arrow-up"></i>
    </button>

    <div
        class="modal fade"
        id="importModal"
        tabindex="-1"
        aria-labelledby="importModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="importModalLabel">
                        Import From Excel
                    </h1>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <form
                    action="{{ $route }}"
                    id="import-form"
                    enctype="multipart/form-data"
                >
                    <div class="modal-body">
                        <label class="w-100">Excel File :</label>
                        <input
                            class="w-100 form-control"
                            type="file"
                            name="excel_file"
                            id="excel-input"
                        />
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Close
                        </button>
                        <button
                            type="button"
                            class="btn btn-success"
                            id="download-example-button"
                        >
                            Download Example
                        </button>
                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="do-import-btn"
                        >
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div
        class="modal fade"
        id="importSpinner"
        tabindex="-1"
        aria-labelledby="importSpinnerLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="importSpinnerLabel">
                        Please Wait ...
                    </h1>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="lds-dual-ring"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push("scripts")
    <script type="module">
        $(document).ready(function () {
            let importModal = $('#importModal');
            let importSpinnerModal = $('#importSpinner');

            importModal = new Modal(importModal, {
                keyboard: true,
                backdrop: 'static',
            });
            importSpinnerModal = new Modal(importSpinnerModal, {
                keyboard: false,
                backdrop: 'static',
            });

            $('#import-form').on('submit', function (e) {
                e.preventDefault();
                const formData = new FormData($(this)[0]);

                importSpinnerModal.show();
                importModal.hide();

                $.ajax({
                    type: 'POST',
                    url: '{{ $route }}',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    data: formData,
                    processData: false, // Prevent jQuery from automatically processing the data
                    contentType: false, // Prevent jQuery from automatically setting the content type
                    success: function () {
                        importSpinnerModal.hide();
                        importModal.show();
                        triggerSwalSuccess('Imported Successfully');
                    },
                    error: function () {
                        importSpinnerModal.hide();
                        importModal.hide();
                        triggerSwalError('There Is Been An Error');
                    },
                });
            });

            $('#download-example-button').on('click', function () {
                importSpinnerModal.show();
                importModal.hide();

                $.ajax({
                    type: 'GET',
                    url: '{{ $exroute }}',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    xhrFields: {
                        responseType: 'blob', // Specify that the response is a binary blob
                    },
                    success: function (data, status, xhr) {
                        downloadResponseFile(data, xhr);
                        importSpinnerModal.hide();
                    },
                    error: function () {
                        importSpinnerModal.hide();
                        importModal.hide();
                    },
                });
            });
        });
    </script>
@endpush
