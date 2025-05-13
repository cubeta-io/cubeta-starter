<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Tests;

use Cubeta\CubetaStarter\StringValues\Strings\Tests\TestAdditionalFactoryDataString;

interface HasTestAdditionalFactoryData
{
    public function testAdditionalFactoryData(): TestAdditionalFactoryDataString;
}