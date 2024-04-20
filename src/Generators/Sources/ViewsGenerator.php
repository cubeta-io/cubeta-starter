<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\CubeWarning;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use JetBrains\PhpStorm\ArrayShape;

class ViewsGenerator extends WebControllerGenerator
{
    const FORM_STUB = __DIR__ . '/../../stubs/views/form.stub';
    const SHOW_STUB = __DIR__ . '/../../stubs/views/show.stub';
    const INDEX_STUB = __DIR__ . '/../../stubs/views/index.stub';

    public static string $key = "views";

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

        if (FileUtils::contentExistInFile($filePath, $newColumn) || FileUtils::contentExistInFile($filePath, $htmlColName)) {
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
                    $newColumns = FileUtils::prependLastMatch('/\s*}\s*,\s*\{/', "},{" . $newColumn, $existingColumns);
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

    public function run(bool $override = false): void
    {
        $routes = $this->getRoutesNames($this->table, $this->actor);

        $this->generateCreateOrUpdateForm(storeRoute: $routes['store'], override: $override);
        $this->generateCreateOrUpdateForm(updateRoute: $routes['update'], override: $override);

        $this->generateShowView($routes['edit'], $override);
        $this->generateIndexView($routes['create'], $routes['data'], $override);
    }

    /**
     * @param string|null $storeRoute
     * @param string|null $updateRoute
     * @param bool $override
     * @return void
     */
    public function generateCreateOrUpdateForm(?string $storeRoute = null, ?string $updateRoute = null, bool $override = false): void
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

        $this->table->attributes()->each(function (CubeAttribute $attribute) use ($updateInput, $modelVariable, &$inputs) {
            $label = $this->getLabelName($attribute->name);
            $isRequired = 'required';
            if ($attribute->nullable || $updateInput) {
                $isRequired = '';
            }
            $value = $updateInput
                ? ($attribute->isTranslatable()
                    ? ":value=\"\${$modelVariable}->getRawOriginal('{$attribute->name}')\""
                    : ":value=\"\${$modelVariable}->{$attribute->name}\"")
                : null;
            $checked = $updateInput
                ? ":checked=\"\${$modelVariable}->{$attribute->name}\""
                : 'checked';
            switch ($attribute->type) {
                case ColumnTypeEnum::KEY->value:
                {
                    $model = CubeTable::create(Naming::model(str_replace('_id', '', $attribute->name)));
                    $relatedTable = Settings::make()->getTable($model->modelName);
                    $value = str_replace('_id', '', $value);
                    $select2Route = $this->getRouteName($model, ContainerType::WEB, $this->actor) . '.allPaginatedJson';
                    if (!$model->getModelPath()->exist() || !$model->getWebControllerPath()->exist()) break;
                    if (!ClassUtils::isMethodDefined($model->getWebControllerPath(), 'allPaginatedJson')) break;
                    $inputs .= "<x-select2 label=\"{$label}\" name=\"{$attribute->name}\" api=\"{{route('{$select2Route}')}}\" option-value=\"id\" option-inner-text=\"{$relatedTable->titleable()->name}\" {$value} {$isRequired}/> \n";
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
        });
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
     * @param CubeAttribute $attribute
     * @return string
     */
    private function getInputTagType(CubeAttribute $attribute): string
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
        elseif ($attribute->isFile()) return "file";
        else return "text";
    }

    /**
     * @param string $editRoute
     * @param bool $override
     * @return void
     */
    public function generateShowView(string $editRoute, bool $override = false): void
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

        $this->generateFileFromStub($stubProperties,
            $showPath->fullPath,
            $override,
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
            } elseif ($attribute->isFile()) {
                $components .= "<x-image-preview :imagePath=\"\${$modelVariable}->{$attribute->name}\"/> \n";
            } elseif ($attribute->isTranslatable()) {
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
     * @param bool $override
     * @return void
     */
    public function generateIndexView(string $creatRoute, string $dataRoute, bool $override = false): void
    {
        $dataColumns = $this->generateDataTableColumns();
        $routes = $this->getRoutesNames($this->table);

        $stubProperties = [
            '{modelName}' => $this->table->modelName,
            '{createRouteName}' => $creatRoute,
            '{htmlColumns}' => $dataColumns['html'],
            '{dataTableColumns}' => $dataColumns['json'],
            '{dataTableDataRouteName}' => $dataRoute,
            '{exportRoute}' => $routes['export'],
            '{importRoute}' => $routes['import'],
            '{exampleRoute}' => $routes['example'],
            '{modelClassName}' => $this->table->getModelClassString(),
        ];

        $indexPath = CubePath::make("resources/views/dashboard/{$this->table->viewNaming()}/index.blade.php");

        if ($indexPath->exist()) {
            $indexPath->logAlreadyExist("Generating Index Page For ({$this->table->modelName}) Model");
        }

        $indexPath->ensureDirectoryExists();

        $this->generateFileFromStub(
            $stubProperties,
            $indexPath->fullPath,
            $override,
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

            if ($attribute->isFile()) {
                $json .= "{\n\t\"data\": '{$attribute->name}',render:function (data) {const filePath = \"{{asset(\"storage/\")}}/\" + data; return `<div class=\"gallery\"><a href=\"\${filePath}\"><img class=\"img-fluid\" src=\"\${filePath}\" alt=\"\"/></a>`;}}, \n";
                $html .= "\n<th>{$label}</th>\n";
                continue;
            }

            if ($attribute->isKey()) {
                $relatedModelName = str_replace('_id', "", $attribute->name);
                $relatedTable = Settings::make()->getTable($relatedModelName);
                $usedName = $relatedTable
                    ? $relatedTable->relationMethodNaming() . "." . $relatedTable->titleable()->name
                    : $relatedModelName . ".id";
                $json .= "{\"data\": '{$usedName}', searchable: true, orderable: true}, \n";
            }

            $json .= "{\"data\": '{$attribute->name}', searchable: true, orderable: true}, \n";

            $html .= "\n<th>{$label}</th>\n";
        }

        return ['html' => $html, 'json' => $json];
    }
}
