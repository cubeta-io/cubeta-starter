<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Relations;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFactoryRelationMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models\HasModelRelationMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Factories\FactoryRelationMethodStringString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PhpImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Models\ModelRelationString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;

class CubeHasMany extends CubeRelation implements HasModelRelationMethod, HasDocBlockProperty, HasResourcePropertyString, HasFactoryRelationMethod
{
    public function factoryRelationMethod(): FactoryRelationMethodStringString
    {
        return new FactoryRelationMethodStringString(
            $this->modelName,
        );
    }

    public function modelRelationMethod(): ModelRelationString
    {
        return new ModelRelationString(
            $this->modelName,
            RelationsTypeEnum::HasMany,
        );
    }

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            str($this->modelName)->plural()->lower()->toString(),
            "\Illuminate\Support\Collection<$this->modelName>|null",
            imports: new PhpImportString($this->getModelNameSpace())
        );
    }

    public function resourcePropertyString(): ResourcePropertyString
    {
        return new ResourcePropertyString(
            str($this->modelName)->plural()->snake()->lower()->toString(),
            "{$this->getResourceName()}::collection(\$this->whenLoaded('{$this->relationMethodNaming(singular: false)}'))",
            [
                new PhpImportString($this->getResourceNameSpace(false))
            ]
        );
    }
}