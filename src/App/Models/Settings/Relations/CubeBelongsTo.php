<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Relations;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models\HasModelRelationMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PhpImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Models\ModelRelationString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;


class CubeBelongsTo extends CubeRelation implements HasModelRelationMethod, HasDocBlockProperty, HasResourcePropertyString
{
    public function modelRelationMethod(): ModelRelationString
    {
        return new ModelRelationString(
            $this->modelName,
            RelationsTypeEnum::BelongsTo,
        );
    }

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            str($this->modelName)->singular()->lower()->toString(),
            "$this->modelName|null",
            imports: new PhpImportString($this->getModelNameSpace())
        );
    }

    public function resourcePropertyString(): ResourcePropertyString
    {
        return new ResourcePropertyString(
            str($this->modelName)->singular()->snake()->lower()->toString(),
            "{$this->getResourceName()}::make(\$this->whenLoaded('{$this->relationMethodNaming()}'))",
            [
                new PhpImportString($this->getResourceNameSpace(false))
            ]
        );
    }
}