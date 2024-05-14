<?php

namespace Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\Sources\WebControllers\InertiaReactTSController;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;

class ReactPagesGenerator extends InertiaReactTSController
{
    use WebGeneratorHelper, StringsGenerator;

    const MODEL_INTERFACE_STUB = __DIR__ . '/../../../stubs/Inertia/ts/interface.stub';
    const FORM_STUB = __DIR__ . '/../../../stubs/Inertia/pages/form.stub';

    private string $imports = "";
    private string $currentForm = "Create";

    public function run(bool $override = false): void
    {
        $routes = $this->getRoutesNames($this->table, $this->actor);

        $this->generateTypeScriptInterface($override);
        $this->generateCreateOrUpdateForm(storeRoute: $routes['store'], override: $override);
        $this->generateCreateOrUpdateForm(updateRoute: $routes['update'], override: $override);
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

        [$formInterface, $translatableContext, $smallFields, $bigFields, $defaultValues] = $this->getFormProperties();

        if ($this->currentForm == "Edit") {
            $this->addImport("import { {$this->table->modelName} } from \"@/Models/{$this->table->modelName}\";");
        }

        $routes = $this->getRoutesNames($this->table, $this->actor);

        if ($this->currentForm == "Edit") {
            $action = "post(route(\"{$routes['update']}\" , {$this->table->variableNaming()}.id));";
        } else {
            $action = "post(route(\"{$routes['store']}\"));";
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

        $formPath = CubePath::make("resources/js/Pages/dashboard/$pageName/" . $this->currentForm . '.tsx');

        if ($formPath->exist()) {
            $formPath->logAlreadyExist("When Generating {$this->currentForm} Form For ({$this->table->modelName}) Model");
            return;
        }

        $formPath->ensureDirectoryExists();

        $this->generateFileFromStub(
            $stubProperties,
            $formPath->fullPath,
            $override,
            self::FORM_STUB
        );
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
                $defaultValues .= "{$attribute->name} : {$this->table->variableNaming()}.{$attribute->name},";
            }

            if ($attribute->isTextable() || $attribute->isText()) {
                $bigFields .= $this->getInputField($attribute);
            } else {
                $smallFields .= $this->getInputField($attribute);
            }
        });

        $formInterface .= "\"_method\"?:\"PUT\"|\"POST\"\n";
        $defaultValues .= "}";

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
        if ($attribute->isString() || $attribute->isDateable()) {
            return "$attribute->name?:string;\n";
        } elseif ($attribute->isNumeric() || $attribute->isKey()) {
            return "{$attribute->name}?:number;\n";
        } elseif ($attribute->isBoolean()) {
            return "{$attribute->name}?:boolean;\n";
        } elseif ($attribute->isFile()) {
            return "{$attribute->name}?:File;\n";
        } else {
            return "{$attribute->name}?:any;\n";
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
            $relatedModel = Settings::make()->getTable(Naming::model(str_replace('_id', '', $attribute->name)));
            $select2Route = $this->getRouteName($relatedModel, ContainerType::WEB, $this->actor) . '.allPaginatedJson';

            if (
                !$relatedModel?->getModelPath()->exist()
                || !$relatedModel?->getWebControllerPath()->exist()
                || !ClassUtils::isMethodDefined($relatedModel?->getWebControllerPath(), 'allPaginatedJson')
                || !$relatedModel->getTSModelPath()->exist()
            ) {
                return "";
            }

            $this->addImport("import { PaginatedResponse } from \"@/Models/Response\";");
            $this->addImport("import ApiSelect from \"@/Components/form/fields/Select/ApiSelect\";");
            $this->addImport("import { {$relatedModel->modelName} } from \"@/Models/{$relatedModel->modelName}");

            return $this->inertiaApiSelectComponent($relatedModel, $select2Route, $attribute, $this->currentForm == "Edit");
        } elseif ($attribute->isFile()) {
            $this->addImport("import Input from \"@/Components/form/fields/Input\";");
            return $this->inertiaFileInputComponent($attribute);
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
        $properties = "";
        $this->table->attributes()->each(function (CubeAttribute $attr) use (&$properties) {
            $properties .= $this->getAttributeInterfaceProperty($attr);
        });

        $stubProperties = [
            '{{modelName}}' => $this->table->modelName,
            '{{properties}}' => $properties,
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
}
