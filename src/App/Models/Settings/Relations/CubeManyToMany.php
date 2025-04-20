<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Relations;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFactoryRelationMethod;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FactoryRelationMethod;

class CubeManyToMany extends CubeRelation implements HasFactoryRelationMethod
{
    public function factoryRelationMethod(): FactoryRelationMethod
    {
        return new FactoryRelationMethod(
            $this->modelName
        );
    }
}