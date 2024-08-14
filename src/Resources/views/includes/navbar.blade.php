<nav class="d-flex flex-row py-3">
    <div class="d-flex align-items-center justify-content-start h-100 w-50 gap-1">
        <img class="h-100" src="{{asset("$assetsPath/images/logo-light.png")}}" alt="">
        <h1 class="fw-bolder text-white">CUBETA</h1>
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
