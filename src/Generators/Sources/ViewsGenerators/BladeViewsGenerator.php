<?php

namespace Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\FormLocalSelectorString;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\Sources\WebControllers\BladeControllerGenerator;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\CubeWarning;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\FormViewStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\ShowViewStubBuilder;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;
use JetBrains\PhpStorm\ArrayShape;

class BladeViewsGenerator extends BladeControllerGenerator
{
    use WebGeneratorHelper;
    use RouteBinding;

    public static string $key = "views";

    /**
     * @param CubePath $filePath
     * @param string   $newColumn
     * @param string   $htmlColName
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
        if (!Settings::make()->getFrontendType() == FrontendTypeEnum::BLADE) {
            CubeLog::add(new CubeError("Install web tools by running [php artisan cubeta:install web && php artisan cubeta:install web-packages] then try again", happenedWhen: "Generating a {$this->table->modelName} blade views"));
            return;
        }

        $routes = $this->getRouteNames($this->table, ContainerType::WEB, $this->actor);

        $viewsName = $this->table->viewNaming();
        $modelVariable = $this->table->variableNaming();
        $createFormPath = CubePath::make("resources/views/dashboard/{$viewsName}/create.blade.php");

        $hasTranslatableFields = $this->table->hasTranslatableAttribute();

        $createInputs = $this->table
            ->attributes()
            ->filter(fn(CubeAttribute $attribute) => $attribute instanceof HasBladeInputComponent)
            ->map(fn(HasBladeInputComponent $attr) => $attr->bladeInputComponent("store", $this->actor)->__toString())
            ->implode("\n");

        FormViewStubBuilder::make()
            ->method("POST")
            ->title("Create {$this->table->modelName}")
            ->localizationSelector($hasTranslatableFields ? new FormLocalSelectorString() : "")
            ->submitRoute($routes['store'])
            ->updateParameters("")
            ->inputs($createInputs)
            ->generate($createFormPath, $this->override);

        $updateFormPath = CubePath::make("resources/views/dashboard/{$viewsName}/edit.blade.php");
        $updateInputs = $this->table
            ->attributes()
            ->filter(fn(CubeAttribute $attribute) => $attribute instanceof HasBladeInputComponent)
            ->filter(function (CubeAttribute $attribute) {
                if ($attribute->isKey()) {
                    $model = CubeTable::create($attribute->modelNaming());
                    if (!$model->getModelPath()->exist() || !$model->getWebControllerPath()->exist()) {
                        return false;
                    }
                    if (!ClassUtils::isMethodDefined($model->getWebControllerPath(), 'allPaginatedJson')) {
                        return false;
                    }
                }
                return true;
            })->map(fn(HasBladeInputComponent $attr) => $attr->bladeInputComponent("update", $this->actor)->__toString())
            ->implode("\n");

        FormViewStubBuilder::make()
            ->method("PUT")
            ->title("Update {$this->table->modelName}")
            ->localizationSelector($hasTranslatableFields ? new FormLocalSelectorString() : "")
            ->submitRoute($routes['update'])
            ->updateParameters(", \${$modelVariable}->id")
            ->inputs($updateInputs)
            ->type($modelVariable, $this->table->getModelClassString())
            ->generate($updateFormPath, $this->override);

        $viewsName = $this->table->viewNaming();
        $showPath = CubePath::make("resources/views/dashboard/{$viewsName}/show.blade.php");
        ShowViewStubBuilder::make()
            ->modelClassString($this->table->getModelClassString())
            ->modelVariable($this->table->variableNaming())
            ->modelName($this->table->modelNaming())
            ->titleable($this->table->titleable()->name)
            ->editRoute($routes['edit'])
            ->components(
                $this->table->attributes()
                    ->map(fn(CubeAttribute $attribute) => $attribute->bladeDisplayComponent()?->__toString())
                    ->implode("\n")
            )->generate($showPath, $this->override);

        $this->generateIndexView($routes['create'], $routes['data'], $override);
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
     * @param string $editRoute
     * @param bool   $override
     * @return void
     */
    public function generateShowView(string $editRoute, bool $override = false): void
    {

    }

    /**
     * @param string $creatRoute
     * @param string $dataRoute
     * @param bool   $override
     * @return void
     */
    public function generateIndexView(string $creatRoute, string $dataRoute, bool $override = false): void
    {
        $dataColumns = $this->generateDataTableColumns();
        $routes = $this->getRouteNames($this->table, ContainerType::WEB, $this->actor);

        $stubProperties = [
            '{modelName}' => $this->table->modelName,
            '{createRouteName}' => $creatRoute,
            '{htmlColumns}' => $dataColumns['html'],
            '{dataTableColumns}' => $dataColumns['json'],
            '{dataTableDataRouteName}' => $dataRoute,
            '{exportRoute}' => $routes['export'],
            '{importRoute}' => $routes['import'],
            '{exampleRoute}' => $routes['import_example'],
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
            CubePath::stubPath('views/index.stub')
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
                $json .= "{\n\t\"data\": '{$attribute->name}',render:function (data) {const filePath = data?.url; return `<div class=\"gallery\"><a href=\"\${filePath}\"><img class=\"img-fluid\" style=\"max-width: 80px\" src=\"\${filePath}\" alt=\"\"/></a>`;}}, \n";
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

            if ($attribute->isTranslatable()) {
                $render = ",render: (data) => translate(data)";
            } else {
                $render = "";
            }

            $json .= "{\"data\": '{$attribute->name}', searchable: true, orderable: true $render}, \n";

            $html .= "\n<th>{$label}</th>\n";
        }

        return ['html' => $html, 'json' => $json];
    }
}
