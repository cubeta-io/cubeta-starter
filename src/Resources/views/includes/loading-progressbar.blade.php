<div id="progress-bar" class="progress"
     style="height: 5px; display: none; position: fixed; top: 0; left: 0; width: 100%; z-index: 9999;">
    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
         style="width: 0;background-color: var(--brand-primary)"></div>
</div>

@push('custom-scripts')
    <script type="module">
        $(document).ready(function () {
            // Function to start the progress bar animation
            function startProgressBar(every = 1000) {
                $('#progress-bar').show();
                $('.progress-bar').css('width', '10%');
                animateProgressBar(every);
            }

            // Function to animate the progress bar
            function animateProgressBar(every = 1000) {
                let width = 0;
                const interval = setInterval(function () {
                    if (width < 90) { // Cap the progress at 90% until the page load completes
                        width += 3;
                        $('.progress-bar').css('width', width + '%');
                    }
                }, every);
            }

            // Trigger progress bar on link clicks
            $('a').on('click', function (e) {
                // Ignore links with specific targets (e.g., external links or modal triggers)
                if ($(this).attr('target') !== '_blank' && !$(this).data('toggle')) {
                    startProgressBar(1);
                }
            });

            // Trigger progress bar on form submissions
            $('form').on('submit', function () {
                startProgressBar();
            });

            // Handle AJAX start and stop events (for AJAX requests)
            $(document).ajaxStart(function () {
                startProgressBar();
            });

            $(document).ajaxStop(function () {
                $('.progress-bar').css('width', '100%');
                setTimeout(function () {
                    $('#progress-bar').fadeOut();
                }, 1000);
            });
        });
    </script>
@endpush
