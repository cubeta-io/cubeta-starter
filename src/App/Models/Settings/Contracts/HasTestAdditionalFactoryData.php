<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\TestAdditionalFactoryDataString;

interface HasTestAdditionalFactoryData
{
    public function testAdditionalFactoryData(): TestAdditionalFactoryDataString;
}