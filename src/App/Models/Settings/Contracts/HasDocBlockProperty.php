<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockProperty;

interface HasDocBlockProperty
{
    public function docBlockProperty(): DocBlockProperty;
}