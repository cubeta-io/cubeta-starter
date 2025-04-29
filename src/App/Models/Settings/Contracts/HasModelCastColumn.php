<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\CastColumnString;

interface HasModelCastColumn
{
    public function modelCastColumn(): CastColumnString;
}