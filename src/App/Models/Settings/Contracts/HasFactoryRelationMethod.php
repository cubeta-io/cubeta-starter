<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\FactoryRelationMethod;

interface HasFactoryRelationMethod
{
    public function factoryRelationMethod(): FactoryRelationMethod;
}