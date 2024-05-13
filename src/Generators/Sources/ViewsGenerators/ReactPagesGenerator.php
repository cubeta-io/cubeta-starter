<?php

namespace Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\Sources\WebControllers\InertiaReactTSController;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;

class ReactPagesGenerator extends InertiaReactTSController
{
    use WebGeneratorHelper, StringsGenerator;

    const MODEL_INTERFACE_STUB = __DIR__ . '/../../../stubs/Inertia/ts/interface.stub';
    const FORM_STUB = __DIR__ . '/../../../stubs/Inertia/pages/form.stub';

    private string $imports = "";

    public function run(bool $override = false): void
    {
        $routes = $this->getRoutesNames($this->table, $this->actor);

        $this->generateTypeScriptInterface($override);
        $this->generateCreateOrUpdateForm(storeRoute: $routes['create'], override: $override);
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
        $currentForm = $storeRoute ? "post" : "put";
        $createdForm = $storeRoute ? 'Create' : 'Edit';

        $pageName = $this->table->viewNaming();

        [$formInterface, $translatableContext, $smallFields] = $this->getFieldsProperties();

        $stubProperties = [
            '{{imports}}' => $this->imports,
            '{{formFieldsInterface}}' => $formInterface,
            '{{setPut}}' => $currentForm == "store" ? "" : "setData(\"_method\" , 'PUT');",
            '{{action}}' => "post(route(\"{$this->getRoutesNames($this->table , $this->actor)['store']}\"));",
            '{{translatableContext}}' => $translatableContext['open'] ?? "",
            '{{closeTranslatableContext}}' => $translatableContext['close'] ?? "",
            '{{bigFields}}' => "",
            '{{smallFields}}' => $smallFields
        ];

        $formPath = CubePath::make("resources/js/Pages/dashboard/$pageName/" . $createdForm . '.tsx');

        if ($formPath->exist()) {
            $formPath->logAlreadyExist("When Generating $createdForm Form For ({$this->table->modelName}) Model");
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

    private function getFieldsProperties(): array
    {
        $formInterface = "";
        $translatableContext = [];

        if ($this->table->translatables()->count()) {
            $translatableContext = [
                'open' => '<TranslatableInputsContext>',
                'close' => '</TranslatableInputsContext>'
            ];
            $this->imports .= "\n import TranslatableInputsContext from \"@/Contexts/TranslatableInputsContext\";\n";
        }

        $smallFields = "";

        $this->table->attributes()->each(function (CubeAttribute $attribute) use (&$smallFields, &$formInterface) {
            $formInterface .= $this->getAttributeInterfaceProperty($attribute);
            $smallFields .= $this->getInputField($attribute);
        });

        $formInterface .= "\"_method\"?:\"PUT\"|\"POST\"\n";

        return [
            $formInterface,
            $translatableContext,
            $smallFields,
        ];
    }

    public function getAttributeInterfaceProperty(CubeAttribute $attribute): string
    {
        if ($attribute->isString() || $attribute->isDateTime()) {
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
                $this->imports .= "import TranslatableTextEditor from \"@/Components/form/fields/TranslatableEditor\";\n";
                return $this->inertiaTranslatableTextEditor($attribute);
            } else {
                $this->imports .= "\nimport TranslatableInput from \"@/Components/form/fields/TranslatableInput\";\n";
                return $this->inertiaTranslatableInputComponent($attribute);
            }
        } elseif ($attribute->isBoolean()) {
            $this->imports .= "\nimport Radio from \"@/Components/form/fields/Radio\";\n";
            $labels = $attribute->booleanLabels();
            return $this->inertiaRadioButtonComponent($attribute, $labels);
        } elseif ($attribute->isKey()) {
            $relatedModel = Settings::make()->getTable(Naming::model(str_replace('_id', '', $attribute->name)));
            $select2Route = $this->getRouteName($relatedModel, ContainerType::WEB, $this->actor) . '.allPaginatedJson';

            if (!$relatedModel?->getModelPath()->exist() || !$relatedModel?->getWebControllerPath()->exist()) {
                return "";
            }
            if (!ClassUtils::isMethodDefined($relatedModel?->getWebControllerPath(), 'allPaginatedJson')) {
                return "";
            }

            $this->imports .= "\nimport { PaginatedResponse } from \"@/Models/Response\";\n";
            $this->imports .= "import { {$relatedModel->modelName} } from \"@/Models/{$relatedModel->modelName}";
            $this->imports .= "\nimport ApiSelect from \"@/Components/form/fields/Select/ApiSelect\";\n";

            return $this->inertiaApiSelectComponent($relatedModel, $select2Route, $attribute);
        } elseif ($attribute->isFile()) {
            return $this->inertiaFileInputComponent($attribute);
        } elseif ($attribute->isText()) {
            $this->imports .= "import TextEditor from \"@/Components/form/fields/TextEditor\";\n";
            return $this->inertiaTextEditroComponent($attribute);
        } else {
            $this->imports .= "import Input from \"@/Components/form/fields/Input\";";
            return $this->inertiaInputComponent($attribute);
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
            '{{properties}}' => $properties
        ];

        $interfacePath = CubePath::make("resources/js/Models/{$this->table->modelName}.ts");

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
}
