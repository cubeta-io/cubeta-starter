@props(['cols', 'route'])
<button type="button" class="btn btn-primary {{ $attributes->merge() }}" data-bs-toggle="modal"
        data-bs-target="#export-modal">
    Export
</button>
<div class="modal fade" id="export-modal" tabindex="-1" aria-labelledby="export-modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="export-modal-label">Modal title</h1>
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

<script type="module">
    $(document).ready(function () {
        let spinnerModal = document.getElementById('spinner');
        let exportModal = document.getElementById('export-modal');

        exportModal = new bootstrap.Modal(
            exportModal, {
                keyboard: true,
                backdrop: "static"
            });
        spinnerModal = new bootstrap.Modal(spinnerModal, {
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
                success: function (data) {
                    window.location.href = data.url;
                    spinnerModal.hide();
                },
                error: function (data) {
                    console.log(data);
                    spinnerModal.hide();
                    exportModal.hide();
                    console.error(data);
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
