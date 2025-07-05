<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components;

use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsInputComponentString;

interface HasReactTsInputString
{
    /**
     * @param string                $formType
     * @param "store"|"update"|null $actor
     * @return ReactTsInputComponentString
     */
    public function inputComponent(string $formType = "store", ?string $actor = null): ReactTsInputComponentString;
}