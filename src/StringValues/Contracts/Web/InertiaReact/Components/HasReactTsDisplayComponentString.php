<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components;

use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsDisplayComponentString;

interface HasReactTsDisplayComponentString
{
    public function displayComponentString(): ReactTsDisplayComponentString;
}