@extends('CubetaStarter::layout')

@section('content')
    @include('CubetaStarter::includes.sidebar')

    <main class="main">
        <section class="section profile">
            <div class="container">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="card-header text-center">
                            <h1>{{$title}}</h1>
                            <p>{{$textUnderTitle}}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <form class="form" method="POST" action="{{$action}}">
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
                                        <div class="col-md-3 m-2">
                                            <label>none</label>
                                            <input class="form-check-input" type="radio" value="none"
                                                   name="actor" checked>
                                        </div>
                                        @foreach($roles as $role)
                                            <div class="col-md-3 m-2">
                                                <label>{{$role}}</label>
                                                <input class="form-check-input" type="radio"
                                                       value="{{$role}}"
                                                       name="actor">
                                            </div>
                                        @endforeach
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

    @push('scripts')
        @if($attributesField)
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
        @endif
    @endpush

@endsection
