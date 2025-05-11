<?php

namespace Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Components\HasInputString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\Sources\WebControllers\InertiaReactTSController;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\FormPageStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Typescript\TsInterfaceStubBuilder;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;

class ReactTSPagesGenerator extends InertiaReactTSController
{
    use WebGeneratorHelper, StringsGenerator;

    private string $imports = "";
    private string $currentForm = "Create";

    public function run(bool $override = false): void
    {
        if (!Settings::make()->getFrontendType() == FrontendTypeEnum::REACT_TS) {
            CubeLog::add(new CubeError("Install react-ts tools by running [php artisan cubeta:install react-ts && php artisan cubeta:install react-ts-packages] then try again", happenedWhen: "Generating a {$this->table->modelName} react pages"));
            return;
        }

        $routes = $this->getRouteNames($this->table, ContainerType::WEB, $this->actor);

        $this->generateTypescriptModel();

        $this->generateUpdateFormPage($routes['update']);
        $this->generateCreateFormPage($routes['store']);

        $this->generateShowPage($override);
        $this->generateIndexPage($override);

        CodeSniffer::make()
            ->setModel($this->table)
            ->checkForTsInterfaces()
            ->checkForReactTSPagesAndControllerRelations($this->actor);
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

        $routes = $this->getRouteNames($this->table, ContainerType::WEB, $this->actor);
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
                $this->addImport("import Gallery from \"@/Components/Show/Gallery\";");
                $bigFields .= "<div className=\"bg-gray-50 my-2 mb-5 p-4 rounded-md font-bold text-xl dark:bg-dark dark:text-white\">
                                    <label className=\"font-semibold text-lg\">{$attr->titleNaming()} :</label>
                                    <Gallery
                                        sources={[{$modelVariable}{$nullable}.{$attr->name}?.url]}
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

        $this->generateFileFromStub($stubProperties, $showPagePath->fullPath, $override, CubePath::stubPath('Inertia/pages/show.stub'));

        $showPagePath->format();
    }

    public function generateIndexPage(bool $override = false): void
    {
        $pageName = $this->table->viewNaming();

        $indexPagePath = CubePath::make("resources/js/Pages/dashboard/$pageName/Index.tsx");

        $this->imports = "";
        $dataTableColumns = $this->getDataTableColumns();
        $routes = $this->getRouteNames($this->table, ContainerType::WEB, $this->actor);
        $stubProperties = [
            "{{imports}}" => $this->imports,
            "{{columns}}" => $dataTableColumns,
            '{{modelName}}' => $this->table->modelName,
            "{{modelVariable}}" => $this->table->variableNaming(),
            "{{createRoute}}" => $routes['create'],
            "{{dataRoute}}" => $routes['data'],
            "{{indexRoute}}" => $routes['index'],
            "{{importRoute}}" => $routes['import'],
            "{{exportRoute}}" => $routes['export'],
            '{{importExampleRoute}}' => $routes['import_example'],
        ];

        if ($indexPagePath->exist()) {
            $indexPagePath->logAlreadyExist("When Generating Index Page For ({$this->table->modelName}) Model");
            return;
        }

        $indexPagePath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $indexPagePath->fullPath, $override, CubePath::stubPath('Inertia/pages/index.stub'));

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

            $this->addImport('import { Link } from "@inertiajs/react";');
            $relatedModelAttribute = $relatedModel->titleable();
            $translatable = $relatedModelAttribute->isTranslatable() ? "translatable:true," : "";
            $relatedModelShowRoute = $this->getRouteNames($this->table, ContainerType::WEB, $this->actor)['show'];
            $columns .= "
            {
                label: \"{$relatedModel->modelName} {$relatedModelAttribute->titleNaming()}\",
                name: \"{$rel->relationMethodNaming()}.{$relatedModelAttribute->name}\",
                sortable: true,
                {$translatable}
                render:({$relatedModelAttribute->name} , {$rel->variableNaming()}) => (
                            <Link
                                className=\"hover:text-primary underline\"
                                href={route(\"$relatedModelShowRoute\" , {$relatedModel->variableNaming()}.id)}>
                                {{$relatedModelAttribute->name}}
                            </Link>)
            },";

            return true;
        });

        return $columns;
    }

    private function generateTypescriptModel(): void
    {
        $interfacePath = $this->table->getTSModelPath();
        $builder = TsInterfaceStubBuilder::make()
            ->modelName($this->table->modelNaming());

        $this->table
            ->attributes()
            ->merge($this->table->relations())
            ->whereInstanceOf(HasInterfacePropertyString::class)
            ->each(function (HasInterfacePropertyString|CubeAttribute|CubeRelation $item) use ($builder) {
                if ($item instanceof CubeRelation) {
                    $model = Settings::make()->getTable($item->modelNaming()) ?? CubeTable::create($item->modelNaming());
                    if (!$model->getTSModelPath()->exist()) {
                        return true;
                    } else {
                        $string = $item->interfacePropertyString();
                    }
                } else {
                    $string = $item->interfacePropertyString();
                }

                $builder->property($string);
                if ($string->import) {
                    $builder->import($string->import);
                }
                return true;
            });

        $builder->generate($interfacePath);
    }

    /**
     * @param string $updateRoute
     * @return void
     */
    private function generateUpdateFormPage(string $updateRoute): void
    {
        $pageName = $this->table->viewNaming();
        $formPath = CubePath::make("resources/js/Pages/dashboard/$pageName/Edit.tsx");
        $builder = FormPageStubBuilder::make()
            ->componentName("Edit")
            ->formTitle("Edit {$this->table->modelNaming()}")
            ->componentProps("{{$this->table->variableNaming()}}:{{$this->table->variableNaming()}:{$this->table->modelNaming()}}")
            ->import(new TsImportString($this->table->modelNaming(), "@/Models/{$this->table->modelNaming()}"))
            ->setPut("setData(\"_method\" , 'PUT');")
            ->action("post(route(\"{$updateRoute}\" , {$this->table->variableNaming()}.id));")
            ->when(
                $this->table->hasTranslatableAttribute(),
                fn($builder) => $builder->translatableContextOpenTag("<TranslatableInputsContext>")
                    ->translatableContextCloseTag("</TranslatableInputsContext>")
                    ->import(new TsImportString("TranslatableInputsContext", "@/Contexts/TranslatableInputsContext"))
            )->formFieldInterface(new InterfacePropertyString("_method", "'PUT'|'POST'", true));

        $this->table->attributes()
            ->each(function (CubeAttribute $attr) use ($builder) {
                if (!$attr->isFile()) {
                    $builder->defaultValue($attr->name, "{$this->table->variableNaming()}?.{$attr->name}");
                }

                if ($attr instanceof HasInputString) {
                    if ($attr->isText() || $attr->isTextable()) {
                        $builder->bigField($attr->inputComponent("update", $this->actor));
                    } else {
                        $builder->smallField($attr->inputComponent("update", $this->actor));
                    }
                }

                if ($attr instanceof HasInterfacePropertyString) {
                    $interfaceProperty = $attr->interfacePropertyString();
                    if ($interfaceProperty->import) {
                        $builder->import($interfaceProperty->import);
                    }
                    $builder->formFieldInterface($interfaceProperty);
                }
            });

        $builder->generate($formPath, $this->override);
    }

    /**
     * @param string $storeRoute
     * @return void
     */
    private function generateCreateFormPage(string $storeRoute): void
    {
        $pageName = $this->table->viewNaming();
        $formPath = CubePath::make("resources/js/Pages/dashboard/$pageName/Create.tsx");
        $builder = FormPageStubBuilder::make()
            ->componentName("Create")
            ->formTitle("Add New {$this->table->modelNaming()}")
            ->action("post(route(\"{$storeRoute}\"));")
            ->when(
                $this->table->hasTranslatableAttribute(),
                fn($builder) => $builder->translatableContextOpenTag("<TranslatableInputsContext>")
                    ->translatableContextCloseTag("</TranslatableInputsContext>")
                    ->import(new TsImportString("TranslatableInputsContext", "@/Contexts/TranslatableInputsContext"))
            )->formFieldInterface(new InterfacePropertyString("_method", "'PUT'|'POST'", true));

        $this->table->attributes()
            ->each(function (CubeAttribute $attr) use ($builder) {
                if ($attr instanceof HasInputString) {
                    if ($attr->isText() || $attr->isTextable()) {
                        $builder->bigField($attr->inputComponent("store", $this->actor));
                    } else {
                        $builder->smallField($attr->inputComponent("store", $this->actor));
                    }
                }

                if ($attr instanceof HasInterfacePropertyString) {
                    $interfaceProperty = $attr->interfacePropertyString();
                    if ($interfaceProperty->import) {
                        $builder->import($interfaceProperty->import);
                    }
                    $builder->formFieldInterface($interfaceProperty);
                }
            });

        $builder->generate($formPath, $this->override);
    }
}
