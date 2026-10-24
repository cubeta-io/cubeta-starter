<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components;

use JetBrains\PhpStorm\ExpectedValues;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsInputComponentString;

interface HasReactTsInputString
{
    /**
     * @param "store"|"update" $formType
     */
    public function inputComponent(#[ExpectedValues(values: ['store', 'update'])] string $formType = 'store', ?string $actor = null): ReactTsInputComponentString;
}
