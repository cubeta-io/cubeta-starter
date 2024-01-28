<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\app\Models\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

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
     * generate input fields for create or update form
     */
    private function generateInputs($actor = null, array $attributes = [], array $nullables = [], string $modelVariable = '', bool $updateInput = false): string
    {
        $inputs = '';

        if (in_array('translatable', array_values($attributes))) {
            $inputs .= "<x-language-selector/> \n";
        }

        foreach ($attributes as $attribute => $type) {
            $attribute = columnNaming($attribute);
            $label = $this->getLabelName($attribute);

            $isRequired = (!in_array($attribute, $nullables) || $type == 'file')
                ? 'required'
                : '';

            /**
             * this for checking if the current inputs are for an update form
             * so if it is an update remove required attribute
             * if it isn't an update form left the $isRequired variable as it is
             */
            $isRequired = ($modelVariable != '')
                ? ''
                : $isRequired;

            $value = $updateInput ? ($type == 'translatable' ? ":value=\"\${$modelVariable}->getRawOriginal('{$attribute}')\"" : ":value=\"\${$modelVariable}->{$attribute}\"") : null;
            $checked = $updateInput ? ":checked=\"\${$modelVariable}->{$attribute}\"" : 'checked';

            if ($type == 'key') {
                $modelName = modelNaming(str_replace('_id', '', $attribute));
                $relatedTable = Settings::make()->getTable($modelName);
                $value = str_replace('_id', '', $value);
                $select2Route = $this->getRouteName($modelName, ContainerType::WEB, $actor) . '.allPaginatedJson';

                if (!file_exists(getModelPath($modelName)) || !file_exists(getWebControllerPath($modelName))) {
                    continue;
                }

                if (!isMethodDefined(getWebControllerPath($modelName), 'allPaginatedJson')) {
                    continue;
                }

                $inputs .= "
                <!-- TODO::if you created this before the parent model configure this route here as you want -->
                <x-select2 label=\"{$label}\" name=\"{$attribute}\" api=\"{{route('{$select2Route}')}}\" option-value=\"id\" option-inner-text=\"{$relatedTable->titleable()->name}\" {$value} {$isRequired}/> \n";
            } elseif ($type == 'translatable') {

                $inputs .= "<x-translatable-input label=\"{$label}\" name=\"{$attribute}\" type='text' {$value} {$isRequired}/> \n";

            } elseif ($attribute == 'email') {

                $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute}\" type=\"email\" {$value} {$isRequired}/> \n";

            } elseif ($attribute == 'password') {

                $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute}\" type=\"password\" {$value} {$isRequired}/> \n";

            } elseif (in_array($attribute, ['phone', 'phone_number', 'home_number', 'work_number', 'tele', 'telephone'])) {

                $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute}\" type=\"tel\" {$value} {$isRequired}/> \n";

            } elseif (Str::contains($attribute, ['_url', 'url_', 'URL_', '_URL'])) {

                $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute}\" type=\"url\" {$value} {$isRequired}/> \n";

            } elseif (in_array($type, ['integer', 'bigInteger', 'unsignedBigInteger', 'unsignedDouble', 'double', 'float'])) {

                $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute}\" type=\"number\" {$value} {$isRequired}/> \n";

            } elseif (in_array($type, ['string', 'json'])) {

                $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute}\" type=\"text\" {$value} {$isRequired}/> \n";

            } elseif ($type == 'text') {

                $inputs .= "\n <x-text-editor label=\"{$label}\" name=\"{$attribute}\" {$value} {$isRequired}/> \n";

            } elseif ($type == 'date') {

                $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute}\" type=\"date\" {$value} {$isRequired}/> \n";

            } elseif ($type == 'time') {

                $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute}\" type=\"time\" {$value} {$isRequired}/> \n";

            } elseif (in_array($type, ['dateTime', 'timestamp'])) {

                $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute}\" type=\"datetime-local\" {$value} {$isRequired}/> \n";

            } elseif ($type == 'file') {

                $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute}\" type=\"file\" {$value} {$isRequired}/> \n";

            } elseif ($type == 'boolean') {

                $inputs .= "\n <x-form-check>
                                    <x-form-check-radio name=\"{$attribute}\" value=\"{{0}}\" {$checked} {$isRequired}/>
                                    <x-form-check-radio name=\"{$attribute}\" value=\"{{1}}\" {$checked} {$isRequired}/>
                               </x-form-check> \n";

            } else {

                $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute}\" type=\"text\" {$value} {$isRequired}/> \n";

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
        return str_replace('_id', ' ', ucfirst($attribute));
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
        $dataColumns = $this->generateDataTableColumns($attributes);

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
     * @return string[]
     */
    #[ArrayShape(['html' => "string", 'json' => "string"])]
    private function generateDataTableColumns(array $attributes = []): array
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
                                \"data\": '{$attribute}',render:
                                    function (data) {
                                        const filePath = \"{{asset(\"storage/\")}}/\" + data;
                                        return `<div class=\"gallery\"><a href=\"\${filePath}\"><img class=\"img-fluid\" src=\"\${filePath}\"/></a>`;
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

    private function generateShowViewComponents(string $modelVariable, array $attributes = []): string
    {
        $components = '';
        foreach ($attributes as $attribute => $type) {
            $attribute = columnNaming($attribute);
            $label = $this->getLabelName($attribute);
            if ($type == 'text') {
                $components .= "<x-long-text-field :value=\"\${$modelVariable}->{$attribute}\" label=\"{$label}\"/> \n";
            } elseif ($type == 'file') {
                $components .= "<x-image-preview :imagePath=\"\${$modelVariable}->{$attribute}\"/> \n";
            } elseif ($type == 'translatable') {
                $components .= "<x-translatable-small-text-field :value=\"\${$modelVariable}->getRawOriginal('{$attribute}')\" label=\"{$label}\"/> \n";
            } else {
                $components .= "<x-small-text-field :value=\"\${$modelVariable}->{$attribute}\" label=\"{$label}\"/> \n";
            }
        }

        return $components;
    }

    public function generateOrderingQueriesForTranslatableColumns(array $attributes = []): string
    {
        $translatableColumns = $this->getJQueryDataTablesTranslatableColumnsIndexes($attributes);
        $queries = '';

        if (count($translatableColumns) <= 0) {
            return $queries;
        }

        $queries .= "\$query = \$this->orderTranslatableColumns(\$query, [\n";
        foreach ($translatableColumns as $col => $index) {
            $queries .= "['orderIndex' => 0, 'columnIndex' => $index, 'columnName' => '$col'],\n";
        }

        $queries .= "\n]);";

        return $queries;
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

    private function addColumnToDataTable(string $filePath, string $newColumn, string $htmlColName = ""): bool
    {
        if (!file_exists($filePath)) {
            echo "$filePath Doesn't Exists \n";
            return false;
        }

        if (checkIfContentExistInFile($filePath, $newColumn) || checkIfContentExistInFile($filePath, $htmlColName)) {
            echo "The New Column Already Exists In [ $filePath ] \n";
        }

        $fileContent = file_get_contents($filePath);

        // adding html column
        if (str_contains($fileContent, "<th>Action</th>")) {
            $fileContent = str_replace("<th>Action</th>", "<th>$htmlColName</th>\n<th>Action</th>\n", $fileContent);
        } else if (str_contains($fileContent, "</tr>")) {
            $fileContent = str_replace("</tr>", "<th>$htmlColName</th>\n</tr>\n", $fileContent);
        } else {
            echo "We Couldn't find the Proper Place To Add New Column In The HTML Of $filePath \n";
            return false;
        }

        // Find the columns array
        $pattern = '/\bcolumns\s*:\s*\[\s*([^]]*)\s*]/';

        preg_match($pattern, $fileContent, $matches);

        if (isset($matches[1])) {
            $existingColumns = trim($matches[1]);
            if (!empty($existingColumns)) {
                if (preg_match('/}(\s|\n)*,(\s|\n)*\{/i', $existingColumns)) {
                    $newColumns = prependLastMatch('/\s*}\s*,\s*\{/', "},{" . $newColumn, $existingColumns);
                } else {
                    $newColumns = "$existingColumns , $newColumn";
                }
            } else {
                $newColumns = $newColumn;
            }
            $updatedContent = str_replace($matches[1], $newColumns, $fileContent);

            file_put_contents($filePath, $updatedContent);
            echo "New Content Has Been Added To $filePath \n";
            return true;
        }
        // If the columns array is not found, try to find an empty array
        $emptyArrayPattern = '/\bcolumns\s*:\s*\[\s*]\s*/';

        preg_match($emptyArrayPattern, $fileContent, $emptyArrayMatches);

        if (isset($emptyArrayMatches[0])) {
            // If an empty array is found, replace it with the new column
            $updatedContent = str_replace($emptyArrayMatches[0], 'columns: [' . $newColumn . ']', $fileContent);

            // Write the updated content back to the file
            file_put_contents($filePath, $updatedContent);

            echo "New Content Has Been Added To $filePath \n";
            return true;
        }

        echo "We Couldn't find the Proper Place To Add New Column In The Script Tag Of $filePath \n";
        return false;
    }
}
