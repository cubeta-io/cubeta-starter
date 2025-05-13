<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Models;

use Cubeta\CubetaStarter\StringValues\Strings\Models\CastColumnString;

interface HasModelCastColumn
{
    public function modelCastColumn(): CastColumnString;
}