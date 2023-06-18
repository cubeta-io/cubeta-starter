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
    public function generateCreateOrUpdateForm(string $modelName, array $attributes = [], $storeRoute = null, $updateRoute = null): void
    {
        $viewsName = viewNaming($modelName);
        $modelVariable = variableNaming($modelName);
        $inputs = $storeRoute ? $this->generateInputs($attributes) : $this->generateInputs($attributes, $modelVariable, true);
        $createdForm = $storeRoute ? 'Create' : 'Edit';

        $stubProperties = [
            '{title}' => "$createdForm $modelName",
            '{submitRoute}' => $storeRoute ?? $updateRoute,
            '{components}' => $inputs,
            '{method}' => $updateRoute ? 'PUT' : 'POST',
            '{updateParameter}' => $updateRoute ? ", \$$modelVariable" . '->id' : '',
        ];

        $formDirectory = base_path("resources/views/dashboard/$viewsName/" . strtolower($createdForm) . '.blade.php');

        if (!is_dir(base_path("resources/views/dashboard/$viewsName/"))) {
            mkdir(base_path("resources/views/dashboard/$viewsName/"), 0777, true);
        }

        if (file_exists($formDirectory)) {
            $this->error("$createdForm Form Already Created");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $formDirectory,
            __DIR__ . '/../Commands/stubs/views/form.stub'
        );

        $this->info("$createdForm form for $viewsName created");
    }

    /**
     * generate input fields for create or update form
     */
    private function generateInputs(array $attributes = [], string $modelVariable = '', bool $updateInput = false): string
    {
        $inputs = '';
        if (in_array('translatable', array_values($attributes))) {
            $inputs .= "<x-language-selector></x-language-selector> \n";
        }
        foreach ($attributes as $attribute => $type) {
            $label = $this->getLabelName($attribute);
            $value = $updateInput ? ":value=\"\$$modelVariable->$attribute\"" : null;
            $checked = $updateInput ? ":checked=\"\$$modelVariable->$attribute\"" : 'checked';

            if ($type == 'translatable') {
                $inputs .= "<x-translatable-input label=\"$label\" type='text'></x-translatable-input>";
            } elseif ($attribute == 'email') {
                $inputs .= "\n <x-input label=\"$label\" type=\"email\" $value></x-input> \n";
            } elseif ($attribute == 'password') {
                $inputs .= "\n <x-input label=\"$label\" type=\"password\" $value></x-input> \n";
            } elseif (in_array($attribute, ['phone', 'phone_number', 'home_number', 'work_number', 'tele', 'telephone'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"tel\" $value></x-input> \n";
            } elseif (Str::contains($attribute, ['_url', 'url_', 'URL_', '_URL'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"url\" $value></x-input> \n";
            } elseif (in_array($type, ['integer', 'bigInteger', 'unsignedBigInteger', 'unsignedDouble', 'double', 'float'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"number\" $value></x-input> \n";
            } elseif (in_array($type, ['string', 'json'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"text\" $value></x-input> \n";
            } elseif ($type == 'text') {
                $inputs .= "\n <x-text-editor label=\"$label\" $value></x-text-editor> \n";
            } elseif ($type == 'date') {
                $inputs .= "\n <x-input label=\"$label\" type=\"date\" $value></x-input> \n";
            } elseif ($type == 'time') {
                $inputs .= "\n <x-input label=\"$label\" type=\"time\" $value></x-input> \n";
            } elseif (in_array($type, ['dateTime', 'timestamp'])) {
                $inputs .= "\n <x-input label=\"$label\" type=\"datetime-local\" $value></x-input> \n";
            } elseif ($type == 'file') {
                $inputs .= "\n <x-input label=\"$label\" type=\"file\" $value></x-input> \n";
            } elseif ($type == 'boolean') {
                $inputs .= "\n <x-form-check>
                                    <x-form-check-radio name=\"$attribute\" value=\"{{false}}\" $checked></x-form-check-radio>
                                    <x-form-check-radio name=\"$attribute\" value=\"{{true}}\" $checked></x-form-check-radio>
                               </x-form-check> \n";
            } else {
                $inputs .= "\n <x-input label=\"$label\" type=\"text\" $value></x-input> \n";
            }
        }
        return $inputs;
    }

    /**
     * get the component label name
     * @param string $attribute
     * @return array|string
     */
    private function getLabelName(string $attribute): array|string
    {
        return str_replace('_', ' ', Str::title(Str::singular($attribute)));
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateShowView(string $modelName, string $editRoute, array $attributes = []): void
    {
        $viewsName = viewNaming($modelName);
        $modelVariable = variableNaming($modelName);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{editRoute}' => $editRoute,
            '{components}' => $this->generateShowViewComponents($modelVariable, $attributes),
            '{modelVariable}' => $modelVariable,
        ];

        $showDirectory = base_path("resources/views/dashboard/$viewsName/show.blade.php");

        if (!is_dir(base_path("resources/views/dashboard/$viewsName/"))) {
            mkdir(base_path("resources/views/dashboard/$viewsName/"), 0777, true);
        }

        if (file_exists($showDirectory)) {
            $this->error(" \n Show View Already Created \n");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $showDirectory,
            __DIR__ . '/../Commands/stubs/views/show.stub'
        );

        $this->info("show view for $viewsName created");
    }

    private function generateShowViewComponents(string $modelVariable, array $attributes = []): string
    {
        $components = '';
        if (count(array_keys($attributes , 'translatable'))){
            $components .= "<div class=\"p-3\"><x-language-selector></x-language-selector></div> \n";
        }
        foreach ($attributes as $attribute => $type) {
            $label = $this->getLabelName($attribute);
            if ($type == 'text') {
                $components .= "<x-long-text-field :value=\"\$$modelVariable->$attribute\" label=\"$label\"></x-long-text-field> \n";
            } elseif ($type == 'file') {
                $components .= "<x-image-preview :imagePath=\"\$$modelVariable->$attribute\"></x-image-preview> \n";
            } elseif ($type == 'translatable') {
                $components .= "<x-translatable-small-text-field :value=\"\$$modelVariable->$attribute\" label=\"$label\"></x-translatable-small-text-field> \n";
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
    public function generateIndexView(string $modelName, string $creatRoute, string $dataRoute, array $attributes = []): void
    {
        $viewsName = viewNaming($modelName);
        $dataColumns = $this->generateViewDataColumns($attributes);
        $translationComponents = $this->handleSelectLanguageButton($attributes);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{createRouteName}' => $creatRoute,
            '{htmlColumns}' => $dataColumns['html'],
            '{dataTableColumns}' => $dataColumns['json'],
            '{dataTableDataRouteName}' => $dataRoute,
            '{handle-selected-language}' => $translationComponents['js-code'] ,
            '{language-selector}' => $translationComponents['view-component']
        ];

        $indexDirectory = base_path("resources/views/dashboard/$viewsName/index.blade.php");

        if (!is_dir(base_path("resources/views/dashboard/$viewsName/"))) {
            mkdir(base_path("resources/views/dashboard/$viewsName/"), 0777, true);
        }

        if (file_exists($indexDirectory)) {
            $this->error('Index View Already Created');

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $indexDirectory,
            __DIR__ . '/../Commands/stubs/views/index.stub'
        );

        $this->info("index view for $viewsName created");
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['html' => 'string', 'json' => 'string'])]
    private function generateViewDataColumns(array $attributes = []): array
    {
        $html = '';
        $json = '';

        foreach ($attributes as $attribute => $type) {
            $label = $this->getLabelName($attribute);
            if ($type == 'file' || $type == 'text') {
                continue;
            }
            $html .= "\n<th>$label</th>\n";
            if ($type == 'translatable') {
                $json .= "{
                             data: 'description',
                             searchable: true,
                             orderable: true,
                             render: function(description, type, row) {
                                 if (type === 'display') {
                                    description = JSON.parse(description.replace(/&quot;/g, '\"'));
                                    var output = description[selectedLanguage] || '';
                                    return selectedLanguage.toUpperCase() + ': ' + output + '<br>';
                                 }
                                return description;
                             }
                           },";
            } else {
                $json .= "{\"data\": '$attribute', searchable: true, orderable: true}, \n";
            }
        }

        return ['html' => $html, 'json' => $json];
    }

    /**
     * @param array $attributes
     * @return string[]
     */
    private function handleSelectLanguageButton(array $attributes = []): array
    {
        $translatableAttributes = array_keys($attributes, 'translatable');

        if (count($translatableAttributes) > 0) {
            return [
                'js-code' => "var selectedLanguage = $('input[name=\"selected-language\"]:checked').val();
                    $('input[name=\"selected-language\"]').on('click', function() {
                        selectedLanguage = $(this).val();
                        table.draw();
                    });",
                'view-component' => "<div class=\"p-3\"><x-language-selector></x-language-selector></div>"
            ];
        } else return [
            'json-code' => '',
            'view-component' => ''
        ];
    }
}
