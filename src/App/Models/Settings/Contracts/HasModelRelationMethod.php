<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\ModelRelationString;

interface HasModelRelationMethod
{
    public function modelRelationMethod(): ModelRelationString;
}