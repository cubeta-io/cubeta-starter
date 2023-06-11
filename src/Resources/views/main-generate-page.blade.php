@extends('CubetaStarter::layout')
@section('content')
    @include('CubetaStarter::includes.sidebar')

    <main class="main">
        <section class="section profile">
            <div class="container">
                <div class="modal mt-5" tabindex="-1" role="dialog" id="spinner" data-keyboard="false"
                     data-backdrop="static">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content bg-white">
                            <div class="modal-body">
                                <h3 id="modal-title" class="text-center">{{$modalBody ?? null}}</h3>
                                <div class="card d-flex justify-content-center align-items-center bg-white"
                                     style="border: none">
                                    <div class="card-body w-25 h-25 text-center" style="border-radius: 15px">
                                        <div class="lds-dual-ring"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header text-center">
                        <div class="card-header text-center">
                            <h1>{{$title}}</h1>
                            <p>{{$textUnderTitle}}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="generator-form" class="form" method="POST" action="{{$action}}">
                            @csrf
                            <div class="row">
                                @if($modelNameField)
                                    <div class="col-md-12 m-2">
                                        <div class="form-group">
                                            <input class="form-control" type="text" name="model_name"
                                                   placeholder="Enter Your Model Name e.g:Product"
                                                   required>
                                        </div>
                                    </div>
                                @endif

                                @if($actorsField)
                                    @if(isset($roles) && count($roles) > 0)
                                        <div class="col-md-12 p-3">
                                            <p class="description-font">Here Your Project Defined Roles, Select One to
                                                be
                                                the actor for the created model endpoints : </p>
                                            <div class="row">
                                                <div class="col-md-3 m-1">
                                                    <label>none</label>
                                                    <input class="form-check-input" type="radio" value="none"
                                                           name="actor" checked>
                                                </div>
                                                @foreach($roles as $role)
                                                    <div class="col-md-3 m-1">
                                                        <label>{{$role}}</label>
                                                        <input class="form-check-input" type="radio"
                                                               value="{{$role}}"
                                                               name="actor">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <div id="columns-container" class="row">
                                <!--columns inputs-->
                            </div>

                            @if($attributesField)
                                <button id="add-column-button" class="btn btn-primary col-md-2 m-3">
                                    Add Column
                                </button>
                            @endif

                            <div id="relations-container" class="row">
                                <!--relation inputs-->
                            </div>

                            @if($relationsField)
                                <button id="add-relation-button" class="btn btn-primary col-md-2 m-3">
                                    Add Relation
                                </button>
                            @endif

                            @if($addActor)
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <p class="description-font">If You Have Multi Actors System
                                                and You're
                                                Planing To Use Our Multi Actors Configuration You Need
                                                to Have
                                                Spatie/Permission Package. Do you want to install it?
                                            </p>
                                            <a id="install-spatie"
                                               href="{{route('cubeta-starter.call-install-spatie')}}"
                                               class="btn btn-primary">
                                                install spatie
                                            </a>
                                        </div>
                                    </div>
                                    @if($roles && count($roles) >0)
                                        <div class="col-md-12 p-3">
                                            <label>Your Project Roles</label>
                                            <div class="row">
                                                @foreach($roles as $role)
                                                    <div class="col-md-3">
                                                        <p class="border border-dark text-center m-2"
                                                           style="color: #001e4a">{{$role}}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-12 p-3">
                                        <p class="description-font">
                                            Here add your actors and their permissions if they have
                                            multiple
                                            permissions.
                                            Input is like: can-do,can-read,can-publish, etc.
                                        </p>
                                        <div class="row" id="rolesContainer">

                                            {{--roles permissions input--}}

                                            <div class="col-md-12 mt-2">
                                                <button class="btn btn-primary" id="addRole">Add New
                                                    Role
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="card-footer">
                                <div class="d-flex align-items-center justify-content-center">
                                    <ul>
                                        {!! $notes !!}
                                    </ul>
                                    <button class="text-center btn btn-primary mx-auto"
                                            type="submit">
                                        Generate
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @if(request('error'))
        @push('scripts')
            <script type="module">
                Swal.fire({
                    title: "Error",
                    text: "{{ request('error') }}",
                    icon: "error",
                    button: "OK",
                }).then(() => {
                    window.location.href = "{{url()->previous()}}";
                });
            </script>
        @endpush
    @endif

    @if(request('success'))
        @push('scripts')
            <script type="module">
                Swal.fire({
                    title: "Success",
                    text: "{{ request('error') }}",
                    icon: "success",
                    button: "OK",
                }).then(() => {
                    window.location.href = "{{url()->previous()}}";
                });
            </script>
        @endpush
    @endif

    @push('scripts')
        <script type="module">
            $(document).ready(function () {
                const myModal = document.getElementById('spinner');
                const modal = new bootstrap.Modal(myModal, {
                    keyboard: false,
                    backdrop: "static"
                });
                modal.hide();
                $('#generator-form').submit(function () {
                    modal.show();
                })

                $('#install-spatie').click(function (e) {
                    document.getElementById('modal-title').innerText = "Installing Spatie/Permissions"
                    modal.show();
                })

                $(document).on('keydown', function (event) {
                    if (event.key === 'Escape') {
                        modal.hide();
                    }
                });
            });
        </script>
    @endpush

    @if($addActor)
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
                        const $newPermissionInput = $("<div></div>", {
                            "class": "col-md-5 d-flex align-items-center",
                            "id": "permissionInput"
                        });
                        const $newPermissionInputField = $("<input>", {
                            "id": "permissionName",
                            "name": `roles[${roleIndex}][permissions]`,
                            "class": "form-control",
                            "type": "text",
                            "placeholder": "Enter role permissions e.g: can-edit,can-read, etc."
                        });
                        const $deleteButton = $("<button></button>", {
                            "class": "btn btn-sm btn-danger col-md-1 ml-1",
                            "type": "button",
                            "html": "&times;"
                        });
                        $deleteButton.css({
                            "width": "30px",
                            "height": "25px",
                            "margin": "auto",
                            "padding": "initial",
                            "fontWeight": "bolder",
                            "borderRadius": "60%"
                        });
                        $deleteButton.on('click', function () {
                            $newRoleInputRow.remove();
                        });
                        $newRoleInput.append($newRoleInputField);
                        $newPermissionInput.append($newPermissionInputField);
                        $newRoleInputRow.append($newRoleInput);
                        $newRoleInputRow.append($newPermissionInput);
                        $newRoleInputRow.append($deleteButton);
                        $rolesContainer.find('#addRole').before($newRoleInputRow);
                        roleIndex++;
                    });
                });
            </script>
        @endpush
    @endif
    @if($attributesField)
        @push('scripts')
            <script type="module">
                $(document).ready(function () {
                    const addColumnButton = document.getElementById("add-column-button");
                    const addRelationButton = document.getElementById("add-relation-button");
                    const columnsContainer = document.getElementById("columns-container");
                    const relationsContainer = document.getElementById("relations-container");
                    let inputIndex = 1;
                    let relationIndex = 1;

                    addColumnButton.addEventListener("click", function (e) {
                        e.preventDefault();

                        const newColumn = document.createElement("div");
                        newColumn.className = "row m-2";

                        const newColumnName = document.createElement("div");
                        newColumnName.className = "col-md-4 m-1";
                        const columnNameInput = document.createElement("input");
                        columnNameInput.className = "form-control column-name";
                        columnNameInput.type = "text";
                        columnNameInput.name = "columns[" + inputIndex + "][name]";
                        columnNameInput.placeholder = "Enter Your Column Name e.g:price";
                        columnNameInput.required = true;
                        newColumnName.appendChild(columnNameInput);

                        const newColumnType = document.createElement("div");
                        newColumnType.className = "col-md-6 m-1";
                        const columnTypeSelect = document.createElement("select");
                        columnTypeSelect.className = "form-select column-type";
                        columnTypeSelect.setAttribute("aria-label", "Default select example");
                        columnTypeSelect.name = "columns[" + inputIndex + "][type]";

                        @foreach($types as $type)
                        const option{{$loop->iteration}} = document.createElement("option");
                        option{{$loop->iteration}}.value = "{{$type}}";
                        option{{$loop->iteration}}.text = "{{$type}}";
                        columnTypeSelect.appendChild(option{{$loop->iteration}});
                        @endforeach
                        newColumnType.appendChild(columnTypeSelect);

                        const deleteColumnButton = document.createElement("button");
                        deleteColumnButton.className = "btn btn-sm btn-danger col-md-1 text-center";
                        deleteColumnButton.type = "button";
                        deleteColumnButton.innerHTML = "&times;";
                        deleteColumnButton.style.width = "30px";
                        deleteColumnButton.style.height = "25px";
                        deleteColumnButton.style.margin = "auto";
                        deleteColumnButton.style.padding = "initial";
                        deleteColumnButton.style.fontWeight = "bolder";
                        deleteColumnButton.style.borderRadius = "60%";
                        deleteColumnButton.addEventListener("click", function () {
                            newColumn.remove();
                        });

                        newColumn.appendChild(newColumnName);
                        newColumn.appendChild(newColumnType);
                        newColumn.appendChild(deleteColumnButton);

                        columnsContainer.appendChild(newColumn);
                        inputIndex++;
                    });

                    @if($relationsField)
                    addRelationButton.addEventListener("click", function (e) {
                        e.preventDefault();

                        const newRelation = document.createElement("div");
                        newRelation.className = "row";

                        const relationNameInput = document.createElement("div");
                        relationNameInput.className = "col-md-4 m-1";
                        const relationInput = document.createElement("input");
                        relationInput.className = "form-control relation-name";
                        relationInput.type = "text";
                        relationInput.name = "relations[" + relationIndex + "][name]";
                        relationInput.placeholder = "Enter Your Related Models";
                        relationInput.required = true;
                        relationNameInput.appendChild(relationInput);

                        const relationTypeInput = document.createElement("div");
                        relationTypeInput.className = "col-md-6 m-1";
                        const relationTypeSelect = document.createElement("select");
                        relationTypeSelect.className = "form-select column-type";
                        relationTypeSelect.setAttribute("aria-label", "Default select example");
                        relationTypeSelect.name = "relations[" + relationIndex + "][type]";

                        const option1 = document.createElement("option");
                        option1.value = "hasMany";
                        option1.text = "Has Many";
                        relationTypeSelect.appendChild(option1);
                        const option2 = document.createElement("option");
                        option2.value = "manyToMany";
                        option2.text = "Many To Many";
                        relationTypeSelect.appendChild(option2);
                        relationTypeInput.appendChild(relationTypeSelect);

                        const deleteRelationButton = document.createElement("button");
                        deleteRelationButton.className = "btn btn-sm btn-danger col-md-1 text-center";
                        deleteRelationButton.type = "button";
                        deleteRelationButton.innerHTML = "&times;";
                        deleteRelationButton.style.width = "30px";
                        deleteRelationButton.style.height = "25px";
                        deleteRelationButton.style.margin = "auto";
                        deleteRelationButton.style.padding = "initial";
                        deleteRelationButton.style.fontWeight = "bolder";
                        deleteRelationButton.style.borderRadius = "60%";
                        deleteRelationButton.addEventListener("click", function () {
                            newRelation.remove();
                        });

                        newRelation.appendChild(relationNameInput);
                        newRelation.appendChild(relationTypeInput);
                        newRelation.appendChild(deleteRelationButton);

                        relationsContainer.appendChild(newRelation);
                        relationIndex++;
                    });
                    @endif
                });
            </script>
        @endpush
    @endif

@endsection
