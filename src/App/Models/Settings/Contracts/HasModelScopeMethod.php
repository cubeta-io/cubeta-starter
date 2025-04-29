<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

interface HasModelScopeMethod
{
    public function modelScopeMethod(): ModelScopeMethodString;
}