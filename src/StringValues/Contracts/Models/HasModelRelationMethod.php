<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Models;

use Cubeta\CubetaStarter\StringValues\Strings\Models\ModelRelationString;

interface HasModelRelationMethod
{
    public function modelRelationMethod(): ModelRelationString;
}