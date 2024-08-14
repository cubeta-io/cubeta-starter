<div id="terminal-wrapper">
    <div class="resizer"></div>
    <div class="terminal-header">
        <div>
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
            </button>
        </div>
        <button id="clear-terminal" class="btn btn-sm btn-danger">@include('CubetaStarter::icons.trash')</button>
    </div>
    <div id="terminal">
        @php
            $logs = collect(\Illuminate\Support\Facades\Cache::get('logs') ?? []) ;
        @endphp
        @if(count($logs))
            @foreach($logs as $log)
                @if(is_string($log))
                    <div
                        class='my-5 p-3 w-100 d-flex gap-1 p-2 flex-column justify-content-between border border-success rounded-3 border-2'
                        style='position: relative'>
                        <span style='position: absolute; top: -25%; left: 1%' class='bg-success rounded-2 p-1 fw-bold'>Info</span>
                        <div class='w-100'>{{$log}}</div>
                    </div>
                @else
                    {!! $log->getHtml() !!}
                @endif
            @endforeach
        @else
            > Generating Logs ...
        @endif
    </div>
    <div class="resizer"></div>
</div>

@push('custom-scripts')
    <script type="module">
        $(document).ready(function () {
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
