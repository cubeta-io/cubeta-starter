<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components;

use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsInputComponentString;
use JetBrains\PhpStorm\ExpectedValues;

interface HasReactTsInputString
{
    /**
     * @param "store"|"update" $formType
     * @param string|null $actor
     * @return ReactTsInputComponentString
     */
    public function inputComponent(#[ExpectedValues(values: ['store', 'update'])] string $formType = "store", ?string $actor = null): ReactTsInputComponentString;
}