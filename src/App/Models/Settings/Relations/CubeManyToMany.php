<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Relations;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFactoryRelationMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models\HasModelRelationMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Factories\FactoryRelationMethodStringString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Models\ModelRelationString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;

class CubeManyToMany extends CubeRelation implements HasFactoryRelationMethod, HasModelRelationMethod, HasDocBlockProperty, HasResourcePropertyString
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
            RelationsTypeEnum::ManyToMany,
        );
    }

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            str($this->modelName)->plural()->lower()->toString(),
            "Collection<$this->modelName>|null",
            imports: [
                new ImportString($this->getModelNameSpace()),
                new ImportString("\Illuminate\Support\Collection")
            ]
        );
    }

    public function resourcePropertyString(): ResourcePropertyString
    {
        return new ResourcePropertyString(
            str($this->modelName)->plural()->snake()->lower()->toString(),
            "{$this->getResourceName()}::collection(\$this->whenLoaded('{$this->relationMethodNaming(singular: false)}'))",
            [
                new ImportString($this->getResourceNameSpace(false))
            ]
        );
    }
}