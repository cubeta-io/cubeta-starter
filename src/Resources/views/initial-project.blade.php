@extends('CubetaStarter::layout')

@section('content')
    <main class="main">
        <section class="section profile">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header text-center">
                            <h1>Initial Your Project</h1>
                            <p>to have a full amazing experience with cubeta-starter</p>
                        </div>
                        <div class="card-body">
                            <form class="form" method="POST" action="{{ route('call-initial-project') }}">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <p class="description-font">We have an exception handler for you, and
                                                it will replace "app/Exceptions/handler.php" file with
                                                a file of the same name. Do you want to use it?</p>
                                            <br>
                                            <label for="useExceptionHandler" class="form-check-label">Yes</label>
                                            <input class="form-check-input" id="useExceptionHandler" type="radio"
                                                   name="useExceptionHandler" value="true">
                                            <label for="dontUseExceptionHandler" class="form-check-label">No</label>
                                            <input id="dontUseExceptionHandler" class="form-check-input"
                                                   name="useExceptionHandler"
                                                   value="false" type="radio" checked>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <p class="description-font">If You Have Multi Actors System and You're
                                                Planing To Use Our Multi Actors Configuration You Need to Have
                                                Spatie/Permission Package. Do you want to install it? (if your choice
                                                was
                                                to install it, and you didn't provide roles the package will not be
                                                installed)</p>
                                            <label for="installSpatie" class="form-check-label">Yes</label>
                                            <input class="form-check-input" id="installSpatie" type="radio"
                                                   name="installSpatie" value="true">
                                            <label for="dontInstallSpatie" class="form-check-label">No</label>
                                            <input id="dontInstallSpatie" class="form-check-input"
                                                   name="installSpatie"
                                                   value="false" type="radio" checked>
                                        </div>
                                    </div>
                                    <div class="col-md-12 p-3">
                                        <p class="description-font">
                                            Here add your actors and their permissions if they have multiple
                                            permissions.
                                            Input is like: can-do,can-read,can-publish, etc.
                                        </p>
                                        <div class="row" id="rolesContainer">
                                            <div class="col-md-6" id="roleInput">
                                                <input id="roleName" name="roles[0][name]"
                                                       value="{{ old('roles[0][name]') }}"
                                                       class="form-control" type="text"
                                                       placeholder="Enter role name e.g: admin">
                                            </div>
                                            <div class="col-md-6" id="permissionInput">
                                                <input id="permissionName" name="roles[0][permissions]"
                                                       value="{{ old('roles[0][permissions]') }}"
                                                       class="form-control" type="text"
                                                       placeholder="Enter role permissions e.g: can-edit,can-read, etc.">
                                            </div>
                                            <div class="col-md-12 mt-2">
                                                <button class="btn btn-primary" id="addRole">Add New Role</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button class="text-center btn btn-primary" type="submit">Initialize</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @push('scripts')
        <script type="module">
            $(document).ready(function () {
                let roleIndex = 1;

                $("#addRole").on('click', function (event) {
                    event.preventDefault();
                    const $rolesContainer = $("#rolesContainer");
                    const $newRoleInputRow = $("<div></div>", {"class": "row m-2"});
                    const $newRoleInput = $("<div></div>", {"class": "col-md-6", "id": "roleInput"});
                    const $newRoleInputField = $("<input>", {
                        "id": "roleName",
                        "name": `roles[${roleIndex}][name]`,
                        "class": "form-control",
                        "type": "text",
                        "placeholder": "Enter role name e.g: admin"
                    });
                    const $newPermissionInput = $("<div></div>", {"class": "col-md-6", "id": "permissionInput"});
                    const $newPermissionInputField = $("<input>", {
                        "id": "permissionName",
                        "name": `roles[${roleIndex}][permissions]`,
                        "class": "form-control",
                        "type": "text",
                        "placeholder": "Enter role permissions e.g: can-edit,can-read, etc."
                    });
                    $newRoleInput.append($newRoleInputField);
                    $newPermissionInput.append($newPermissionInputField);
                    $newRoleInputRow.append($newRoleInput);
                    $newRoleInputRow.append($newPermissionInput);
                    $rolesContainer.find('#addRole').before($newRoleInputRow);
                    roleIndex++;
                });
            });
        </script>
    @endpush
@endsection
