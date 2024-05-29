<?php

namespace Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\Sources\WebControllers\InertiaReactTSController;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;

class ReactTSPagesGenerator extends InertiaReactTSController
{
    use WebGeneratorHelper, StringsGenerator;

    const MODEL_INTERFACE_STUB = __DIR__ . '/../../../stubs/Inertia/ts/interface.stub';
    const FORM_STUB = __DIR__ . '/../../../stubs/Inertia/pages/form.stub';
    const SHOW_PAGE_STUB = __DIR__ . '/../../../stubs/Inertia/pages/show.stub';
    const INDEX_PAGE_STUB = __DIR__ . '/../../../stubs/Inertia/pages/index.stub';

    private string $imports = "";
    private string $currentForm = "Create";

    public function run(bool $override = false): void
    {
        $routes = $this->getRoutesNames($this->table, $this->actor);

        $this->generateTypeScriptInterface($override);

        $this->generateCreateOrUpdateForm(storeRoute: $routes['store'], override: $override);
        $this->generateCreateOrUpdateForm(updateRoute: $routes['update'], override: $override);
        $this->generateShowPage($override);
        $this->generateIndexPage($override);

        CodeSniffer::make()
            ->setModel($this->table)
            ->checkForTsInterfaces()
            ->checkForReactTSPagesAndControllerRelations($this->actor);
    }

    /**
     * @param string|null $storeRoute
     * @param string|null $updateRoute
     * @param bool        $override
     * @return void
     */
    public function generateCreateOrUpdateForm(?string $storeRoute = null, ?string $updateRoute = null, bool $override = false): void
    {
        $this->imports = '';
        $this->currentForm = $storeRoute ? 'Create' : 'Edit';

        $pageName = $this->table->viewNaming();

        $formPath = CubePath::make("resources/js/Pages/dashboard/$pageName/" . $this->currentForm . '.tsx');

        if ($formPath->exist()) {
            $formPath->logAlreadyExist("When Generating {$this->currentForm} Form For ({$this->table->modelName}) Model");
            return;
        }

        [$formInterface, $translatableContext, $smallFields, $bigFields, $defaultValues] = $this->getFormProperties();

        if ($this->currentForm == "Edit") {
            $this->addImport("import { {$this->table->modelName} } from \"@/Models/{$this->table->modelName}\";");
        }

        if ($this->currentForm == "Edit" && $updateRoute) {
            $action = "post(route(\"{$updateRoute}\" , {$this->table->variableNaming()}.id));";
        } else {
            $action = "post(route(\"{$storeRoute}\"));";
        }

        if ($this->currentForm == "Create") {
            $defaultValues = "";
        }

        $stubProperties = [
            '{{imports}}' => $this->imports,
            '{{formFieldsInterface}}' => $formInterface,
            '{{setPut}}' => $this->currentForm == "Create" ? "" : "setData(\"_method\" , 'PUT');",
            '{{action}}' => $action,
            '{{translatableContext}}' => $translatableContext['open'] ?? "",
            '{{closeTranslatableContext}}' => $translatableContext['close'] ?? "",
            '{{bigFields}}' => $bigFields,
            '{{smallFields}}' => $smallFields,
            "{{formType}}" => $this->currentForm,
            "{{modelName}}" => $this->table->modelName,
            "{{componentName}}" => $this->currentForm,
            "{{componentProps}}" => $this->currentForm == "Edit" ? "{{$this->table->variableNaming()}}:{{$this->table->variableNaming()}:{$this->table->modelName}}" : "",
            "{{defaultValues}}" => $defaultValues,
        ];

        $formPath->ensureDirectoryExists();

        $this->generateFileFromStub(
            $stubProperties,
            $formPath->fullPath,
            $override,
            self::FORM_STUB
        );

        $formPath->format();
    }

    private function getFormProperties(): array
    {
        $formInterface = "";
        $translatableContext = [];

        if ($this->table->translatables()->count()) {
            $translatableContext = [
                'open' => '<TranslatableInputsContext>',
                'close' => '</TranslatableInputsContext>',
            ];
            $this->imports .= "\n import TranslatableInputsContext from \"@/Contexts/TranslatableInputsContext\";\n";
        }

        $smallFields = "";
        $bigFields = "";
        $defaultValues = "{";

        $this->table->attributes()->each(function (CubeAttribute $attribute) use (&$bigFields, &$smallFields, &$formInterface, &$defaultValues) {
            $formInterface .= $this->getAttributeInterfaceProperty($attribute);

            if (!$attribute->isFile()) {
                $defaultValues .= "{$attribute->name} : {$this->table->variableNaming()}.{$attribute->name},\n";
            }

            if ($attribute->isTextable() || $attribute->isText()) {
                $bigFields .= $this->getInputField($attribute);
            } else {
                $smallFields .= $this->getInputField($attribute);
            }
        });

        $formInterface .= "\"_method\"?:\"PUT\"|\"POST\"\n";
        $defaultValues .= " _method: \"PUT\",\n}";

        return [
            $formInterface,
            $translatableContext,
            $smallFields,
            $bigFields,
            $defaultValues,
        ];
    }

    public function getAttributeInterfaceProperty(CubeAttribute $attribute): string
    {
        $nullable = $attribute->nullable ? "?" : "";
        if ($attribute->isString() || $attribute->isDateable()) {
            return "{$attribute->name}{$nullable}:string;\n";
        } elseif ($attribute->isNumeric()) {
            return "{$attribute->name}{$nullable}:number;\n";
        } elseif ($attribute->isBoolean()) {
            return "{$attribute->name}{$nullable}:boolean;\n";
        } elseif ($attribute->isFile()) {
            return "{$attribute->name}{$nullable}:File|string;\n";
        } elseif ($attribute->isKey()) {
            $relatedModelName = Naming::model(str_replace('_id', '', $attribute->name));
            $relatedModelTable = CubeTable::create($relatedModelName);
            if ($relatedModelTable->getModelPath()->exist()) {
                return "{$attribute->name}{$nullable}:number;\n";
            }
            return "";
        } else {
            return "{$attribute->name}{$nullable}:any;\n";
        }
    }

    public function getInputField(CubeAttribute $attribute): string
    {
        if ($attribute->isTranslatable()) {
            if ($attribute->isTextable()) {
                $this->addImport("import TranslatableTextEditor from \"@/Components/form/fields/TranslatableEditor\";");
                return $this->inertiaTranslatableTextEditor($attribute, $this->currentForm == "Edit");
            } else {
                $this->addImport("import TranslatableInput from \"@/Components/form/fields/TranslatableInput\";");
                return $this->inertiaTranslatableInputComponent($attribute, $this->currentForm == "Edit");
            }
        } elseif ($attribute->isBoolean()) {
            $this->addImport("import Radio from \"@/Components/form/fields/Radio\";");
            $labels = $attribute->booleanLabels();
            return $this->inertiaRadioButtonComponent($attribute, $labels, $this->currentForm == "Edit");
        } elseif ($attribute->isKey()) {
            $relatedModel =
                Settings::make()->getTable(Naming::model(str_replace('_id', '', $attribute->name)))
                ?? CubeTable::create(Naming::model(str_replace('_id', '', $attribute->name)));

            $dataRoute = $this->getRouteName($relatedModel, ContainerType::WEB, $this->actor) . '.data';

            if (
                !$relatedModel?->getModelPath()->exist() //Category Path
                || !$relatedModel?->getWebControllerPath()->exist() // Category Controller
                || !ClassUtils::isMethodDefined($relatedModel?->getWebControllerPath(), 'data') // exist
                || !$relatedModel->getTSModelPath()->exist() // Category.ts
            ) {
                return "";
            }

            $this->addImport("import { PaginatedResponse } from \"@/Models/Response\";");
            $this->addImport("import ApiSelect from \"@/Components/form/fields/Select/ApiSelect\";");
            $this->addImport("import { {$relatedModel->modelName} } from \"@/Models/{$relatedModel->modelName}\"");

            return $this->inertiaApiSelectComponent($relatedModel, $dataRoute, $attribute, $this->currentForm == "Edit");
        } elseif ($attribute->isFile()) {
            $this->addImport("import Input from \"@/Components/form/fields/Input\";");
            return $this->inertiaFileInputComponent($attribute, $this->currentForm == "Edit");
        } elseif ($attribute->isText()) {
            $this->addImport("import TextEditor from \"@/Components/form/fields/TextEditor\";");
            return $this->inertiaTextEditorComponent($attribute, $this->currentForm == "Edit");
        } else {
            $this->addImport("import Input from \"@/Components/form/fields/Input\";");
            return $this->inertiaInputComponent($attribute, $this->currentForm == "Edit");
        }
    }

    public function generateTypeScriptInterface(?bool $override = false): void
    {
        $this->imports = "";
        $properties = "id?:number,\n";
        $this->table->attributes()->each(function (CubeAttribute $attr) use (&$properties) {
            $properties .= $this->getAttributeInterfaceProperty($attr);
        });

        $relations = "";

        $this->table->relations()->each(function (CubeRelation $rel) use (&$relations, &$imports) {
            if (!$rel->getTSModelPath()->exist()) {
                return true;
            }

            if ($rel->isHasOne() || $rel->isBelongsTo()) {
                $relations .= "{$rel->variableNaming()}?:{$rel->modelName},\n";
            }

            if ($rel->isHasMany() || $rel->isManyToMany()) {
                $relations .= "{$rel->variableNaming()}?:{$rel->modelName}[],\n";
            }

            $this->addImport("import { {$rel->modelName} } from \"./{$rel->modelName}\";");

            return true;
        });

        $stubProperties = [
            '{{modelName}}' => $this->table->modelName,
            '{{properties}}' => $properties,
            "{{relations}}" => $relations,
            "{{imports}}" => $this->imports,
        ];

        $interfacePath = $this->table->getTSModelPath();

        if ($interfacePath->exist()) {
            $interfacePath->logAlreadyExist("When Generating {$this->table->modelName} Typescript Interface");
            return;
        }

        $interfacePath->ensureDirectoryExists();

        $this->generateFileFromStub(
            stubProperties: $stubProperties,
            path: $interfacePath->fullPath,
            override: $override,
            otherStubsPath: self::MODEL_INTERFACE_STUB
        );

        $interfacePath->format();
    }

    public function addImport($import): void
    {
        $all = FileUtils::extraTrim($this->imports);
        $trimmed = FileUtils::extraTrim($import);

        if (str_contains($all, $trimmed)) {
            return;
        }

        $this->imports .= "\n$import\n";
    }

    public function generateShowPage(bool $override = false): void
    {
        $this->imports = "";

        $modelVariable = $this->table->variableNaming();

        $smallFields = "";
        $bigFields = "";

        $routes = $this->getRoutesNames($this->table, $this->actor);
        $pageName = $this->table->viewNaming();

        $showPagePath = CubePath::make("resources/js/Pages/dashboard/$pageName/Show.tsx");

        if ($showPagePath->exist()) {
            $showPagePath->logAlreadyExist("When Generating Show Page For ({$this->table->modelName}) Model");
            return;
        }

        $this->table->attributes()->each(function (CubeAttribute $attr) use (&$smallFields, &$bigFields, $modelVariable) {
            $nullable = $attr->nullable ? "?" : "";
            if ($attr->isText() || $attr->isTextable()) {
                $this->addImport("import LongTextField from \"@/Components/Show/LongTextField\";");
                if ($attr->isTranslatable()) {
                    $this->addImport("import { translate } from \"@/Models/Translatable\";");
                    $bigFields .= "\n<LongTextField label={\"{$attr->titleNaming()}\"} value={translate({$modelVariable}{$nullable}.{$attr->name})} />\n";
                } else {
                    $bigFields .= "\n<LongTextField label={\"{$attr->titleNaming()}\"} value={{$modelVariable}{$nullable}.{$attr->name}} />\n";
                }
            } elseif ($attr->isFile()) {
                $this->addImport("import { asset } from \"@/helper\";");
                $this->addImport("import Gallery from \"@/Components/Show/Gallery\";");
                $bigFields .= "<div className=\"bg-gray-50 my-2 mb-5 p-4 rounded-md font-bold text-xl\">
                                    <label className=\"font-semibold text-lg\">{$attr->titleNaming()} :</label>
                                    <Gallery
                                        sources={
                                            {$modelVariable}{$nullable}.image
                                                ? [asset((\"storage/\" + {$modelVariable}{$nullable}.{$attr->name}) as string)]
                                                : []
                                        }
                                    />
                                </div>";
            } elseif ($attr->isTranslatable()) {
                $this->addImport("import { translate } from \"@/Models/Translatable\";");
                $this->addImport("import SmallTextField from \"@/Components/Show/SmallTextField\";");
                $smallFields .= "\n<SmallTextField label=\"{$attr->titleNaming()} \" value={translate({$modelVariable}{$nullable}.{$attr->name})} />\n";
            } elseif ($attr->isBoolean()) {
                $this->addImport("import SmallTextField from \"@/Components/Show/SmallTextField\";");
                $smallFields .= "<SmallTextField label=\"{$attr->titleNaming()} ? \" value={{$modelVariable}{$nullable}.{$attr->name} ? 'Yes' : 'No'} />";
            } else {
                $this->addImport("import SmallTextField from \"@/Components/Show/SmallTextField\";");
                $smallFields .= "<SmallTextField label=\"{$attr->titleNaming()} \" value={{$modelVariable}{$nullable}.{$attr->name}} />";
            }
        });

        $stubProperties = [
            '{{modelName}}' => $this->table->modelName,
            "{{imports}}" => $this->imports,
            "{{variableName}}" => $modelVariable,
            "{{editRoute}}" => $routes['edit'],
            "{{smallFields}}" => $smallFields,
            "{{bigFields}}" => $bigFields,
        ];

        $showPagePath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $showPagePath->fullPath, $override, self::SHOW_PAGE_STUB);

        $showPagePath->format();
    }

    public function generateIndexPage(bool $override = false): void
    {
        $pageName = $this->table->viewNaming();

        $indexPagePath = CubePath::make("resources/js/Pages/dashboard/$pageName/Index.tsx");

        $this->imports = "";
        $routes = $this->getRoutesNames($this->table, $this->actor);
        $stubProperties = [
            '{{modelName}}' => $this->table->modelName,
            "{{imports}}" => $this->imports,
            "{{createRoute}}" => $routes['create'],
            "{{dataRoute}}" => $routes['data'],
            "{{columns}}" => $this->getDataTableColumns(),
            "{{modelVariable}}" => $this->table->variableNaming(),
            "{{indexRoute}}" => $routes['index'],
        ];

        if ($indexPagePath->exist()) {
            $indexPagePath->logAlreadyExist("When Generating Index Page For ({$this->table->modelName}) Model");
            return;
        }

        $indexPagePath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $indexPagePath->fullPath, $override, self::INDEX_PAGE_STUB);

        $indexPagePath->format();
    }

    public function getDataTableColumns(): string
    {
        $columns = "";
        $this->table->attributes()->each(function (CubeAttribute $attr) use (&$columns) {
            if ($attr->isText() || $attr->isTextable() || $attr->isFile()) {
                return true;
            }
            if ($attr->isTranslatable()) {
                $columns .= "{
                    label: \"{$attr->titleNaming()}\",
                    name: \"{$attr->name}\",
                    translatable: true,
                    sortable: true,
                },";
            } elseif ($attr->isBoolean()) {
                $columns .= "{
                    label: \"{$attr->titleNaming()} ?\",
                    name: \"{$attr->name}\",
                    sortable: true,
                    render: ({$attr->name}) =>
                    {$attr->name} ? <span>Yes</span> : <span>No</span>,
                },";
            } elseif ($attr->isKey()) {
                return true;
            } else {
                $columns .= "{
                    label: \"{$attr->titleNaming()}\",
                    name: \"{$attr->name}\",
                    sortable: true,
                },";
            }

            return true;
        });

        $this->table->relations()->each(function (CubeRelation $rel) use (&$columns) {
            if (!$rel->isBelongsTo()) {
                return true;
            }

            $relatedModel = $rel->getTable() ?? Settings::make()->serialize($rel->modelName, []);

            if (!$rel->getModelPath()->exist()
                || !ClassUtils::isMethodDefined($rel->getModelPath(), $this->table->relationMethodNaming(singular: false))) {
                return true;
            }

            if (!$this->table->getModelPath()->exist()
                || !ClassUtils::isMethodDefined($this->table->getModelPath(), $rel->relationMethodNaming())) {
                return true;
            }

            $columns .= "
            {
                label: \"{$relatedModel->modelName} {$relatedModel->titleable()->titleNaming()}\",
                name: \"{$rel->relationMethodNaming()}.{$relatedModel->titleable()->name}\",
                sortable: true,
            },";

            return true;
        });

        return $columns;
    }
}
