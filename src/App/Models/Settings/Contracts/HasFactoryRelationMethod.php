<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\FactoryRelationMethodStringString;

interface HasFactoryRelationMethod
{
    public function factoryRelationMethod(): FactoryRelationMethodStringString;
}