<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Models\CastColumnString;

interface HasModelCastColumn
{
    public function modelCastColumn(): CastColumnString;
}