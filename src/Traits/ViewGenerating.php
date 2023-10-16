<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

trait ViewGenerating
{
    use RouteBinding {
        getRouteName as public;
    }

    /**
     * create an update or a create form
     *
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateCreateOrUpdateForm(string $modelName, array $attributes = [], array $nullables = [], $storeRoute = null, $updateRoute = null, $actor = null): void
    {
        $viewsName = viewNaming($modelName);
        $modelVariable = variableNaming($modelName);
        $inputs = $storeRoute ? $this->generateInputs($actor, $attributes, $nullables) : $this->generateInputs($actor, $attributes, $nullables, $modelVariable, true);
        $createdForm = $storeRoute ? 'Create' : 'Edit';

        $stubProperties = [
            '{title}' => "{$createdForm} {$modelName}",
            '{submitRoute}' => $storeRoute ?? $updateRoute,
            '{components}' => $inputs,
            '{method}' => $updateRoute ? 'PUT' : 'POST',
            '{updateParameter}' => $updateRoute ? ", \${$modelVariable}" . '->id' : '',
        ];

        $formDirectory = base_path("resources/views/dashboard/{$viewsName}/" . strtolower($createdForm) . '.blade.php');

        if (!is_dir(base_path("resources/views/dashboard/{$viewsName}/"))) {
            mkdir(base_path("resources/views/dashboard/{$viewsName}/"), 0777, true);
        }

        if (file_exists($formDirectory)) {
            $this->error("{$createdForm} Form Already Created");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $formDirectory,
            __DIR__ . '/../Commands/stubs/views/form.stub'
        );

        $this->info("{$createdForm} form for {$viewsName} created");
    }

    /**
     * @param string $modelName
     * @param string $creatRoute
     * @param string $dataRoute
     * @param array $attributes
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateIndexView(string $modelName, string $creatRoute, string $dataRoute, array $attributes = []): void
    {
        $viewsName = viewNaming($modelName);
        $dataColumns = $this->generateViewDataColumns($attributes);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{createRouteName}' => $creatRoute,
            '{htmlColumns}' => $dataColumns['html'],
            '{dataTableColumns}' => $dataColumns['json'],
            '{dataTableDataRouteName}' => $dataRoute,
        ];

        $indexDirectory = base_path("resources/views/dashboard/{$viewsName}/index.blade.php");

        if (!is_dir(base_path("resources/views/dashboard/{$viewsName}/"))) {
            mkdir(base_path("resources/views/dashboard/{$viewsName}/"), 0777, true);
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

        $this->info("index view for {$viewsName} created");
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

        $showDirectory = base_path("resources/views/dashboard/{$viewsName}/show.blade.php");

        if (!is_dir(base_path("resources/views/dashboard/{$viewsName}/"))) {
            mkdir(base_path("resources/views/dashboard/{$viewsName}/"), 0777, true);
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

        $this->info("show view for {$viewsName} created");
    }

    /**
     * generate input fields for create or update form
     */
    private function generateInputs($actor = null, array $attributes = [], array $nullables = [], string $modelVariable = '', bool $updateInput = false): string
    {
        $inputs = '';

        if (in_array('translatable', array_values($attributes))) {
            $inputs .= "<x-language-selector></x-language-selector> \n";
        }

        foreach ($attributes as $attribute => $type) {
            $attribute = columnNaming($attribute);
            $label = $this->getLabelName($attribute);
            $isRequired = !in_array($attribute, $nullables) || $type == 'file' ? 'required' : '';
            $value = $updateInput ? ($type == 'translatable' ? ":value=\"\${$modelVariable}->getRawOriginal('{$attribute}')\"" : ":value=\"\${$modelVariable}->{$attribute}\"") : null;
            $checked = $updateInput ? ":checked=\"\${$modelVariable}->{$attribute}\"" : 'checked';

            if ($type == 'key') {
                $modelName = modelNaming(str_replace('_id', '', $attribute));
                $value = str_replace('_id', '', $value);
                $select2Route = $this->getRouteName($modelName, 'web', $actor) . '.allPaginatedJson';
                $inputs .= "
                <!-- TODO::if you created this before the parent model configure this route here as you want -->
                <x-select2 label=\"{$label}\" api=\"{{route('{$select2Route}')}}\" option-value=\"id\" option-inner-text=\"id\" {$value} {$isRequired}></x-select2> \n";
            } elseif ($type == 'translatable') {
                $inputs .= "<x-translatable-input label=\"{$label}\" type='text' {$value} {$isRequired}></x-translatable-input> \n";
            } elseif ($attribute == 'email') {
                $inputs .= "\n <x-input label=\"{$label}\" type=\"email\" {$value} {$isRequired}></x-input> \n";
            } elseif ($attribute == 'password') {
                $inputs .= "\n <x-input label=\"{$label}\" type=\"password\" {$value} {$isRequired}></x-input> \n";
            } elseif (in_array($attribute, ['phone', 'phone_number', 'home_number', 'work_number', 'tele', 'telephone'])) {
                $inputs .= "\n <x-input label=\"{$label}\" type=\"tel\" {$value} {$isRequired}></x-input> \n";
            } elseif (Str::contains($attribute, ['_url', 'url_', 'URL_', '_URL'])) {
                $inputs .= "\n <x-input label=\"{$label}\" type=\"url\" {$value} {$isRequired}></x-input> \n";
            } elseif (in_array($type, ['integer', 'bigInteger', 'unsignedBigInteger', 'unsignedDouble', 'double', 'float'])) {
                $inputs .= "\n <x-input label=\"{$label}\" type=\"number\" {$value} {$isRequired}></x-input> \n";
            } elseif (in_array($type, ['string', 'json'])) {
                $inputs .= "\n <x-input label=\"{$label}\" type=\"text\" {$value} {$isRequired}></x-input> \n";
            } elseif ($type == 'text') {
                $inputs .= "\n <x-text-editor label=\"{$label}\" {$value} {$isRequired}></x-text-editor> \n";
            } elseif ($type == 'date') {
                $inputs .= "\n <x-input label=\"{$label}\" type=\"date\" {$value} {$isRequired}></x-input> \n";
            } elseif ($type == 'time') {
                $inputs .= "\n <x-input label=\"{$label}\" type=\"time\" {$value} {$isRequired}></x-input> \n";
            } elseif (in_array($type, ['dateTime', 'timestamp'])) {
                $inputs .= "\n <x-input label=\"{$label}\" type=\"datetime-local\" {$value} {$isRequired}></x-input> \n";
            } elseif ($type == 'file') {
                $inputs .= "\n <x-input label=\"{$label}\" type=\"file\" {$value} {$isRequired}></x-input> \n";
            } elseif ($type == 'boolean') {
                $inputs .= "\n <x-form-check>
                                    <x-form-check-radio name=\"{$attribute}\" value=\"{{0}}\" {$checked} {$isRequired}></x-form-check-radio>
                                    <x-form-check-radio name=\"{$attribute}\" value=\"{{1}}\" {$checked} {$isRequired}></x-form-check-radio>
                               </x-form-check> \n";
            } else {
                $inputs .= "\n <x-input label=\"{$label}\" type=\"text\" {$value} {$isRequired}></x-input> \n";
            }
        }
        return $inputs;
    }

    private function generateShowViewComponents(string $modelVariable, array $attributes = []): string
    {
        $components = '';
        foreach ($attributes as $attribute => $type) {
            $attribute = columnNaming($attribute);
            $label = $this->getLabelName($attribute);
            if ($type == 'text') {
                $components .= "<x-long-text-field :value=\"\${$modelVariable}->{$attribute}\" label=\"{$label}\"></x-long-text-field> \n";
            } elseif ($type == 'file') {
                $components .= "<x-image-preview :imagePath=\"\${$modelVariable}->{$attribute}\"></x-image-preview> \n";
            } elseif ($type == 'translatable') {
                $components .= "<x-translatable-small-text-field :value=\"\${$modelVariable}->getRawOriginal('{$attribute}')\" label=\"{$label}\"></x-translatable-small-text-field> \n";
            } else {
                $components .= "<x-small-text-field :value=\"\${$modelVariable}->{$attribute}\" label=\"{$label}\"></x-small-text-field> \n";
            }
        }

        return $components;
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['html' => "string", 'json' => "string"])]
    private function generateViewDataColumns(array $attributes = []): array
    {
        $html = '';
        $json = '';

        foreach ($attributes as $attribute => $type) {
            $attribute = columnNaming($attribute);
            $label = $this->getLabelName($attribute);
            if ($type == 'text') {
                continue;
            }
            if ($type == 'file') {
                $json .= "{
                                \"data\": '$attribute',render:
                                    function (data) {
                                        const filePath = \"{{asset(\"storage/\")}}/\" + data;
                                        return '<a href=\"' + filePath + '\" class=\"btn btn-sm btn-primary\"  target=\"_blank\">view</a>';
                                    }
                           }, \n";
                $html .= "\n<th>{$label}</th>\n";
                continue;
            }
            $json .= "{\"data\": '{$attribute}', searchable: true, orderable: true}, \n";

            $html .= "\n<th>{$label}</th>\n";
        }

        return ['html' => $html, 'json' => $json];
    }

    private function getJQueryDataTablesTranslatableColumnsIndexes(array $attributes = []): array
    {
        $translatableIndex = 1;
        $translatableColumnsIndexes = [];

        foreach ($attributes as $attribute => $type) {
            if ($type == 'translatable') {
                $translatableColumnsIndexes[$attribute] = $translatableIndex;
            }
            if ($type == 'file' || $type == 'text') {
                continue;
            }
            $translatableIndex++;
        }

        return $translatableColumnsIndexes;
    }

    public function generateOrderingQueriesForTranslatableColumns(array $attributes = []): string
    {
        $translatableColumns = $this->getJQueryDataTablesTranslatableColumnsIndexes($attributes);
        $queries = '';

        if (count($translatableColumns) <= 0) {
            return $queries;
        }

        $queries .= "\$locale = app()->getLocale() ; \n";

        foreach ($translatableColumns as $col => $index) {
            $queries .=
                "if (request()->has('order') && request('order')[0]['column'] == {$index}) {
                            \$query->order(function (\$query) use (\$locale) {
                                if (request('order')[0]['dir'] == 'asc') {
                                    \$query->orderByRaw(\"JSON_EXTRACT({$col}, ?) ASC\", ['$.\"' . \$locale . '\"']);
                                } else {
                                    \$query->orderByRaw(\"JSON_EXTRACT({$col}, ?) DESC\", ['$.\"' . \$locale . '\"']);
                                }
                            });
                 } \n";
        }

        return $queries;
    }

    /**
     * get the component label name
     * @param string $attribute
     * @return array|string
     */
    private function getLabelName(string $attribute): array|string
    {
        return str_replace('_', ' ', ucfirst($attribute));
    }
}
