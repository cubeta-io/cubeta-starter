<footer id="footer" class="footer"></footer>
<script>
    const _CSRF_TOKEN = "{{ csrf_token() }}";
</script>

<script src="{{ asset('js/main.js') }}"></script>
<script src="{{ asset('js/PluginsInitializer.js') }}"></script>
<script src="{{ asset('js/CustomFunctions.js') }}"></script>

<script type="module">
    $(document).ready(function () {
        markRequiredFields();
        initPluginsByClass();
        handleImageGalleryExistence();
        changeLocale();
    });
    disableSubmitUntilFillRequiredFields();

    @if (session()->has('error'))
    triggerSwalError("{{ session()->get('error') }}");
    @php
        session()->remove('error');
    @endphp
    @endif

    @if (session()->has('success'))
    triggerSwalSuccess("{{ session()->get('success') }}");
    @php
        session()->remove('success');
    @endphp
    @endif

    @if (session()->has('message'))
    triggerSwalMessage('{{ session()->get('message') }}');
    @php
        session()->remove('success');
    @endphp
    @endif
</script>
@stack('scripts')
