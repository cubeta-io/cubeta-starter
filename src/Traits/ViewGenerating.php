<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\CreateFile;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Str;

trait ViewGenerating
{
    use NamingTrait;

    /**
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
            echo("<info>Create Form Already Created</info>");
            return;
        }

        new CreateFile(
            $stubProperties,
            $formDirectory,
            __DIR__ . "/../Commands/stubs/views/form.stub"
        );

        echo("<info>A create form for $lowerPluralModelName created</info>");
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
                               </x-form-check>";
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
     * @throws FileNotFoundException
     * @throws BindingResolutionException
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
            echo("<info>Show View Already Created</info>");
            return;
        }

        new CreateFile(
            $stubProperties,
            $showDirectory,
            __DIR__ . "/../Commands/stubs/views/show.stub"
        );

        echo("<info>A show view for $lowerPluralModelName created</info>");
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
                $components .= "<long-text-field :value=\"\$$modelVariable->$attribute\" label=\"$label\"></long-text-field>";
                continue;
            }
            if ($type == 'file') {
                $components .= "<image-preview imagePath=\"\$$modelVariable->$attribute\"></image-preview>";
            } else {
                $components .= "<small-text-field :value=\"\$$modelVariable->$attribute\" label=\"$label\"></small-text-field>";
            }
        }
        return $components;
    }
}
