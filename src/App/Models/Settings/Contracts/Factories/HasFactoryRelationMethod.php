<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Factories\FactoryRelationMethodStringString;

interface HasFactoryRelationMethod
{
    public function factoryRelationMethod(): FactoryRelationMethodStringString;
}