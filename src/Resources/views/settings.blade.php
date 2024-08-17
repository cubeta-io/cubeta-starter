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
        $installedApi = \Cubeta\CubetaStarter\App\Models\Settings\Settings::make()->installedApi();
        $installedWeb = \Cubeta\CubetaStarter\App\Models\Settings\Settings::make()->installedWeb();
        $installedRoles = \Cubeta\CubetaStarter\App\Models\Settings\Settings::make()->installedRoles();
        $installedAuth = \Cubeta\CubetaStarter\App\Models\Settings\Settings::make()->installedAuth();
    @endphp

    <div class="d-flex align-items-center justify-content-start flex-column w-100">
        <div class="w-100 d-flex justify-content-center align-items-center my-4" style="max-width: 55%;">
            <div class="card w-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-start">
                        <h2 class="text-white">Settings</h2>
                    </div>
                    <form method="POST" action="{{route('cubeta.starter.settings.set')}}">
                        <div class="row align-items-center text-white w-100">
                            <label class="form-check-label col-md-9 my-2" for="ask_api">
                                Does your project have restful API ?
                                @if($installedApi)
                                    <span style="padding: 1px" class="bg-success rounded-2">Installed</span>
                                @endif
                            </label>
                            <div class="col-md-3 my-2">
                                <input @checked(!$installedApi) class="form-check-input" name="api" type="checkbox"
                                       value="true"
                                       id="ask_api" @disabled($installedApi)>
                            </div>

                            <label class="form-check-label col-md-9 my-2" for="ask_dashboard">
                                Do you need to generate dashboard ?
                                @if($installedWeb)
                                    <span style="padding: 1px" class="bg-success rounded-2">Installed</span>
                                @endif
                            </label>
                            <div class="col-md-3 my-2">
                                <input class="form-check-input" name="web" type="checkbox" value="true"
                                       id="ask_dashboard"
                                        @disabled($installedWeb)
                                        @checked(!$installedWeb)
                                >
                            </div>

                            <label class="form-check-label col-md-9 my-2 hidden" for="ask_stack">
                                Choose preset
                            </label>
                            <div class="col-md-3 my-2 select-container hidden" id="frontend_stack_select">
                                <select class="rounded custom-select" name="frontend_stack" id="ask_stack">
                                    @foreach($stacks as $stack)
                                        @if($stack != \Cubeta\CubetaStarter\Enums\FrontendTypeEnum::NONE->value)
                                            <option
                                                    value="{{$stack}}" @disabled(\Cubeta\CubetaStarter\App\Models\Settings\Settings::make()->getFrontendType() == \Cubeta\CubetaStarter\Enums\FrontendTypeEnum::tryFrom($stack))>
                                                {{$stack}}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <label class="form-check-label col-md-9 my-2" for="ask_auth">
                                Do you have authentication ?
                                @if($installedAuth)
                                    <span style="padding: 1px" class="bg-success rounded-2">Installed</span>
                                @endif
                            </label>
                            <div class="col-md-3 my-2">
                                <input class="form-check-input" type="checkbox" name="auth" value="true" id="ask_auth">
                            </div>

                            <label class="form-check-label col-md-9 my-2" for="ask_permissions">
                                Does your project support multi actors and multi roles ?
                                @if($installedRoles)
                                    <span style="padding: 1px" class="bg-success rounded-2">Installed</span>
                                @endif
                            </label>
                            <div class="col-md-3 my-2">
                                <input class="form-check-input" name="permissions" type="checkbox" value="true"
                                       id="ask_permissions"
                                        @disabled($installedRoles)
                                >
                            </div>

                            <label class="form-check-label col-md-9 my-2" for="ask_permissions">
                                Override ? (this will cause to override any previous file with the same name and
                                directory)
                            </label>
                            <div class="col-md-3 my-2">
                                <input class="form-check-input" checked name="override" type="checkbox" value="true"
                                       id="ask_overrid">
                            </div>
                        </div>
                        @if(\Cubeta\CubetaStarter\App\Models\Settings\Settings::make()->installedRoles())
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


        @if(\Cubeta\CubetaStarter\App\Models\Settings\Settings::make()->installedRoles())
            <div class="w-100 d-flex justify-content-center align-items-center my-4"
                 style="margin-bottom: 200px!important;max-width: 55%;">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-start">
                            <h2 class="text-white">Add Actor</h2>
                        </div>
                        <form method="POST" action="{{route('cubeta.starter.add.actor')}}">
                            <div class="w-100 d-flex align-items-center gap-2">
                                <input placeholder="actor name" name="actor" class="brand-input" id="add_actor"
                                       required>
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
    </div>

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
