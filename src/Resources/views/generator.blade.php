@extends('CubetaStarter::layout')
@section('content')
    @php
        $roleEnumPath = \Cubeta\CubetaStarter\Helpers\CubePath::make("app/Enums/RolesPermissionEnum.php");
        if ($roleEnumPath->exist() and class_exists("\\App\\Enums\\RolesPermissionEnum")) {
            $actors = ['none', ...\App\Enums\RolesPermissionEnum::ALLROLES];
        }else{
            $actors = [];
        }
        $columnTypes = \Cubeta\CubetaStarter\Enums\ColumnTypeEnum::getAllValues();
        $noRelationsNeeded = \Cubeta\CubetaStarter\Generators\GeneratorFactory::notNeedForRelations();
        $noColumnsNeeded = \Cubeta\CubetaStarter\Generators\GeneratorFactory::noNeedForColumns();
    @endphp
    <div class="d-flex justify-content-center align-items-center" style="overflow-y: scroll">
        <div class="card" style="margin-bottom: 100px; margin-top: 100px">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-start">
                    <h2 class="text-white">Generate</h2>
                </div>
                <form id="generate_form" method="POST" action="{{route('cubeta.starter.generate')}}">
                    <div class="my-3 d-flex align-items-center gap-2">
                        <input required placeholder="Model name" class="brand-input p-1" name="model_name" type="text"/>

                        <label class="fw-semibold text-white">Generate for it</label>

                        <select required name="generate_key" id="generate_key_select" class="rounded custom-select">
                            <option value="full_crud">Full CRUD</option>
                            @foreach($generatingType as $type)
                                <option value="{{$type}}">{{ucfirst($type)}}</option>
                            @endforeach
                        </select>

                        <label class="fw-semibold text-white">for</label>

                        <select required name="container" class="rounded custom-select">
                            @if($installedApi)
                                <option value="{{\Cubeta\CubetaStarter\Enums\ContainerType::API}}">API</option>
                            @endif

                            @if($installedWeb)
                                <option value="{{\Cubeta\CubetaStarter\Enums\ContainerType::WEB}}">Web</option>
                            @endif

                            @if($installedApi && $installedWeb)
                                <option value="{{\Cubeta\CubetaStarter\Enums\ContainerType::BOTH}}">Both</option>
                            @endif
                        </select>

                        @if($hasRoles)
                            <label class="fw-semibold text-white">Act with</label>
                            <select required name="actor" class="rounded custom-select">
                                @foreach($actors as $actor)
                                    <option value="{{$actor}}">{{$actor}}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="flex-column gap-2 my-3" id="columns_fields_container">
                        <div class="d-flex gap-5 align-items-center">
                            <h3 class="text-white fw-light">Columns</h3>
                            <button id="add_column_button" type="button"
                                    style="background-color: var(--brand-secondary)" class="btn btn-sm">
                                @include('CubetaStarter::icons.plus')
                            </button>
                        </div>

                        <div class="d-flex gap-3 align-items-center" id="columns_inputs_container_0">
                            <input required placeholder="name" class="brand-input p-1" name="columns[0][name]"
                                   type="text"/>
                            <select required name="columns[0][type]" class="rounded custom-select p-1">
                                @foreach($columnTypes as $column)
                                    <option value="{{$type}}">{{$column}}</option>
                                @endforeach
                            </select>

                            <div class="form-check my-3">
                                <input class="form-check-input" type="checkbox" name="columns[0][nullable]" value="true"
                                       id="nullable_0">
                                <label class="form-check-label text-white fw-semibold" for="nullable_0">
                                    is nullable
                                </label>
                            </div>

                            <div class="form-check my-3">
                                <input class="form-check-input" type="checkbox" name="columns[0][unique]" value="true"
                                       id="unique_0">
                                <label class="form-check-label text-white" for="unique_0">
                                    is unique
                                </label>
                            </div>

                            <button onclick="removeField(event)"
                                    type="button" data-column-id='0'
                                    id="remove_column_0"
                                    style="background-color: var(--brand-primary)"
                                    class="btn btn-sm remove_column_button">
                                @include('CubetaStarter::icons.minus')
                            </button>
                        </div>
                    </div>

                    <div class="flex-column gap-2 my-3" id="relations_fields_container">
                        <div class="d-flex gap-5 align-items-center">
                            <h3 class="text-white fw-light">Relations</h3>
                            <button id="add_relation_button" type="button"
                                    style="background-color: var(--brand-secondary)" class="btn btn-sm">
                                @include('CubetaStarter::icons.plus')
                            </button>
                        </div>

                        <div class="my-3 d-flex gap-3 align-items-center" id="relations_inputs_container_0">
                            <input placeholder="name" class="brand-input p-1"
                                   name="relations[0][name]"
                                   id="relation_name_0"
                                   type="text"
                                   required
                            />
                            <select required name="relations[0][type]" id="relation_type_select_0"
                                    class="rounded custom-select p-1">
                                <option value="{{\Cubeta\CubetaStarter\Enums\RelationsTypeEnum::HasMany->value}}">Has
                                    many
                                </option>
                                <option value="{{\Cubeta\CubetaStarter\Enums\RelationsTypeEnum::ManyToMany->value}}">
                                    Many to many
                                </option>
                            </select>

                            <button onclick="removeRelationField(event)" id="remove_relation_button_0"
                                    data-relation-id="0" type="button"
                                    style="background-color: var(--brand-primary)" class="btn btn-sm">
                                @include('CubetaStarter::icons.minus')
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button id="generate_button" type="submit" class="submit-button">
                            Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('custom-scripts')
        <script>
            function removeField(event) {
                document.getElementById(`columns_inputs_container_${event.currentTarget.dataset.columnId}`).remove();
            }

            function removeRelationField(event) {
                document.getElementById(`relations_inputs_container_${event.currentTarget.dataset.relationId}`).remove();
            }
        </script>
        <script type="module">
            const columnsContainer = $("#columns_fields_container");
            const addColumnButton = $("#add_column_button");

            function getModelNameField(id) {
                return `<input required placeholder="name" class="brand-input p-1" name="columns[${id}][name]" type="text"/>`;
            }

            function getColumnTypeField(id) {
                return `<select required name="columns[${id}][type]" class="rounded custom-select p-1">
                            @foreach($columnTypes as $column)
                <option value="{{$type}}">{{$column}}</option>
                            @endforeach
                </select>`;
            }

            function getIsNullInput(id) {
                return `<div class="form-check my-3">
                            <input class="form-check-input" type="checkbox" name="columns[${id}][nullable]" value="true"
                                   id="nullable_${id}">
                            <label class="form-check-label text-white fw-semibold" for="nullable_${id}">
                                is nullable
                            </label>
                        </div>`;
            }

            function getIsUniqueInput(id) {
                return `<div class="form-check my-3">
                            <input class="form-check-input" type="checkbox" name="columns[${id}][unique]" value="true"
                                   id="unique_${id}">
                            <label class="form-check-label text-white" for="unique_${id}">
                                is unique
                            </label>
                        </div>`
            }

            function getMinusButton(id) {
                return `<button id="remove_column_${id}" onclick="removeField(event)" type="button" style="background-color: var(--brand-primary)" class="btn btn-sm" data-column-id='${id}'>
                            @include('CubetaStarter::icons.minus')
                </button>`
            }

            function getFullColumnContainer(id) {
                return `<div class="d-flex gap-3 align-items-center" id="columns_inputs_container_${id}">
                            ${getModelNameField(id) + getColumnTypeField(id) + getIsNullInput(id) + getIsUniqueInput(id) + getMinusButton(id)}
                        </div>`
            }

            let columnsCount = 0;
            addColumnButton.on('click', function (event) {
                event.preventDefault();
                columnsCount++;
                columnsContainer.append(getFullColumnContainer(columnsCount));
            })

            const addRelationButton = $("#add_relation_button");
            const relationsContainer = $("#relations_fields_container");

            function getRelationNameInput(id) {
                return `<input required placeholder="name" class="brand-input p-1"
                               name="relations[${id}][name]"
                               id="relation_name_${id}"
                               type="text"
                        />`
            }

            function getRelationTypeSelect(id) {
                return `<select required name="relations[${id}][type]" id="relation_type_select_${id}"
                                class="rounded custom-select p-1">
                            <option value="{{\Cubeta\CubetaStarter\Enums\RelationsTypeEnum::HasMany->value}}">Has
                                many
                            </option>
                            <option value="{{\Cubeta\CubetaStarter\Enums\RelationsTypeEnum::ManyToMany->value}}">
                                Many to many
                            </option>
                        </select>`
            }

            function getRemoveRelationButton(id) {
                return `<button onclick="removeRelationField(event)" id="remove_relation_button_${id}" data-relation-id="${id}" type="button" style="background-color: var(--brand-primary)" class="btn btn-sm">@include('CubetaStarter::icons.minus')</button>`
            }

            function getRelationFieldsContainer(id) {
                return `<div class="my-3 d-flex gap-3 align-items-center" id="relations_inputs_container_${id}">
                            ${getRelationNameInput(id) + getRelationTypeSelect(id) + getRemoveRelationButton(id)}
                        </div>`
            }

            let relationsCount = 0;
            addRelationButton.on('click', function (event) {
                event.preventDefault();
                relationsCount++;
                relationsContainer.append(getRelationFieldsContainer(relationsCount));
            })
        </script>

        <script type="module">
            $(document).ready(function () {
                const rels = $("#relations_fields_container");
                const cols = $("#columns_fields_container");
                const form = $("#generate_form");
                $("#generate_key_select").on('change', function (event) {
                    const selected = $(event.target).val();
                    const dontNeedRelations = "{{implode(" , ",$noRelationsNeeded)}}";
                    const dontNeedColumns = "{{implode(" , " , $noColumnsNeeded)}}";
                    if (dontNeedRelations.includes(selected)) {
                        rels.css('display', 'none');
                    } else {
                        rels.css('display', 'flex');
                    }

                    if (dontNeedColumns.includes(selected)) {
                        cols.css('display', 'none');
                    } else {
                        cols.css('display', 'flex');
                    }
                })
            })
        </script>
    @endpush

@endsection
