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
                                            <label for="model-name-field" class="form-label">Model Name</label>
                                            <input id="model-name-field" class="form-control" type="text"
                                                   name="model_name"
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
                                                    <label>
                                                        none
                                                        <input class="form-check-input" type="radio" value="none"
                                                               name="actor" checked>
                                                    </label>
                                                </div>
                                                @foreach($roles as $role)
                                                    <div class="col-md-3 m-1">
                                                        <label>
                                                            {{$role}}
                                                            <input class="form-check-input" type="radio"
                                                                   value="{{$role}}"
                                                                   name="actor">
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endif


                                @if($containerField)
                                    <div class="col-md-12 p-3">
                                        <p class="description-font">What is the container type of your generated CURDs
                                            ? </p>
                                        <div class="row">

                                            <div class="col-md-3 m-1">
                                                <label>
                                                    API
                                                    <input class="form-check-input" type="radio"
                                                           value="api"
                                                           name="containerType" checked>
                                                </label>
                                            </div>

                                            <div class="col-md-3 m-1">
                                                <label>
                                                    Web
                                                    <input class="form-check-input" type="radio"
                                                           value="web"
                                                           name="containerType">
                                                </label>
                                            </div>

                                            <div class="col-md-3 m-1">
                                                <label>
                                                    Both
                                                    <input class="form-check-input" type="radio" value="both"
                                                           name="containerType">
                                                </label>
                                            </div>

                                        </div>
                                    </div>
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

                                    <div class="row">
                                        <div class="col-md-4">
                                            <p class="description-font">
                                                this will generate the required classes for you to handle users
                                                authentication in api container.
                                                the generated are files :
                                            </p>

                                            <ul>
                                                <li> Migration: 2014_10_12_000000_create_users_table.php</li>
                                                <li> Model: User</li>
                                                <li> Repository: UserRepository</li>
                                                <li> Service: UserService</li>
                                                <li> Resource: UserResource</li>
                                                <li> Requests: AuthRequests</li>
                                                <li>Controller: BaseAuthController</li>
                                                <li>View: reset-password-email</li>
                                            </ul>
                                            <a class="btn btn-primary"
                                               href="{{route('cubeta-starter.init-auth' , 'api')}}">init api auth</a>
                                        </div>

                                        <div class="col-md-4">
                                            <p class="description-font">
                                                this will generate the required classes for you to handle users
                                                authentication in web container.
                                                the generated are files :
                                            </p>
                                            <ul>
                                                <li>Migration: 2014_10_12_000000_create_users_table.php</li>
                                                <li>Model: User</li>
                                                <li>Repository: UserRepository</li>
                                                <li>Service: UserWebService</li>
                                                <li>Requests: AuthRequests</li>
                                                <li>Controller: BaseAuthWebController</li>
                                                <li>View: reset-password-email and some other views</li>
                                            </ul>
                                            <a class="btn btn-primary"
                                               href="{{route('cubeta-starter.init-auth' , 'web')}}">init web
                                                auth</a>
                                        </div>

                                        <div class="col-md-4">
                                            <p class="description-font">
                                                this will generate the required classes for you to handle users
                                                authentication in web and api containers.
                                                this will generate the files like the initialization in web and api
                                                containers combined
                                            </p>
                                            <br>
                                            <br>
                                            <br>
                                            <br>
                                            <br>
                                            <br>
                                            <br>
                                            <br>
                                            <a class="btn btn-primary mt-3"
                                               href="{{route('cubeta-starter.init-auth')}}">init auth</a>
                                        </div>
                                    </div>

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

    @include('CubetaStarter::includes.handle-messages-scripts')

    @if($addActor)
        @push('scripts')
            <script type="module">
                $(document).ready(function () {
                    let roleIndex = 1;
                    $("#addRole").on('click', function (event) {
                        event.preventDefault();
                        const $rolesContainer = $("#rolesContainer");
                        const $newRoleInputRow = $("<div></div>").addClass("row mt-2 mb-2");
                        const $newRoleInput = $("<div></div>").addClass("col-md-3").attr("id", "roleInput");
                        const $newRoleInputField = $("<input>").attr({
                            "id": "roleName",
                            "name": `roles[${roleIndex}][name]`,
                            "class": "form-control",
                            "type": "text",
                            "placeholder": "Enter role name e.g: admin"
                        });
                        const $newPermissionInput = $("<div></div>").addClass("col-md-5 d-flex align-items-center").attr("id", "permissionInput");
                        const $newPermissionInputField = $("<input>").attr({
                            "id": "permissionName",
                            "name": `roles[${roleIndex}][permissions]`,
                            "class": "form-control",
                            "type": "text",
                            "placeholder": "Enter role permissions e.g: can-edit,can-read, etc."
                        });
                        const $deleteButton = $("<button></button>").addClass("btn btn-sm btn-danger col-md-1 ml-1").attr({
                            "type": "button",
                            "html": "&times;",
                        });
                        $deleteButton.text("X");
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

                        // container type selection
                        const $containerTypeSelectDiv = $('<div class="col-md-1"></div>')
                        const $containerTypeSelect = $('<select></select>')
                        $containerTypeSelect.attr({
                            "name": `roles[${roleIndex}][container]`,
                            "class": "form-control",
                        });
                        $containerTypeSelectDiv.append($containerTypeSelect);

                        const $apiOption = $('<option value="api" selected>API</option>');
                        $containerTypeSelect.append($apiOption);
                        const $webOption = $('<option value="web">WEB</option>');
                        $containerTypeSelect.append($webOption);
                        const $constBothOption = $("<option value='both'>Both</option>");
                        $containerTypeSelect.append($constBothOption);
                        // end of container type selection

                        const authenticated = $("<div></div>").addClass("col-md-2 m-auto");
                        const authenticatedCheckbox = $("<input>").attr({
                            "type": "checkbox",
                            "id": "authenticated-" + roleIndex,
                            "name": "authenticated[" + roleIndex + "]",
                            "class": "form-check-input",
                            "value": "true"
                        });
                        const authenticatedLabel = $("<label></label>").attr("for", "authenticated-" + roleIndex).addClass('').html(" authenticated?");
                        authenticated.append(authenticatedCheckbox);
                        authenticated.append(authenticatedLabel);
                        $newRoleInputField.on("input", function () {
                            authenticatedCheckbox.val($newRoleInputField.val());
                        });

                        $newRoleInput.append($newRoleInputField);
                        $newPermissionInput.append($newPermissionInputField);
                        $newRoleInputRow.append($newRoleInput);
                        $newRoleInputRow.append($newPermissionInput);
                        $newRoleInputRow.append($containerTypeSelectDiv);
                        $newRoleInputRow.append(authenticated);
                        $newRoleInputRow.append($deleteButton);
                        $rolesContainer.find('#addRole').before($newRoleInputRow);
                        roleIndex++;
                    });
                });
            </script>
        @endpush
    @endif
    @push('scripts')
        <script type="module">
            $(document).ready(function () {
                const addColumnButton = document.getElementById("add-column-button");
                const addRelationButton = document.getElementById("add-relation-button");
                const columnsContainer = document.getElementById("columns-container");
                const relationsContainer = document.getElementById("relations-container");
                let inputIndex = 1;
                let relationIndex = 1;

                @if($attributesField)
                addColumnButton.addEventListener("click", function (e) {
                    e.preventDefault();

                    const newColumn = document.createElement("div");
                    newColumn.className = "row";

                    const newColumnName = document.createElement("div");
                    newColumnName.className = "col-md-4 mt-1";
                    const columnNameInput = document.createElement("input");
                    columnNameInput.className = "form-control column-name";
                    columnNameInput.type = "text";
                    columnNameInput.name = "columns[" + inputIndex + "][name]";
                    columnNameInput.placeholder = "Enter Your Column Name e.g:name,type,price";
                    columnNameInput.required = true;
                    newColumnName.appendChild(columnNameInput);

                    @if($nullables)
                    const nullableCheckbox = document.createElement("div");
                    nullableCheckbox.className = "col-md-2 m-auto";
                    const nullableCheckboxInput = document.createElement("input");
                    nullableCheckboxInput.type = "checkbox";
                    nullableCheckboxInput.id = "nullables-" + inputIndex;
                    nullableCheckboxInput.name = "nullables[" + inputIndex + "]";
                    nullableCheckboxInput.className = "form-check-input";
                    nullableCheckboxInput.value = "true";
                    const nullableLabel = document.createElement("label");
                    nullableLabel.setAttribute("for", "nullables-" + inputIndex);
                    nullableLabel.innerHTML = "nullable";
                    nullableLabel.className = '';
                    nullableLabel.style.display = "inherit";
                    nullableCheckbox.appendChild(nullableCheckboxInput);
                    nullableCheckbox.appendChild(nullableLabel);
                    columnNameInput.addEventListener("input", function () {
                        nullableCheckboxInput.value = columnNameInput.value;
                    });
                    @endif

                    @if($uniques)
                    const uniqueCheckbox = document.createElement("div");
                    uniqueCheckbox.className = "col-md-2 m-auto";
                    const uniqueCheckboxInput = document.createElement("input");
                    uniqueCheckboxInput.type = "checkbox";
                    uniqueCheckboxInput.id = "uniques-" + inputIndex;
                    uniqueCheckboxInput.name = "uniques[" + inputIndex + "]";
                    uniqueCheckboxInput.className = "form-check-input";
                    const uniqueLabel = document.createElement("label");
                    uniqueLabel.setAttribute("for", "uniques-" + inputIndex);
                    uniqueLabel.innerHTML = "unique";
                    uniqueLabel.className = '';
                    uniqueLabel.style.display = "inherit";
                    uniqueCheckbox.appendChild(uniqueCheckboxInput);
                    uniqueCheckbox.appendChild(uniqueLabel);
                    columnNameInput.addEventListener("input", function () {
                        uniqueCheckboxInput.value = columnNameInput.value;
                    });
                    @endif

                    const newColumnType = document.createElement("div");
                    newColumnType.className = "col-md-3 mt-1";
                    const columnTypeSelect = document.createElement("select");
                    columnTypeSelect.className = "form-select column-type";
                    columnTypeSelect.setAttribute("aria-label", "Default select example");
                    columnTypeSelect.name = "columns[" + inputIndex + "][type]";

                    columnTypeSelect.addEventListener('input', function () {
                        if (columnTypeSelect.value === 'key') {
                            uniqueCheckboxInput.disabled = true;
                            uniqueCheckboxInput.value = null;
                            uniqueCheckboxInput.checked = false;
                        } else if (columnTypeSelect.value === 'boolean') {
                            uniqueCheckboxInput.disabled = true;
                            uniqueCheckboxInput.value = null;
                            uniqueCheckboxInput.checked = false;
                        } else {
                            uniqueCheckboxInput.disabled = false;
                            uniqueCheckboxInput.value = columnNameInput.value;
                            uniqueCheckboxInput.checked = false;
                        }

                        if (columnTypeSelect.value === 'file') {
                            nullableCheckboxInput.disabled = true;
                            nullableCheckboxInput.value = null;
                            nullableCheckboxInput.checked = true;
                        } else {
                            nullableCheckboxInput.disabled = false;
                            nullableCheckboxInput.value = columnNameInput.value;
                            nullableCheckboxInput.checked = false;
                        }
                    });

                    @foreach($types as $type)
                    const option{{$loop->iteration}} = document.createElement("option");
                    option{{$loop->iteration}}.value = "{{$type}}";
                    option{{$loop->iteration}}.text = "{{$type}}";
                    columnTypeSelect.appendChild(option{{$loop->iteration}});
                    @endforeach
                    newColumnType.appendChild(columnTypeSelect);

                    const deleteColumnButtonDiv = document.createElement("div");
                    deleteColumnButtonDiv.className = "col-md-1 d-flex justify-content-end";
                    const deleteColumnButton = document.createElement("button");
                    deleteColumnButtonDiv.appendChild(deleteColumnButton);
                    deleteColumnButton.className = "btn btn-sm btn-danger";
                    deleteColumnButton.type = "button";
                    deleteColumnButton.innerHTML = "&times;";
                    deleteColumnButton.style.cssText = `
                            width: 30px;
                            height: 30px;
                            padding: 0;
                            font-weight: bolder;
                            border-radius: 4px;
                            font-size: 16px;
                            line-height: 1;
                            transition: all 0.2s ease;
                            background-color: #dc3545;
                            color: #fff;
                            border: none;
                        `;
                    deleteColumnButton.addEventListener("click", function () {
                        newColumn.remove();
                    });

                    newColumnName.appendChild(columnNameInput);
                    newColumn.appendChild(newColumnName);
                    newColumn.appendChild(newColumnType);

                    @if($nullables)
                    newColumn.appendChild(nullableCheckbox);
                    @endif

                    @if($uniques)
                    newColumn.appendChild(uniqueCheckbox);
                    @endif

                    newColumn.appendChild(deleteColumnButtonDiv);

                    columnsContainer.appendChild(newColumn);

                    inputIndex++;
                });
                @endif

                @if($relationsField)
                addRelationButton.addEventListener("click", function (e) {
                    e.preventDefault();

                    const newRelation = document.createElement("div");
                    newRelation.className = "row";

                    const relationNameInput = document.createElement("div");
                    relationNameInput.className = "col-md-4 mt-1";
                    const relationInput = document.createElement("input");
                    relationInput.className = "form-control relation-name";
                    relationInput.type = "text";
                    relationInput.name = "relations[" + relationIndex + "][name]";
                    relationInput.placeholder = "Enter Your Related Models";
                    relationInput.required = true;
                    relationNameInput.appendChild(relationInput);

                    const relationTypeInput = document.createElement("div");
                    relationTypeInput.className = "col-md-3 mt-1";
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

                    const deleteRelationButtonDiv = document.createElement("div");
                    deleteRelationButtonDiv.className = "col-md-3 d-flex justify-content-end align-items-center";
                    const deleteRelationButton = document.createElement("button");
                    deleteRelationButtonDiv.appendChild(deleteRelationButton);
                    deleteRelationButton.className = "btn btn-sm btn-danger";
                    deleteRelationButton.type = "button";
                    deleteRelationButton.innerHTML = "&times;";
                    deleteRelationButton.style.cssText = `
                            width: 30px;
                            height: 30px;
                            padding: 0;
                            font-weight: bolder;
                            border-radius: 4px;
                            font-size: 16px;
                            line-height: 1;
                            transition: all 0.2s ease;
                            background-color: #dc3545;
                            color: #fff;
                            border: none;
                        `;
                    deleteRelationButton.addEventListener("click", function () {
                        newRelation.remove();
                    });

                    newRelation.appendChild(relationNameInput);
                    newRelation.appendChild(relationTypeInput);
                    newRelation.appendChild(deleteRelationButtonDiv);

                    relationsContainer.appendChild(newRelation);
                    relationIndex++;
                });
                @endif
            });
        </script>
    @endpush
@endsection
