<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Models\ModelScopeMethodString;

interface HasModelScopeMethod
{
    public function modelScopeMethod(): ModelScopeMethodString;
}