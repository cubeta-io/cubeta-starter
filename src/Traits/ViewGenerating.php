<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\CreateFile;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

trait ViewGenerating
{
    use NamingTrait;

    /**
     * @param string $modelName
     * @param array $attributes
     * @param string $storeRoute
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateCreateForm(string $modelName, array $attributes, string $storeRoute): void
    {
        $lowerPluralModelName = $this->lowerPluralName($modelName);
        $inputs = $this->generateInputs($attributes);
        $stubProperties = [
            "{modelName}" => $modelName,
            "{storeRoute}" => $storeRoute,
            "{components}" => $inputs
        ];

        $formDirectory = base_path("resources/views/dashboard/$lowerPluralModelName/create.blade.php");

        if (!is_dir(base_path("resources/views/dashboard/$lowerPluralModelName/"))) {
            mkdir(base_path("resources/views/dashboard/$lowerPluralModelName/"), 0777, true);
        }

        if (file_exists($formDirectory)) {
            echo(" \n Create Form Already Created \n");
            return;
        }

        new CreateFile(
            $stubProperties,
            $formDirectory,
            __DIR__ . "/../Commands/stubs/views/form.stub"
        );

        echo(" \n A create form for $lowerPluralModelName created \n");
    }

    /**
     * @param array $attributes
     * @return string
     */
    public function generateInputs(array $attributes): string
    {
        $inputs = '';
        foreach ($attributes as $attribute => $type) {
            $label = $this->getLabelName($attribute);

            if ($attribute == 'email') {
                $inputs .= "\n <x-input label=\"$label\" type=\"email\"></x-input> \n";
                continue;
            }

            if ($attribute == 'password') {
                $inputs .= "\n <x-input label=\"$label\" type=\"password\"></x-input> \n";
                continue;
            }

            if (in_array($attribute, ['phone', 'phone_number', 'home_number', 'work_number', 'tele', 'telephone'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"tel\"></x-input> \n";
                continue;
            }

            if (Str::contains($attribute, ['_url', 'url_', 'URL_', '_URL'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"url\"></x-input> \n";
                continue;
            }

            if (in_array($type, ['integer', 'bigInteger', 'unsignedBigInteger', 'unsignedDouble', 'double', 'float'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"number\"></x-input> \n";
            }

            if (in_array($type, ['string', 'json'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"text\"></x-input> \n";
            }

            if ($type == 'text') {
                $inputs .= "\n <x-text-editor label=\"$label\"></x-text-editor> \n";
            }

            if ($type == 'date') {
                $inputs .= "\n <x-input label=\"$label\" type=\"date\"></x-input> \n";
            }

            if ($type == 'time') {
                $inputs .= "\n <x-input label=\"$label\" type=\"time\"></x-input> \n";
            }

            if (in_array($type, ['dateTime', 'timestamp'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"datetime-local\"></x-input> \n";
            }

            if ($type == 'file') {
                $inputs .= "\n <x-input label=\"$label\" type=\"file\"></x-input> \n";
            }

            if ($type == 'boolean') {
                $inputs .= "\n <x-form-check>
                                    <x-form-check-radio name=\"$attribute\" value=\"is $attribute\" checked></x-form-check-radio>
                                    <x-form-check-radio name=\"$attribute\" value=\"not $attribute\"></x-form-check-radio>
                               </x-form-check> \n";
            }
        }

        return $inputs;
    }

    /**
     * @param string $attribute
     * @return array|string
     */
    public function getLabelName(string $attribute): array|string
    {
        return str_replace('_', ' ', Str::title(Str::singular($attribute)));
    }

    /**
     * @param string $modelName
     * @param array $attributes
     * @param string $editRoute
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateShowView(string $modelName, array $attributes, string $editRoute): void
    {
        $lowerPluralModelName = $this->lowerPluralName($modelName);
        $modelVariable = $this->variableNaming($modelName);

        $stubProperties = [
            "{modelName}" => $modelName,
            "{editRoute}" => $editRoute,
            "{components}" => $this->generateShowViewComponents($modelVariable, $attributes),
            "{modelVariable}" => $modelVariable
        ];

        $showDirectory = base_path("resources/views/dashboard/$lowerPluralModelName/show.blade.php");

        if (!is_dir(base_path("resources/views/dashboard/$lowerPluralModelName/"))) {
            mkdir(base_path("resources/views/dashboard/$lowerPluralModelName/"), 0777, true);
        }

        if (file_exists($showDirectory)) {
            echo(" \n Show View Already Created \n");
            return;
        }

        new CreateFile(
            $stubProperties,
            $showDirectory,
            __DIR__ . "/../Commands/stubs/views/show.stub"
        );

        echo(" \n A show view for $lowerPluralModelName created \n");
    }

    /**
     * @param string $modelVariable
     * @param array $attributes
     * @return string
     */
    public function generateShowViewComponents(string $modelVariable, array $attributes): string
    {
        $components = '';
        foreach ($attributes as $attribute => $type) {
            $label = $this->getLabelName($attribute);
            if ($type == 'text') {
                $components .= "<x-long-text-field :value=\"\$$modelVariable->$attribute\" label=\"$label\"></x-long-text-field> \n";
                continue;
            }
            if ($type == 'file') {
                $components .= "<x-image-preview :imagePath=\"\$$modelVariable->$attribute\"></x-image-preview> \n";
            } else {
                $components .= "<x-small-text-field :value=\"\$$modelVariable->$attribute\" label=\"$label\"></x-small-text-field> \n";
            }
        }
        return $components;
    }

    /**
     * @param string $modelName
     * @param array $attributes
     * @param string $creatRoute
     * @param string $dataRoute
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateIndexView(string $modelName, array $attributes, string $creatRoute, string $dataRoute): void
    {
        $lowerPluralModelName = $this->lowerPluralName($modelName);
        $dataColumns = $this->generateViewDataColumns($attributes) ;

        $stubProperties = [
            "{modelName}" => $modelName,
            "{createRouteName}" => $creatRoute,
            "{htmlColumns}" => $dataColumns['html'],
            "{dataTableColumns}" => $dataColumns['json'],
            "{dataTableDataRouteName}" => $dataRoute
        ];

        $indexDirectory = base_path("resources/views/dashboard/$lowerPluralModelName/index.blade.php");

        if (!is_dir(base_path("resources/views/dashboard/$lowerPluralModelName/"))) {
            mkdir(base_path("resources/views/dashboard/$lowerPluralModelName/"), 0777, true);
        }

        if (file_exists($indexDirectory)) {
            echo(" \n Index View Already Created \n");
            return;
        }

        new CreateFile(
            $stubProperties,
            $indexDirectory,
            __DIR__ . "/../Commands/stubs/views/index.stub"
        );

        echo(" \n A index view for $lowerPluralModelName created \n");
    }

    /**
     * @param array $attributes
     * @return string[]
     */
    #[ArrayShape(['html' => "string", 'json' => "string"])]
    public function generateViewDataColumns(array $attributes): array
    {
        $html = '';
        $json = '';

        foreach ($attributes as $attribute => $type) {
            $html .= "\n<th>$attribute</th>\n";
            $json .= "{\"data\": '$attribute', searchable: true, orderable: true}, \n";
        }

        return ['html' => $html , 'json' => $json] ;
    }
}
