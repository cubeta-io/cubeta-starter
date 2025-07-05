<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components;

use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\DisplayComponentString;

interface HasBladeDisplayComponent
{
    public function bladeDisplayComponent(): DisplayComponentString;
}