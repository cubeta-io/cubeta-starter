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

    readonly public string $columnName;
    readonly public string $actorName;
    readonly public string $returnColName;

    private CubeTable $relatedModel;

    public function __construct(string $columnName, string $actorName)
    {
        $this->columnName = $columnName;
        $this->actorName = $actorName;

        $relatedModelName = Naming::model(str_replace('_id', '', $this->columnName));
        $this->relatedModel = Settings::make()->getTable($relatedModelName) ?? CubeTable::create($relatedModelName);
        $this->returnColName = $this->relatedModel->relationMethodNaming() . '.' . $this->relatedModel->titleable()->name;
    }

    public function __toString(): string
    {
        $showRouteName = $this->relatedModel->showRoute($this->actorName , ContainerType::WEB)->name;
        $relationColumnName = $this->relatedModel->relationMethodNaming() . '.' . $this->relatedModel->titleable()->name;
        $columnCalling = "\$row->" . $this->relatedModel->relationMethodNaming() . "->" . $this->relatedModel->titleable()->name;

        return "->editColumn('$relationColumnName' , function (\$row) {
                    return \"<a href='\" . route('{$showRouteName}', \$row->{$this->columnName}) . \"'>{{$columnCalling}}</a>\";
                })";
    }
}