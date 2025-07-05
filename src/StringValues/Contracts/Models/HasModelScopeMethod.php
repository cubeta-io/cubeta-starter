<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Models;

use Cubeta\CubetaStarter\StringValues\Strings\Models\ModelScopeMethodString;

interface HasModelScopeMethod
{
    public function modelScopeMethod(): ModelScopeMethodString;
}