<footer id="footer" class="footer">

</footer>
<script>
    const _CSRF_TOKEN = "{{csrf_token()}}";
</script>

<script src="{{ asset('js/main.js') }}"></script>
<script src="{{asset('js/PluginsInitializer.js')}}"></script>
<script src="{{asset('js/CustomFunctions.js')}}"></script>

<script type="module">
    $(document).ready(function () {
        markRequiredFields();
        initPluginsByClass();
        handleImageGalleryExistence() ;
    });
    disableSubmitUntilFillRequiredFields();
</script>
@stack('scripts')
</body>
</html>
