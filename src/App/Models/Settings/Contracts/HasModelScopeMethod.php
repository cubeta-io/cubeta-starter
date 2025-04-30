<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\ModelScopeMethodString;

interface HasModelScopeMethod
{
    public function modelScopeMethod(): ModelScopeMethodString;
}