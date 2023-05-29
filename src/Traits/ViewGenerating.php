<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

trait ViewGenerating
{
    /**
     * create an update or a create form
     *
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateCreateOrUpdateForm(string $modelName, array $attributes, $storeRoute = null, $updateRoute = null): void
    {
        $lowerPluralModelName = lowerPluralName($modelName);
        $modelVariable = variableNaming($modelName);
        $inputs = $storeRoute ? $this->generateInputs($attributes) : $this->generateInputs($attributes, $modelVariable, true);
        $createdForm = $storeRoute ? 'Create' : 'Update';

        $stubProperties = [
            '{title}' => "$createdForm $modelName",
            '{submitRoute}' => $storeRoute ?? $updateRoute,
            '{components}' => $inputs,
            '{method}' => $updateRoute ? 'PUT' : 'POST',
            '{updateParameter}' => $updateRoute ? ", \$$modelVariable".'->id' : '',
        ];

        $formDirectory = base_path("resources/views/dashboard/$lowerPluralModelName/".strtolower($createdForm).'.blade.php');

        if (! is_dir(base_path("resources/views/dashboard/$lowerPluralModelName/"))) {
            mkdir(base_path("resources/views/dashboard/$lowerPluralModelName/"), 0777, true);
        }

        if (file_exists($formDirectory)) {
            $this->error("$createdForm Form Already Created");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $formDirectory,
            __DIR__.'/../Commands/stubs/views/form.stub'
        );

        $this->info("$createdForm form for $lowerPluralModelName created");
    }

    /**
     * generate input fields for create or update form
     */
    public function generateInputs(array $attributes, string $modelVariable = '', bool $updateInput = false): string
    {
        $inputs = '';
        foreach ($attributes as $attribute => $type) {
            $label = $this->getLabelName($attribute);
            $value = $updateInput ? ":value=\"\$$modelVariable->$attribute\"" : null;
            $checked = $updateInput ? ":checked=\"\$$modelVariable->$attribute\"" : 'checked';

            if ($attribute == 'email') {
                $inputs .= "\n <x-input label=\"$label\" type=\"email\" $value></x-input> \n";

                continue;
            }

            if ($attribute == 'password') {
                $inputs .= "\n <x-input label=\"$label\" type=\"password\" $value></x-input> \n";

                continue;
            }

            if (in_array($attribute, ['phone', 'phone_number', 'home_number', 'work_number', 'tele', 'telephone'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"tel\" $value></x-input> \n";

                continue;
            }

            if (Str::contains($attribute, ['_url', 'url_', 'URL_', '_URL'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"url\" $value></x-input> \n";

                continue;
            }

            if (in_array($type, ['integer', 'bigInteger', 'unsignedBigInteger', 'unsignedDouble', 'double', 'float'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"number\" $value></x-input> \n";
            }

            if (in_array($type, ['string', 'json'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"text\" $value></x-input> \n";
            }

            if ($type == 'text') {
                $inputs .= "\n <x-text-editor label=\"$label\" $value></x-text-editor> \n";
            }

            if ($type == 'date') {
                $inputs .= "\n <x-input label=\"$label\" type=\"date\" $value></x-input> \n";
            }

            if ($type == 'time') {
                $inputs .= "\n <x-input label=\"$label\" type=\"time\" $value></x-input> \n";
            }

            if (in_array($type, ['dateTime', 'timestamp'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"datetime-local\" $value></x-input> \n";
            }

            if ($type == 'file') {
                $inputs .= "\n <x-input label=\"$label\" type=\"file\" $value></x-input> \n";
            }

            if ($type == 'boolean') {
                $inputs .= "\n <x-form-check>
                                    <x-form-check-radio name=\"$attribute\" value=\"is $attribute\" $checked></x-form-check-radio>
                                    <x-form-check-radio name=\"$attribute\" value=\"not $attribute\" $checked></x-form-check-radio>
                               </x-form-check> \n";
            }
        }

        return $inputs;
    }

    public function getLabelName(string $attribute): array|string
    {
        return str_replace('_', ' ', Str::title(Str::singular($attribute)));
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateShowView(string $modelName, array $attributes, string $editRoute): void
    {
        $lowerPluralModelName = lowerPluralName($modelName);
        $modelVariable = variableNaming($modelName);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{editRoute}' => $editRoute,
            '{components}' => $this->generateShowViewComponents($modelVariable, $attributes),
            '{modelVariable}' => $modelVariable,
        ];

        $showDirectory = base_path("resources/views/dashboard/$lowerPluralModelName/show.blade.php");

        if (! is_dir(base_path("resources/views/dashboard/$lowerPluralModelName/"))) {
            mkdir(base_path("resources/views/dashboard/$lowerPluralModelName/"), 0777, true);
        }

        if (file_exists($showDirectory)) {
            $this->error(" \n Show View Already Created \n");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $showDirectory,
            __DIR__.'/../Commands/stubs/views/show.stub'
        );

        $this->info("show view for $lowerPluralModelName created");
    }

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
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateIndexView(string $modelName, array $attributes, string $creatRoute, string $dataRoute): void
    {
        $lowerPluralModelName = lowerPluralName($modelName);
        $dataColumns = $this->generateViewDataColumns($attributes);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{createRouteName}' => $creatRoute,
            '{htmlColumns}' => $dataColumns['html'],
            '{dataTableColumns}' => $dataColumns['json'],
            '{dataTableDataRouteName}' => $dataRoute,
        ];

        $indexDirectory = base_path("resources/views/dashboard/$lowerPluralModelName/index.blade.php");

        if (! is_dir(base_path("resources/views/dashboard/$lowerPluralModelName/"))) {
            mkdir(base_path("resources/views/dashboard/$lowerPluralModelName/"), 0777, true);
        }

        if (file_exists($indexDirectory)) {
            $this->error('Index View Already Created');

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $indexDirectory,
            __DIR__.'/../Commands/stubs/views/index.stub'
        );

        $this->info("index view for $lowerPluralModelName created");
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['html' => 'string', 'json' => 'string'])]
    public function generateViewDataColumns(array $attributes): array
    {
        $html = '';
        $json = '';

        foreach ($attributes as $attribute => $type) {
            $label = $this->getLabelName($attribute);
            $html .= "\n<th>$label</th>\n";
            if ($type == 'file') {
                $json .= "{\"data\": \"$attribute\", \"render\": function (data) {return '<img src=\"' + data + '\" width=\"40px\">';}}, \n";
            } else {
                $json .= "{\"data\": '$attribute', searchable: true, orderable: true}, \n";
            }

        }

        return ['html' => $html, 'json' => $json];
    }
}
