<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Tests;

use Cubeta\CubetaStarter\StringValues\Strings\Tests\TestAdditionalFactoryDataString;

interface HasTestAdditionalFactoryData
{
    public function testAdditionalFactoryData(): TestAdditionalFactoryDataString;
}