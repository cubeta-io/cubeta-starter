@extends('CubetaStarter::layout')
@section('content')

    @php
        $stacks = array_reverse(\Cubeta\CubetaStarter\Enums\FrontendTypeEnum::getAllValues());
        $roleEnumPath = \Cubeta\CubetaStarter\Helpers\CubePath::make("app/Enums/RolesPermissionEnum.php");
        if ($roleEnumPath->exist() and class_exists("\\App\\Enums\\RolesPermissionEnum")) {
            $actors = ['none', ...\App\Enums\RolesPermissionEnum::ALLROLES];
        }else{
            $actors = [];
        }
    @endphp

    <nav class="d-flex flex-row py-3">
        <div class="d-flex align-items-center justify-content-start h-100 w-50 gap-1">
            <img class="h-100" src="{{asset("$assetsPath/images/logo-light.png")}}" alt="">
            <h1 class="fw-bolder text-white">CUBETA</h1>
        </div>
        <div class="d-flex align-items-center justify-content-end gap-5 w-50 text-white">
            <a href="#" class="fw-semibold">
                Generate
            </a>
            <a href="#"
               class="fw-semibold @if(request()->fullUrl() == route('cubeta.starter.settings')) active-item @endif">
                Setting
            </a>
        </div>
    </nav>
    <div class="d-flex justify-content-center align-items-center">
        <div class="card mt-5">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-start">
                    <h2 class="text-white">Settings</h2>
                </div>
                <form method="POST" action="{{route('cubeta.starter.settings.set')}}">
                    <div class="row align-items-center text-white w-100">
                        <label class="form-check-label col-md-9 my-2" for="ask_api">
                            Does your project have restful API ?
                        </label>
                        <div class="col-md-3 my-2">
                            <input class="form-check-input" name="api" type="checkbox" value="true" id="ask_api">
                        </div>

                        <label class="form-check-label col-md-9 my-2" for="ask_dashboard">
                            Do you need to generate dashboard ?
                        </label>
                        <div class="col-md-3 my-2">
                            <input class="form-check-input" name="web" type="checkbox" value="true" id="ask_dashboard"
                                   checked>
                        </div>

                        <label class="form-check-label col-md-9 my-2 hidden" for="ask_stack">
                            Choose preset
                        </label>
                        <div class="col-md-3 my-2 select-container hidden" id="frontend_stack_select">
                            <select class="rounded custom-select" name="frontend_stack" id="ask_stack">
                                @foreach($stacks as $stack)
                                    <option value="{{$stack}}">
                                        {{$stack}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <label class="form-check-label col-md-9 my-2" for="ask_auth">
                            Do you have authentication ?
                        </label>
                        <div class="col-md-3 my-2">
                            <input class="form-check-input" type="checkbox" name="auth" value="true" id="ask_auth">
                        </div>

                        <label class="form-check-label col-md-9 my-2" for="ask_permissions">
                            Does your project support multi actors and multi roles ?
                        </label>
                        <div class="col-md-3 my-2">
                            <input class="form-check-input" name="permissions" type="checkbox" value="true"
                                   id="ask_permissions">
                        </div>
                    </div>
                    @if(\Cubeta\CubetaStarter\App\Models\Settings\Settings::make()->hasRoles())
                        <div class="d-flex flex-column align-items-start justify-content-start my-3">
                            <h2 class="text-white">Actors : </h2>
                            <p class="text-white">{{implode(' , ',array_filter($actors , fn ($actor) => $actor != 'none'))}}</p>
                        </div>
                    @endif
                    <div class="d-flex justify-content-end">
                        <button class="submit-button">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    @if(\Cubeta\CubetaStarter\App\Models\Settings\Settings::make()->hasRoles())
        <div class="d-flex justify-content-center align-items-center my-5" style="margin-bottom: 200px!important;">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-start">
                        <h2 class="text-white">Add Actor</h2>
                    </div>
                    <form method="POST" action="{{route('cubeta.starter.add.actor')}}">
                        <div class="w-100 d-flex align-items-center gap-2">
                            <input placeholder="actor name" name="actor" class="brand-input" id="add_actor" required>
                            <span class="text-white">For</span>
                            <div>
                                <select name="container" class="rounded px-4" id="ask_container"
                                        style="padding: 2px 1.5rem;" required
                                >
                                    <option value="both">Both</option>
                                    <option value="api">API</option>
                                    <option value="web">WEB</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-check my-3">
                            <input class="form-check-input" type="checkbox" name="authenticated" value="true"
                                   id="authenticated">
                            <label class="form-check-label text-white" for="authenticated">
                                Has authentication endpoints ?
                            </label>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="submit-button">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @push('custom-scripts')
        <script type="module">
            $(document).ready(function () {
                const askDashboard = $("#ask_dashboard");
                const stackSelect = $("#frontend_stack_select")
                const stackSelectLabel = $(`label[for="ask_stack"]`);
                if (askDashboard.is(':checked')) {
                    stackSelect.removeClass('hidden');
                    stackSelectLabel.removeClass('hidden');
                } else {
                    stackSelect.addClass('hidden');
                    stackSelectLabel.addClass('hidden');
                }
                askDashboard.on('input', function () {
                    if (askDashboard.is(':checked')) {
                        stackSelect.removeClass('hidden');
                        stackSelectLabel.removeClass('hidden');
                    } else {
                        stackSelect.addClass('hidden');
                        stackSelectLabel.addClass('hidden');
                    }
                })
            });
        </script>
    @endpush
@endsection
