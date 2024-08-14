<footer>
    <script src="{{asset("$assetsPath/js/bootstrap.min.js")}}"></script>
    <script src="{{asset("$assetsPath/js/jquery-3.7.1.min.js")}}"></script>
    @stack('scripts')
</footer>


<script type="module">
    $(document).ready(function () {
        const myModal = document.getElementById('spinner');
        const modal = new bootstrap.Modal(myModal, {
            keyboard: false,
            backdrop: "static"
        });
        modal.hide();

        $('.submit-button').click(function () {
            document.getElementById('modal-title').innerText = "Please wait while we generate your files ..."
            modal.show();
        })

        $(document).on('keydown', function (event) {
            if (event.key === 'Escape') {
                modal.hide();
            }
        });
    });
</script>
