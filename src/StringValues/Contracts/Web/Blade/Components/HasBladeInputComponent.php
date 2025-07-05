<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components;

use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\InputComponentString;

interface HasBladeInputComponent
{
    /**
     * @param "store"|"update" $formType
     * @param string|null      $actor
     * @return InputComponentString
     */
    public function bladeInputComponent(string $formType = "store", ?string $actor = null): InputComponentString;
}