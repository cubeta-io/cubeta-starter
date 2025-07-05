@php
    $logs = collect(\Illuminate\Support\Facades\Cache::get('logs') ?? [])->reverse() ;
@endphp
<div id="terminal-wrapper">
    <div class="resizer"></div>
    <div class="terminal-header">
        <div class="d-flex align-items-center justify-content-start gap-2">
            <button id="slide-down"
                    class="btn btn-sm"
                    style="background-color: var(--brand-primary)"
            >
                <div id="chevron_up">
                    @include('CubetaStarter::icons.chevron-up')
                </div>
                <div id="chevron_down">
                    @include('CubetaStarter::icons.chevron-down')
                </div>
                <button id="all-button" class="btn btn-sm btn-secondary">All</button>
                <button id="info-button" class="btn btn-sm btn-success">Info</button>
                <button id="warning-button" class="btn btn-sm btn-warning">Warnings</button>
                <button id="errors-button" class="btn btn-sm btn-danger">Errors</button>
            </button>
        </div>
        <button id="clear-terminal" class="btn btn-sm btn-danger">@include('CubetaStarter::icons.trash')</button>
    </div>
    <div id="terminal">
        @if(count($logs))
            <div id="all" class="d-flex flex-column justify-content-between gap-5">
                @foreach($logs as $log)
                    @if(is_string($log))
                        <div class='p-3 w-100 p-2 border border-success rounded-3 border-2'>
                            <div class='w-100'>{{$log}}</div>
                        </div>
                    @else
                        {!! $log->getHtml() !!}
                    @endif
                @endforeach
            </div>
            <div id="info" class="d-flex flex-column justify-content-between gap-5">
                @foreach($logs->filter(fn ($item) => ($item instanceof \Cubeta\CubetaStarter\Logs\CubeInfo)) as $log)
                    {!! $log->getHtml() !!}
                @endforeach
            </div>
            <div id="warnings" class="d-flex flex-column justify-content-between gap-5">
                @foreach($logs->filter(fn ($item) => $item instanceof \Cubeta\CubetaStarter\Logs\CubeWarning) as $log)
                    {!! $log->getHtml() !!}
                @endforeach
            </div>
            <div id="errors" class="d-flex flex-column justify-content-between gap-5">
                @foreach($logs->filter(fn ($item) => $item instanceof \Cubeta\CubetaStarter\Logs\CubeError) as $log)
                    {!! $log->getHtml() !!}
                @endforeach
            </div>
        @else
            > Generating Logs ...
        @endif
    </div>
    <div class="resizer"></div>
</div>

@push('custom-scripts')
    <script type="module">
        $(document).ready(function () {
            const info = $("#info")
            const warnings = $("#warnings")
            const errors = $("#errors")
            const all = $("#all");

            info.toggle('hidden')
            warnings.toggle('hidden')
            errors.toggle('hidden')

            $("#all-button").on('click', function (e) {
                e.preventDefault();
                all.show('hidden');
                info.hide('hidden');
                warnings.hide('hidden');
                errors.hide('hidden');
            })

            $("#info-button").on('click', function (e) {
                e.preventDefault();
                info.show('hidden');
                all.hide('hidden');
                warnings.hide('hidden');
                errors.hide('hidden');
            })

            $("#warning-button").on('click', function (e) {
                e.preventDefault();
                warnings.show('hidden');
                info.hide('hidden');
                all.hide('hidden');
                errors.hide('hidden');
            })

            $("#errors-button").on('click', function (e) {
                e.preventDefault();
                errors.show('hidden');
                info.hide('hidden');
                all.hide('hidden');
                warnings.hide('hidden');
            })

            const chevronUp = $("#chevron_up");
            chevronUp.toggle('hidden');

            let isResizing = false;
            const terminal = $('#terminal');
            let startY, startHeight;
            const chevronDown = $("#chevron_down");

            $('.resizer').on('mousedown', function (e) {
                isResizing = true;
                startY = e.clientY;
                terminal.show('scale-up-ver-bottom')
                startHeight = terminal.outerHeight();
                $(document).on('mousemove', resizeTerminal);
                $(document).on('mouseup', stopResize);
            });

            function resizeTerminal(e) {
                if (isResizing) {
                    const newHeight = startHeight + (startY - e.clientY);
                    terminal.css('height', newHeight + 'px');
                }
            }

            function stopResize() {
                isResizing = false;
                $(document).off('mousemove', resizeTerminal);
                $(document).off('mouseup', stopResize);
            }

            $('#slide-down').click(function () {
                terminal.toggle('scale-up-ver-bottom');
                chevronDown.toggle('hidden');
                chevronUp.toggle('hidden');
            });

            $("#clear-terminal").on('click', function () {
                fetch('{{route('cubeta.starter.clear.logs')}}', {
                    headers: {
                        'Accept': "application/json",
                    }
                }).then(() => {
                    $("#terminal").html('> Generating Logs ...');
                })
            })
        });
    </script>
@endpush
