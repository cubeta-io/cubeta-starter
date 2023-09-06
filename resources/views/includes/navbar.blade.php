<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="#" class="logo d-flex align-items-center">
            <img src="{{asset('images/cubeta-logo.png')}}" alt="">
            <span class="d-none d-lg-block">Cubeta Starter</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->
    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
            <li class="nav-item dropdown pe-3">

                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <h6 class="d-none d-md-block dropdown-toggle ps-2">App Admin</h6>
                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>Kevin Anderson</h6>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <i class="bi bi-person"></i>
                            <span>Edit Profile</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sign Out</span>
                        </a>
                    </li>

                </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->
            <!-- language dropdown -->
            <li class="nav-item dropdown p-3">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                        {{app()->getLocale()}}
                    </button>
                    <ul class="dropdown-menu">
                        @foreach(config('cubeta-starter.available_locales') as $lang)
                            <li><a class="dropdown-item">{{$lang}}</a></li>
                        @endforeach
                    </ul>
                </div>
            </li>
            <script type="module">
                $(document).ready(function () {
                    $('.dropdown-menu a').click(function (e) {
                        e.preventDefault();
                        const lang = $(this).text();
                        $.ajax({
                            url: '{{route('set-locale')}}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                lang: lang
                            },
                            success: function (response) {
                                location.reload();
                            }
                        });
                    });
                });
            </script><!-- end language dropdown -->
        </ul>
    </nav><!-- End Icons Navigation -->
</header>
