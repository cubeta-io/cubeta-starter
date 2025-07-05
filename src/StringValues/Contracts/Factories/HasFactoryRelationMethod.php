<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Factories;

use Cubeta\CubetaStarter\StringValues\Strings\Factories\FactoryRelationMethodStringString;

interface HasFactoryRelationMethod
{
    public function factoryRelationMethod(): FactoryRelationMethodStringString;
}