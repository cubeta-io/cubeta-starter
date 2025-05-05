<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\InputComponentString;

interface HasBladeInputComponent
{
    /**
     * @param "store"|"update" $formType
     * @param string|null      $actor
     * @return InputComponentString
     */
    public function bladeInputComponent(string $formType = "store", ?string $actor = null): InputComponentString;
}