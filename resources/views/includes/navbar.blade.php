<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="#" class="logo d-flex align-items-center">
            <img src="{{asset('images/cubeta-logo.png')}}" alt="">
            <span class="d-none d-lg-block">Cubeta Starter</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>
    <!-- End Logo -->

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">

            <!--Profile Nav-->
            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <img src="{{asset('images/profile-img.jpg')}}" alt="Profile" class="rounded-circle">
                    <span class="d-none d-md-block dropdown-toggle ps-2">{{auth()->user()?->name ?? "App Admin"}}</span>
                </a>
                <!-- End Profile Iamge Icon -->
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>{{auth()->user()?->name ?? "App Admin"}}</h6>
                        <span>Admin</span>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <i class="bi bi-gear"></i>
                            <span>Account Settings</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <i class="bi bi-question-circle"></i>
                            <span>Need Help?</span>
                        </a>
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
            </li>
            <!-- End Profile Nav -->

            <!-- language dropdown -->
            <li class="nav-item dropdown p-3">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                        {{ strtoupper(app()->getLocale()) }}
                    </button>
                    <ul class="dropdown-menu" id="lang-changer">
                        @foreach (config('cubeta-starter.available_locales') as $lang)
                            <li style="cursor: pointer" data-lang="{{ $lang }}"
                                data-route="{{ route('set-locale') }}">
                                <a class="dropdown-item">{{ strtoupper($lang) }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </li>
            <!-- end of language dropdown -->

        </ul>
    </nav>
</header>
