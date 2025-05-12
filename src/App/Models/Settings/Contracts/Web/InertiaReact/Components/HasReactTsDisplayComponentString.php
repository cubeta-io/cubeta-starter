<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Components;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Components\ReactTsDisplayComponentString;

interface HasReactTsDisplayComponentString
{
    public function displayComponentString(): ReactTsDisplayComponentString;
}