<?php

namespace Cubeta\CubetaStarter\Generators\Sources\WebControllers;

use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators\BladeViewsGenerator;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;
use Illuminate\Support\Facades\Route;
use JetBrains\PhpStorm\ArrayShape;

class BladeControllerGenerator extends AbstractGenerator
{
    use RouteBinding, WebGeneratorHelper;

    public static string $key = 'web-controller';

    protected string $rawColumns = "";

    protected array $additionalRoutes = [];

    public function run(bool $override = false): void
    {
        $modelNameCamelCase = $this->table->variableNaming();
        $idVariable = $this->table->idVariable();
        $controllerPath = $this->table->getWebControllerPath();

        if ($controllerPath->exist()) {
            $controllerPath->logAlreadyExist("Generating Web Controller For  ({$this->table->modelName}) Model");
            return;
        }

        $controllerPath->ensureDirectoryExists();

        $routesNames = $this->getRoutesNames($this->table, $this->actor);
        $views = $this->getViewsNames($this->table, $this->actor);

        $addColumns = $this->getAdditionalColumns();

        $loadedRelations = $this->table->relations()
            ->filter(fn(CubeRelation $rel) => $rel->exists())
            ->map(fn(CubeRelation $rel) => $rel->method())
            ->toJson();

        $stubProperties = [
            '{modelName}'               => $this->table->modelName,
            '{modelNameCamelCase}'      => $modelNameCamelCase,
            '{idVariable}'              => $idVariable,
            '{tableName}'               => $this->table->tableName,
            '{addColumns}'              => $addColumns,
            '{rawColumns}'              => $this->rawColumns ?? '',
            '{indexRoute}'              => $routesNames['index'],
            '{createForm}'              => $views['create'],
            '{indexView}'               => $views['index'],
            '{showView}'                => $views['show'],
            '{editForm}'                => $views['edit'],
            '{namespace}'               => $this->table->getWebControllerNameSpace(false, true),
            '{requestNamespace}'        => $this->table->getRequestNameSpace(false),
            '{modelNamespace}'          => $this->table->getModelNameSpace(false),
            '{serviceNamespace}'        => $this->table->getServiceNamespace(false),
            '{translationOrderQueries}' => $this->generateOrderingQueriesForTranslatableColumns(),
            '{additionalMethods}'       => $this->additionalControllerMethods(),
            '{loadedRelations}'         => $loadedRelations,
            '{baseRouteName}'           => $routesNames['base'],
        ];

        $this->generateFileFromStub($stubProperties, $controllerPath->fullPath);
        $this->addRoute($this->table, $this->actor, ContainerType::WEB, $this->additionalRoutes);
        $controllerPath->format();

        (new BladeViewsGenerator(
            fileName: $this->fileName,
            attributes: $this->attributes,
            nullables: $this->nullables,
            actor: $this->actor
        ))->run();

        $this->addSidebarItem($routesNames['index']);

        CodeSniffer::make()
            ->setModel($this->table)
            ->checkForWebRelations(
                $this->getRouteName($this->table, ContainerType::WEB, $this->actor) . '.allPaginatedJson'
            );
    }

    private function getAdditionalColumns(): string
    {
        $dataColumn = '';
        foreach ($this->table->attributes as $attribute) {
            if ($attribute->isKey()) {
                $relatedModel = CubeTable::create(str_replace('_id', '', $attribute->name));

                if (!$relatedModel->getWebControllerPath()->exist() || !$relatedModel->getModelPath()->exist()) {
                    continue;
                }

                if (ClassUtils::isMethodDefined($relatedModel->getWebControllerPath(), 'show')) {
                    continue;
                }

                $showRouteName = $this->getRoutesNames($relatedModel, $this->actor)['show'];

                if (!Route::has($showRouteName)) {
                    continue;
                }

                $relatedTable = Settings::make()->getTable($relatedModel->modelName);
                $columnName = $relatedTable->relationMethodNaming() . '.' . $relatedTable->titleable()->name;
                $columnCalling = "\$row->" . $relatedTable->relationMethodNaming() . "->" . $relatedTable->titleable()->name;
                $dataColumn .= "
                    ->editColumn('{$columnName}', function (\$row) {
                    //TODO::check on the used show route of the related model key
                        return \"<a href='\" . route('{$showRouteName}', \$row->{$attribute->name}) . \"'>{$columnCalling}</a>\";
                    })";
                $this->rawColumns .= "'{$columnName}' ,";
            } elseif ($attribute->isTranslatable()) {
                $dataColumn .= "\n->editColumn(\"{$attribute->name}\" , fn(\$data) => \$data->{$attribute->name})\n";
            }
        }

        return $dataColumn;
    }

    private function generateOrderingQueriesForTranslatableColumns(): string
    {
        $translatableColumns = $this->getJQueryDataTablesTranslatableColumnsIndexes();
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

    private function getJQueryDataTablesTranslatableColumnsIndexes(): array
    {
        $translatableIndex = 1;
        $translatableColumnsIndexes = [];

        foreach ($this->table->attributes as $attribute) {
            if ($attribute->isTranslatable()) {
                $translatableColumnsIndexes[$attribute->name] = $translatableIndex;
            }
            if ($attribute->type == ColumnTypeEnum::TEXT->value) {
                continue;
            }
            $translatableIndex++;
        }

        return $translatableColumnsIndexes;
    }

    private function additionalControllerMethods(): string
    {
        $methods = '';
        $variableName = $this->table->variableNaming();

        if ($this->table->hasRelationOfType(RelationsTypeEnum::HasMany)) {
            $methods .= "public function allPaginatedJson()\n{\n\t\${$variableName} = \$this->{$variableName}Service->indexWithPagination([], 7);\n\treturn response()->json(\${$variableName} , 200);\n}";
            $this->additionalRoutes[] = 'allPaginatedJson';
        }

        return $methods;
    }

    private function addSidebarItem(string $routeName): void
    {
        $sidebarPath = CubePath::make("resources/views/includes/sidebar.blade.php");
        if (!$sidebarPath->exist()) {
            return;
        }

        $sidebarItem = "\t\t<li class=\"nav-item\">\n\t\t\t<a class=\"nav-link collapsed @if(str_contains(request()->fullUrl() , route('{$routeName}'))) active @endif\" href=\"{{route('{$routeName}')}}\">\n\t\t\t\t<i class=\"bi bi-circle\"></i><span>{$this->table->modelNaming()}</span>\n\t\t\t</a>\n\t\t</li>\n</ul>";

        $sidebar = $sidebarPath->getContent();
        $sidebar = str_replace("</ul>", $sidebarItem, $sidebar);
        $sidebarPath->putContent($sidebar);
    }

    /**
     * @param null $actor
     * @return string[]
     */
    #[ArrayShape(['index' => 'string', 'edit' => 'string', 'create' => 'string', 'show' => 'string'])]
    private function getViewsNames(CubeTable $model, $actor = null): array
    {
        $viewName = $model->viewNaming();
        if (!isset($actor) || $actor == '' || $actor = 'none') {
            return [
                'index'  => 'dashboard.' . $viewName . '.index',
                'edit'   => 'dashboard.' . $viewName . '.edit',
                'create' => 'dashboard.' . $viewName . '.create',
                'show'   => 'dashboard.' . $viewName . '.show',
            ];
        }
        return [
            'index'  => 'dashboard.' . $actor . '.' . $viewName . '.index',
            'edit'   => 'dashboard.' . $actor . '.' . $viewName . '.edit',
            'create' => 'dashboard.' . $actor . '.' . $viewName . '.create',
            'show'   => 'dashboard.' . $actor . '.' . $viewName . '.show',
        ];

    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../../stubs/controller.web.stub';
    }
}
