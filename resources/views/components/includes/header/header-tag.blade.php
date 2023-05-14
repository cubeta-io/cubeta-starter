@props(['logoPath' => '../../../../../public/images/cubeta-logo.png'])
<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
        <div class="logo d-flex align-items-center">
            <img src="{{$logoPath}}" alt="">
            <span class="d-none d-lg-block">Cubeta Admin Dashboard</span>
        </div>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>
    <!-- End Logo -->

    {{$slot}}
</header>
