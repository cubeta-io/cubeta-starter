<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\app\Models\Settings;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Error;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class ViewsGenerator extends WebControllerGenerator
{
    const FORM_STUB = __DIR__ . '/../Commands/stubs/views/form.stub';
    const SHOW_STUB = __DIR__ . '/../Commands/stubs/views/show.stub';
    const INDEX_STUB = __DIR__ . '/../Commands/stubs/views/index.stub';

    public static string $key = "views";
    public static string $configPath = "cubeta-starter.web_controller_path";

    /**
     * @param string $filePath
     * @param string $newColumn
     * @param string $htmlColName
     * @return bool
     */
    public static function addColumnToDataTable(string $filePath, string $newColumn, string $htmlColName = ""): bool
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

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public function run(): void
    {
        $modelName = $this->modelName($this->fileName);
        $routes = $this->getRoutesNames($modelName, $this->actor);

        $this->generateCreateOrUpdateForm(modelName: $modelName, storeRoute: $routes['store']);
        $this->generateCreateOrUpdateForm(modelName: $modelName, updateRoute: $routes['update']);

        $this->generateShowView($routes['edit']);
        $this->generateIndexView($routes['create'], $routes['data']);
    }

    /**
     * @param string $modelName
     * @param $storeRoute
     * @param $updateRoute
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public function generateCreateOrUpdateForm(string $modelName, $storeRoute = null, $updateRoute = null): void
    {
        $viewsName = viewNaming($modelName);
        $modelVariable = variableNaming($modelName);
        $inputs = $storeRoute
            ? $this->generateInputs()
            : $this->generateInputs($modelVariable, true);

        $createdForm = $storeRoute ? 'Create' : 'Edit';

        $stubProperties = [
            '{title}' => "{$createdForm} {$modelName}",
            '{submitRoute}' => $storeRoute ?? $updateRoute,
            '{components}' => $inputs,
            '{method}' => $updateRoute ? 'PUT' : 'POST',
            '{updateParameter}' => $updateRoute ? ", \${$modelVariable}" . '->id' : '',
        ];

        $formDirectory = base_path("resources/views/dashboard/{$viewsName}/" . strtolower($createdForm) . '.blade.php');

        $this->ensureDirectoryExists("resources/views/dashboard/{$viewsName}/");

        throw_if(file_exists($formDirectory), new Error("$formDirectory Already Exist"));

        $this->generateFileFromStub(
            $stubProperties,
            $formDirectory,
            self::FORM_STUB
        );
    }

    /**
     * @param string|null $modelVariable
     * @param bool $updateInput
     * @return string
     */
    private function generateInputs(?string $modelVariable = null, bool $updateInput = false): string
    {
        $inputs = '';

        if (in_array('translatable', array_values($this->attributes))) {
            $inputs .= "<x-language-selector/>\n";
        }

        foreach ($this->attributes as $attribute => $type) {
            $attribute = columnNaming($attribute);
            $label = $this->getLabelName($attribute);

            $isRequired = 'required';
            if (in_array($attribute, $this->nullables) || $updateInput) {
                $isRequired = '';
            }

            $value = $updateInput
                ? ($type == 'translatable'
                    ? ":value=\"\${$modelVariable}->getRawOriginal('{$attribute}')\""
                    : ":value=\"\${$modelVariable}->{$attribute}\"")
                : null;

            $checked = $updateInput
                ? ":checked=\"\${$modelVariable}->{$attribute}\""
                : 'checked';

            switch ($type) {
                case ColumnTypeEnum::KEY->value:
                {
                    $modelName = modelNaming(str_replace('_id', '', $attribute));
                    $relatedTable = Settings::make()->getTable($modelName);
                    $value = str_replace('_id', '', $value);
                    $select2Route = $this->getRouteName($modelName, ContainerType::WEB, $this->actor) . '.allPaginatedJson';
                    if (!file_exists(getModelPath($modelName)) || !file_exists(getWebControllerPath($modelName))) break;
                    if (!isMethodDefined(getWebControllerPath($modelName), 'allPaginatedJson')) break;
                    $inputs .= "<x-select2 label=\"{$label}\" name=\"{$attribute}\" api=\"{{route('{$select2Route}')}}\" option-value=\"id\" option-inner-text=\"{$relatedTable->titleable()->name}\" {$value} {$isRequired}/> \n";
                    break;
                }
                case ColumnTypeEnum::TRANSLATABLE->value:
                {
                    $inputs .= "<x-translatable-input label=\"{$label}\" name=\"{$attribute}\" type='text' {$value} {$isRequired}/> \n";
                    break;
                }
                case ColumnTypeEnum::BOOLEAN->value:
                {
                    $inputs .= "\n <x-form-check>
                                    <x-form-check-radio name=\"{$attribute}\" value=\"{{0}}\" {$checked} {$isRequired}/>
                                    <x-form-check-radio name=\"{$attribute}\" value=\"{{1}}\" {$checked} {$isRequired}/>
                               </x-form-check> \n";
                    break;
                }
                case ColumnTypeEnum::TEXT->value:
                {
                    $inputs .= "\n <x-text-editor label=\"{$label}\" name=\"{$attribute}\" {$value} {$isRequired}/> \n";
                    break;
                }
                default:
                {
                    $fieldType = $this->getInputTagType($attribute, $type);
                    $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute}\" type=\"{$fieldType}\" {$value} {$isRequired}/> \n";
                    break;
                }
            }
        }
        return $inputs;
    }

    /**
     * @param string $attribute
     * @return array|string
     */
    private function getLabelName(string $attribute): array|string
    {
        return str_replace('_id', ' ', ucfirst($attribute));
    }

    /**
     * @param string $colName
     * @param string $colType
     * @return string
     */
    private function getInputTagType(string $colName, string $colType): string
    {
        if (str_contains($colName, "email")) return "email";
        elseif ($colName == "password") return "password";
        elseif (in_array($colName, ['phone', 'phone_number', 'home_number', 'work_number', 'tel', 'telephone'])
            || str_contains($colName, "phone")) return "tel";
        elseif (str_contains($colName, "url")) return "url";
        elseif (ColumnTypeEnum::isNumericType($colType)) return "number";
        elseif (in_array($colType, [ColumnTypeEnum::JSON->value, ColumnTypeEnum::STRING->value])) return "text";
        elseif (in_array($colType, [ColumnTypeEnum::DATETIME->value, ColumnTypeEnum::TIMESTAMP->value])) return "datetime-local";
        elseif ($colType == ColumnTypeEnum::DATE->value) return "date";
        elseif ($colType == ColumnTypeEnum::TIME->value) return "time";
        elseif ($colType == ColumnTypeEnum::FILE->value) return "file";
        else return "text";
    }

    /**
     * @param string $editRoute
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public function generateShowView(string $editRoute): void
    {
        $modelName = modelNaming($this->fileName);
        $viewsName = viewNaming($modelName);
        $modelVariable = variableNaming($modelName);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{editRoute}' => $editRoute,
            '{components}' => $this->generateShowViewComponents($modelVariable),
            '{modelVariable}' => $modelVariable,
        ];

        $showDirectory = base_path("resources/views/dashboard/{$viewsName}/show.blade.php");

        if (!is_dir(base_path("resources/views/dashboard/{$viewsName}/"))) {
            mkdir(base_path("resources/views/dashboard/{$viewsName}/"), 0777, true);
        }

        throw_if(file_exists($showDirectory), new Error("$showDirectory Already Exists"));

        $this->generateFileFromStub(
            $stubProperties,
            $showDirectory,
            self::SHOW_STUB
        );
    }

    /**
     * @param string $modelVariable
     * @return string
     */
    private function generateShowViewComponents(string $modelVariable): string
    {
        $components = '';
        foreach ($this->attributes as $attribute => $type) {
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

    /**
     * @param string $creatRoute
     * @param string $dataRoute
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public function generateIndexView(string $creatRoute, string $dataRoute): void
    {
        $modelName = $this->modelName($this->fileName);
        $viewsName = viewNaming($modelName);
        $dataColumns = $this->generateDataTableColumns();

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

        throw_if(file_exists($indexDirectory), new Error("$indexDirectory Already Exists"));

        $this->generateFileFromStub(
            $stubProperties,
            $indexDirectory,
            self::INDEX_STUB
        );
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['html' => "string", 'json' => "string"])]
    private function generateDataTableColumns(): array
    {
        $html = '';
        $json = '';

        foreach ($this->attributes as $attribute => $type) {
            $attribute = columnNaming($attribute);
            $label = $this->getLabelName($attribute);

            if ($type == 'text') {
                continue;
            }

            if ($type == 'file') {
                $json .= "{\n\t\"data\": '{$attribute}',render:function (data) {const filePath = \"{{asset(\"storage/\")}}/\" + data; return `<div class=\"gallery\"><a href=\"\${filePath}\"><img class=\"img-fluid\" src=\"\${filePath}\" alt=\"\"/></a>`;}}, \n";
                $html .= "\n<th>{$label}</th>\n";
                continue;
            }

            $json .= "{\"data\": '{$attribute}', searchable: true, orderable: true}, \n";

            $html .= "\n<th>{$label}</th>\n";
        }

        return ['html' => $html, 'json' => $json];
    }
}
