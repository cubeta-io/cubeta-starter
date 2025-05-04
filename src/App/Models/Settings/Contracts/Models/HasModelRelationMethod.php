<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Models\ModelRelationString;

interface HasModelRelationMethod
{
    public function modelRelationMethod(): ModelRelationString;
}