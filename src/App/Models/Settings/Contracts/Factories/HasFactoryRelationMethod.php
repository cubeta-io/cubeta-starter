<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories;

use Cubeta\CubetaStarter\StringValues\Strings\Factories\FactoryRelationMethodStringString;

interface HasFactoryRelationMethod
{
    public function factoryRelationMethod(): FactoryRelationMethodStringString;
}