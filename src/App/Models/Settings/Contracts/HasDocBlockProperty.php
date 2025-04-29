<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockPropertyString;

interface HasDocBlockProperty
{
    public function docBlockProperty(): DocBlockPropertyString;
}