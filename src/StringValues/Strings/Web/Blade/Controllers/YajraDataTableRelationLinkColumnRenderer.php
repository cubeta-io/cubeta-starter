<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Controllers;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\Traits\RouteBinding;

class YajraDataTableRelationLinkColumnRenderer
{
    use RouteBinding;

    public string $columnName;
    private string $actorName;

    public function __construct(string $columnName, string $actorName)
    {
        $this->columnName = $columnName;
        $this->actorName = $actorName;
    }

    public function __toString(): string
    {
        $relatedModelName = Naming::model(str_replace('_id', '', $this->columnName));
        $relatedModel = Settings::make()->getTable($relatedModelName);
        if (!$relatedModel) {
            $relatedModel = CubeTable::create($relatedModelName);
        }
        $showRouteName = $this->getRouteNames($relatedModel, ContainerType::WEB, $this->actorName)['show'];
        $relationColumnName = $relatedModel->relationMethodNaming() . '.' . $relatedModel->titleable()->name;
        $columnCalling = "\$row->" . $relatedModel->relationMethodNaming() . "->" . $relatedModel->titleable()->name;

        return "->editColumn('$relationColumnName' , function (\$row) {
                    return \"<a href='\" . route('{$showRouteName}', \$row->{$this->columnName}) . \"'>{$columnCalling}</a>\"
                })";
    }
}