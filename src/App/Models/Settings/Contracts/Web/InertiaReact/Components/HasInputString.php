<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Components;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Components\InputComponentString;

interface HasInputString
{
    /**
     * @param string                $formType
     * @param "store"|"update"|null $actor
     * @return InputComponentString
     */
    public function inputComponent(string $formType = "store", ?string $actor = null): InputComponentString;
}