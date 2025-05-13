<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models;

use Cubeta\CubetaStarter\StringValues\Strings\Models\CastColumnString;

interface HasModelCastColumn
{
    public function modelCastColumn(): CastColumnString;
}