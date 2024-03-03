<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\app\Models\CubetaAttribute;
use Cubeta\CubetaStarter\app\Models\CubetaTable;
use Cubeta\CubetaStarter\app\Models\Settings;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\LogsMessages\CubeLog;
use Cubeta\CubetaStarter\LogsMessages\CubeWarning;
use Cubeta\CubetaStarter\LogsMessages\Errors\NotFound;
use Cubeta\CubetaStarter\LogsMessages\Info\ContentAppended;
use Cubeta\CubetaStarter\LogsMessages\Warnings\ContentAlreadyExist;
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
     * @param CubePath $filePath
     * @param string $newColumn
     * @param string $htmlColName
     * @return bool
     */
    public static function addColumnToDataTable(CubePath $filePath, string $newColumn, string $htmlColName = ""): bool
    {
        if (!$filePath->exist()) {
            CubeLog::add(new NotFound($filePath->fullPath, "Trying To Add $newColumn To The Datatable Columns in : [$filePath->fullPath]"));
            return false;
        }

        if (FileUtils::checkIfContentExistInFile($filePath, $newColumn) || FileUtils::checkIfContentExistInFile($filePath, $htmlColName)) {
            CubeLog::add(new ContentAlreadyExist($newColumn, $filePath->fullPath, "Trying To Add $newColumn To The Datatable Columns in : [$filePath->fullPath]"));
            return false;
        }

        $fileContent = $filePath->getContent();

        // adding html column
        if (str_contains($fileContent, "<th>Action</th>")) {
            $fileContent = str_replace("<th>Action</th>", "<th>$htmlColName</th>\n<th>Action</th>\n", $fileContent);
        } else if (str_contains($fileContent, "</tr>")) {
            $fileContent = str_replace("</tr>", "<th>$htmlColName</th>\n</tr>\n", $fileContent);
        } else {
            CubeLog::add(new CubeWarning(
                "We Couldn't find the Proper Place To Add New Column In The HTML Of [$filePath->fullPath]",
                "Trying To Add $newColumn To The Datatable Columns in : [$filePath->fullPath]"
            ));
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

            $filePath->putContent($updatedContent);
            CubeLog::add(new ContentAppended($newColumn, $filePath->fullPath));
            return true;
        }
        // If the columns array is not found, try to find an empty array
        $emptyArrayPattern = '/\bcolumns\s*:\s*\[\s*]\s*/';

        preg_match($emptyArrayPattern, $fileContent, $emptyArrayMatches);

        if (isset($emptyArrayMatches[0])) {
            // If an empty array is found, replace it with the new column
            $updatedContent = str_replace($emptyArrayMatches[0], 'columns: [' . $newColumn . ']', $fileContent);

            // Write the updated content back to the file
            $filePath->putContent($updatedContent);

            CubeLog::add(new ContentAppended($newColumn, $filePath->fullPath));
            return true;
        }

        CubeLog::add(new CubeWarning(
            "We Couldn't find the Proper Place To Add New Column In The HTML Of [$filePath->fullPath]",
            "Trying To Add $newColumn To The Datatable Columns in : [$filePath->fullPath]"
        ));
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
        $routes = $this->getRoutesNames($this->table, $this->actor);

        $this->generateCreateOrUpdateForm(storeRoute: $routes['store']);
        $this->generateCreateOrUpdateForm(storeRoute: $routes['update'], updateRoute: $routes['update']);

        $this->generateShowView($routes['edit']);
        $this->generateIndexView($routes['create'], $routes['data']);
    }

    /**
     * @param $storeRoute
     * @param $updateRoute
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public function generateCreateOrUpdateForm($storeRoute = null, $updateRoute = null): void
    {
        $viewsName = $this->table->viewNaming();
        $modelVariable = $this->table->variableNaming();
        $inputs = $storeRoute
            ? $this->generateInputs()
            : $this->generateInputs($modelVariable, true);

        $createdForm = $storeRoute ? 'Create' : 'Edit';

        $stubProperties = [
            '{title}' => "{$createdForm} {$this->table->modelName}",
            '{submitRoute}' => $storeRoute ?? $updateRoute,
            '{components}' => $inputs,
            '{method}' => $updateRoute ? 'PUT' : 'POST',
            '{updateParameter}' => $updateRoute ? ", \${$modelVariable}" . '->id' : '',
        ];

        $formPath = CubePath::make("resources/views/dashboard/{$viewsName}/" . strtolower($createdForm) . '.blade.php');

        if ($formPath->exist()) {
            $formPath->logAlreadyExist("When Generating $createdForm Form For ({$this->table->modelName}) Model");
        }

        $formPath->ensureDirectoryExists();

        FileUtils::generateFileFromStub(
            $stubProperties,
            $formPath->fullPath,
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

        foreach ($this->table->attributes() as $attribute) {
            $label = $this->getLabelName($attribute->name);

            $isRequired = 'required';
            if ($attribute->nullable || $updateInput) {
                $isRequired = '';
            }

            $value = $updateInput
                ? ($attribute->type == ColumnTypeEnum::TRANSLATABLE->value
                    ? ":value=\"\${$modelVariable}->getRawOriginal('{$attribute->name}')\""
                    : ":value=\"\${$modelVariable}->{$attribute->name}\"")
                : null;

            $checked = $updateInput
                ? ":checked=\"\${$modelVariable}->{$attribute->name}\""
                : 'checked';

            switch ($attribute->type) {
                case ColumnTypeEnum::KEY->value:
                {
                    $model = CubetaTable::create($attribute->modelNaming(str_replace('_id', '', $attribute->name)));
                    $relatedTable = Settings::make()->getTable($model);
                    $value = str_replace('_id', '', $value);
                    $select2Route = $this->getRouteName($model, ContainerType::WEB, $this->actor) . '.allPaginatedJson';
                    if (!$model->getModelPath()->exist() || !$model->getWebControllerPath()->exist()) break;
                    if (!isMethodDefined($model->getWebControllerPath()->fullPath, 'allPaginatedJson')) break;
                    $inputs .= "<x-select2 label=\"{$label}\" name=\"{$attribute}\" api=\"{{route('{$select2Route}')}}\" option-value=\"id\" option-inner-text=\"{$relatedTable->titleable()->name}\" {$value} {$isRequired}/> \n";
                    break;
                }
                case ColumnTypeEnum::TRANSLATABLE->value:
                {
                    $inputs .= "<x-translatable-input label=\"{$label}\" name=\"{$attribute->name}\" type='text' {$value} {$isRequired}/> \n";
                    break;
                }
                case ColumnTypeEnum::BOOLEAN->value:
                {
                    $inputs .= "\n <x-form-check>
                                    <x-form-check-radio name=\"{$attribute->name}\" value=\"{{0}}\" {$checked} {$isRequired}/>
                                    <x-form-check-radio name=\"{$attribute->name}\" value=\"{{1}}\" {$checked} {$isRequired}/>
                               </x-form-check> \n";
                    break;
                }
                case ColumnTypeEnum::TEXT->value:
                {
                    $inputs .= "\n <x-text-editor label=\"{$label}\" name=\"{$attribute->name}\" {$value} {$isRequired}/> \n";
                    break;
                }
                default:
                {
                    $fieldType = $this->getInputTagType($attribute);
                    $inputs .= "\n <x-input label=\"{$label}\" name=\"{$attribute->name}\" type=\"{$fieldType}\" {$value} {$isRequired}/> \n";
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
     * @param CubetaAttribute $attribute
     * @return string
     */
    private function getInputTagType(CubetaAttribute $attribute): string
    {
        if (str_contains($attribute->name, "email")) return "email";
        elseif ($attribute->name == "password") return "password";
        elseif (in_array($attribute->name, ['phone', 'phone_number', 'home_number', 'work_number', 'tel', 'telephone'])
            || str_contains($attribute->name, "phone")) return "tel";
        elseif (str_contains($attribute->name, "url")) return "url";
        elseif (ColumnTypeEnum::isNumericType($attribute->type)) return "number";
        elseif (in_array($attribute->type, [ColumnTypeEnum::JSON->value, ColumnTypeEnum::STRING->value])) return "text";
        elseif (in_array($attribute->type, [ColumnTypeEnum::DATETIME->value, ColumnTypeEnum::TIMESTAMP->value])) return "datetime-local";
        elseif ($attribute->type == ColumnTypeEnum::DATE->value) return "date";
        elseif ($attribute->type == ColumnTypeEnum::TIME->value) return "time";
        elseif ($attribute->type == ColumnTypeEnum::FILE->value) return "file";
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
        $viewsName = $this->table->viewNaming();
        $stubProperties = [
            '{modelName}' => $this->table->modelName,
            '{editRoute}' => $editRoute,
            '{components}' => $this->generateShowViewComponents(),
            '{modelVariable}' => $this->table->variableNaming(),
        ];

        $showPath = CubePath::make("resources/views/dashboard/{$viewsName}/show.blade.php");

        if ($showPath->exist()) {
            $showPath->logAlreadyExist("When Generating Show Page For  ({$this->table->modelName}) Model");
        }

        $showPath->ensureDirectoryExists();

        FileUtils::generateFileFromStub(
            $stubProperties,
            $showPath,
            self::SHOW_STUB
        );
    }

    /**
     * @return string
     */
    private function generateShowViewComponents(): string
    {
        $modelVariable = $this->table->variableNaming();
        $components = '';
        foreach ($this->table->attributes as $attribute) {
            $label = $this->getLabelName($attribute->name);
            if ($attribute->type == ColumnTypeEnum::TEXT->value) {
                $components .= "<x-long-text-field :value=\"\${$modelVariable}->{$attribute->name}\" label=\"{$label}\"/> \n";
            } elseif ($attribute->type == ColumnTypeEnum::FILE->value) {
                $components .= "<x-image-preview :imagePath=\"\${$modelVariable}->{$attribute->name}\"/> \n";
            } elseif ($attribute->type == ColumnTypeEnum::TRANSLATABLE->value) {
                $components .= "<x-translatable-small-text-field :value=\"\${$modelVariable}->getRawOriginal('{$attribute->name}')\" label=\"{$label}\"/> \n";
            } else {
                $components .= "<x-small-text-field :value=\"\${$modelVariable}->{$attribute->name}\" label=\"{$label}\"/> \n";
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
        $dataColumns = $this->generateDataTableColumns();

        $stubProperties = [
            '{modelName}' => $this->table->modelName,
            '{createRouteName}' => $creatRoute,
            '{htmlColumns}' => $dataColumns['html'],
            '{dataTableColumns}' => $dataColumns['json'],
            '{dataTableDataRouteName}' => $dataRoute,
        ];

        $indexPath = CubePath::make("resources/views/dashboard/{$this->table->viewNaming()}/index.blade.php");

        if ($indexPath->exist()) {
            $indexPath->logAlreadyExist("Generating Index Page For ({$this->table->modelName}) Model");
        }

        $indexPath->ensureDirectoryExists();

        FileUtils::generateFileFromStub(
            $stubProperties,
            $indexPath,
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

        foreach ($this->table->attributes as $attribute) {
            $label = $this->getLabelName($attribute->name);

            if ($attribute->type == ColumnTypeEnum::TEXT->value) {
                continue;
            }

            if ($attribute->type == 'file') {
                $json .= "{\n\t\"data\": '{$attribute->name}',render:function (data) {const filePath = \"{{asset(\"storage/\")}}/\" + data; return `<div class=\"gallery\"><a href=\"\${filePath}\"><img class=\"img-fluid\" src=\"\${filePath}\" alt=\"\"/></a>`;}}, \n";
                $html .= "\n<th>{$label}</th>\n";
                continue;
            }

            $json .= "{\"data\": '{$attribute->name}', searchable: true, orderable: true}, \n";

            $html .= "\n<th>{$label}</th>\n";
        }

        return ['html' => $html, 'json' => $json];
    }
}
