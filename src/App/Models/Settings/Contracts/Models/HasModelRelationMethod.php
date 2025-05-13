<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models;

use Cubeta\CubetaStarter\StringValues\Strings\Models\ModelRelationString;

interface HasModelRelationMethod
{
    public function modelRelationMethod(): ModelRelationString;
}