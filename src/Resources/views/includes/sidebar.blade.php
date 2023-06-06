<div class="row">
    <div class="col-md-3">
        <aside class="sidebar">
            <div class="p-3 nav-bar-background">
                <div class="container w-75">
                    <a href="{{route("cubeta-starter.greetings")}}">
                        <div class="row">
                            <div class="col-md-4">
                                <img class="img-fluid" src="{{asset("$assetsPath/images/logo-light.png")}}">
                            </div>
                            <div class="col-md-8">
                                <img class="img-fluid mt-4" src="{{asset("$assetsPath/images/cubeta-light.png")}}">
                            </div>
                        </div>
                    </a>
                </div>
                <hr>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item @if(request()->fullUrl() == route('cubeta-starter.generate-full.page')) my-nav-active @endif">
                        <a href="{{route('cubeta-starter.generate-full.page')}}" class="nav-link text-white"
                           aria-current="page">
                            Full Generation Operation
                        </a>
                    </li>
                    <li class="nav-item @if(request()->fullUrl() == route('cubeta-starter.generate-migration.page')) my-nav-active @endif">
                        <a href="{{route('cubeta-starter.generate-migration.page')}}" class="nav-link text-white">
                            Create Migration
                        </a>
                    </li>
                    <li class="nav-item @if(request()->fullUrl() == route('cubeta-starter.generate-factory.page')) my-nav-active @endif">
                        <a href="{{route('cubeta-starter.generate-factory.page')}}" class="nav-link text-white">
                            Create Factory
                        </a>
                    </li>
                    <li class="nav-item @if(request()->fullUrl() == route('cubeta-starter.generate-seeder.page')) my-nav-active @endif">
                        <a href="{{route('cubeta-starter.generate-seeder.page')}}" class="nav-link text-white">
                            Create Seeder
                        </a>
                    </li>
                    <li class="nav-item @if(request()->fullUrl() == route('cubeta-starter.generate-repository.page')) my-nav-active @endif">
                        <a href="{{route('cubeta-starter.generate-repository.page')}}" class="nav-link text-white">
                            Create Repository
                        </a>
                    </li>
                    <li class="nav-item @if(request()->fullUrl() == route('cubeta-starter.generate-service.page')) my-nav-active @endif">
                        <a href="{{route('cubeta-starter.generate-service.page')}}" class="nav-link text-white">
                            Create Service and its Interface
                        </a>
                    </li>
                    <li class="nav-item @if(request()->fullUrl() == route('cubeta-starter.generate-request.page')) my-nav-active @endif">
                        <a href="{{route('cubeta-starter.generate-request.page')}}" class="nav-link text-white">
                            Create Form Request
                        </a>
                    </li>
                    <li class="nav-item @if(request()->fullUrl() == route('cubeta-starter.generate-resource.page')) my-nav-active @endif">
                        <a href="{{route('cubeta-starter.generate-resource.page')}}" class="nav-link text-white">
                            Create API Resource
                        </a>
                    </li>
                    <li class="nav-item @if(request()->fullUrl() == route('cubeta-starter.generate-api-controller.page')) my-nav-active @endif">
                        <a href="{{route('cubeta-starter.generate-api-controller.page')}}" class="nav-link text-white">
                            Create API Controller
                        </a>
                    </li>
                    <li class="nav-item @if(request()->fullUrl() == route('cubeta-starter.generate-test.page')) my-nav-active @endif">
                        <a href="{{route('cubeta-starter.generate-test.page')}}" class="nav-link text-white">
                            Create Feature Test
                        </a>
                    </li>
                    <li class="nav-item @if(request()->fullUrl() == route('cubeta-starter.generate-policy.page')) my-nav-active @endif">
                        <a href="{{route('cubeta-starter.generate-policy.page')}}" class="nav-link text-white">
                            Create Policy
                        </a>
                    </li>
                    <li class="nav-item @if(request()->fullUrl() == route('cubeta-starter.generate-postman-collection.page')) my-nav-active @endif">
                        <a href="{{route('cubeta-starter.generate-postman-collection.page')}}"
                           class="nav-link text-white">
                            Create Postman Collection
                        </a>
                    </li>
                </ul>
                <hr>
            </div>
        </aside>
    </div>
    <div class="col-md-9">
