<nav class="d-flex flex-row py-3">
    <div class="d-flex align-items-center justify-content-start h-100 w-50 gap-2">
        <img class="h-100 img-fluid" src="{{asset("$assetsPath/images/logo-light.png")}}" alt="cubeta logo">
        <h3 class="fw-bolder text-white p-0 m-0">CUBETA</h3>
    </div>
    <div class="d-flex align-items-center justify-content-end gap-5 w-50 text-white">
        <a href="{{route('cubeta.starter.generate.page')}}"
           class="fw-semibold @if(request()->fullUrl() == route('cubeta.starter.generate.page')) active-item @endif">
            Generate
        </a>
        <a href="{{route('cubeta.starter.settings')}}"
           class="fw-semibold @if(request()->fullUrl() == route('cubeta.starter.settings')) active-item @endif">
            Setting
        </a>
    </div>
</nav>
