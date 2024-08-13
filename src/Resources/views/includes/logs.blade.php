<div id="terminal-wrapper">
    <div class="resizer"></div>
    <div class="terminal-header">
        <div>
            <button id="slide-down"
                    class="btn btn-sm btn-secondary"
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
        > Generating Logs ...
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

            $('#clear-terminal').click(function () {
                terminal.text('');  // Clear the terminal content
            });
        });
    </script>
@endpush
