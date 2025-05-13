<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;

interface HasDocBlockProperty
{
    public function docBlockProperty(): DocBlockPropertyString;
}