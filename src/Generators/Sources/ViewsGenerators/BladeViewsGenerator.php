<?php

namespace Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators;

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
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeRelation;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\FormLocalSelectorString;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\FormViewStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\IndexViewStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\ShowViewStubBuilder;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;

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

        // Find the column array
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
        // If the column array is not found, try to find an empty array
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
        $hasTranslatableFields = $this->table->hasTranslatableAttribute();

        $indexPath = CubePath::make("resources/views/dashboard/{$this->table->viewNaming()}/index.blade.php");
        $showPath = CubePath::make("resources/views/dashboard/{$viewsName}/show.blade.php");
        $createFormPath = CubePath::make("resources/views/dashboard/{$viewsName}/create.blade.php");
        $updateFormPath = CubePath::make("resources/views/dashboard/{$viewsName}/edit.blade.php");

        $createInputs = $this->getInputsFields();
        $updateInputs = $this->getInputsFields("update");

        // create view
        FormViewStubBuilder::make()
            ->method("POST")
            ->title("Create {$this->table->modelName}")
            ->localizationSelector($hasTranslatableFields ? new FormLocalSelectorString() : "")
            ->submitRoute($routes['store'])
            ->inputs($createInputs)
            ->generate($createFormPath, $this->override);

        // update view
        FormViewStubBuilder::make()
            ->method("PUT")
            ->title("Update {$this->table->modelName}")
            ->localizationSelector($hasTranslatableFields ? new FormLocalSelectorString() : "")
            ->submitRoute($routes['update'])
            ->updateParameters(", \${$modelVariable}->id")
            ->inputs($updateInputs)
            ->type($modelVariable, $this->table->getModelClassString())
            ->generate($updateFormPath, $this->override);

        // show view
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

        // index view
        IndexViewStubBuilder::make()
            ->tableName(ucfirst($this->table->tableNaming()))
            ->createRoute($routes['create'])
            ->dataRoute($routes['data'])
            ->exportRoute($routes['export'])
            ->importRoute($routes['import'])
            ->exampleRoute($routes['import_example'])
            ->modelClassString($this->table->getModelClassString())
            ->htmlColumns(
                $this->table->attributes()
                    ->filter(fn(CubeAttribute $attribute) => !$attribute->isTextable())
                    ->whereInstanceOf(HasHtmlTableHeader::class)
                    ->map(fn(HasHtmlTableHeader $attribute) => $attribute->htmlTableHeader()->__toString())
                    ->implode("\n")
            )->dataTableObjectColumns(
                $this->table->attributes()
                    ->filter(fn(CubeAttribute $attribute) => !$attribute->isTextable())
                    ->whereInstanceOf(HasDatatableColumnString::class)
                    ->map(fn(HasDatatableColumnString $attribute) => $attribute->datatableColumnString()->__toString())
                    ->implode("\n")
            )->generate($indexPath, $this->override);
    }

    private function getInputsFields(string $formType = "store"): string
    {
        return $this->table
                ->attributes()
                ->whereInstanceOf(HasBladeInputComponent::class)
                ->map(fn(HasBladeInputComponent $attr) => $attr->bladeInputComponent($formType, $this->actor)->__toString())
                ->implode("\n") . "\n" . $this->table
                ->relations()
                ->whereInstanceOf(HasBladeInputComponent::class)
                ->filter(fn(CubeRelation $rel) => $rel->getWebControllerPath()->exist()
                    && $rel->relationModel()->getModelPath()->exist()
                    && ClassUtils::isMethodDefined($rel->getWebControllerPath(), "allPaginatedJson")
                )->map(fn(HasBladeInputComponent $attr) => $attr->bladeInputComponent($formType, $this->actor)->__toString())
                ->implode("\n");
    }
}
