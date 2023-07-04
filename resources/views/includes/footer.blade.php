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
        handleImageGalleryExistence();
    });
    disableSubmitUntilFillRequiredFields();
</script>
@if(session()->has('error'))
    <script type="module">
        Swal.fire({
            title: 'Error!',
            text: '{{session()->get('error')}}',
            icon: 'error',
            confirmButtonText: 'Ok',
        })
    </script>
@endif

@if(session()->has('success'))
    <script type="module">
        Swal.fire({
            title: 'Success!',
            text: '{{session()->get('success')}}',
            icon: 'success',
            confirmButtonText: 'Ok',
        })
    </script>
@endif

@if(session()->has('message'))
    <script type="module">
        Swal.fire({
            title: 'Info !',
            text: '{{session()->get('message')}}',
            icon: 'info',
            confirmButtonText: 'Ok',
        })
    </script>
    @endif
    @stack('scripts')
    </body>
    </html>
