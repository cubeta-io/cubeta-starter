<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Resources;


use Cubeta\CubetaStarter\StringValues\Strings\Resources\ResourcePropertyString;

interface HasResourcePropertyString
{
    public function resourcePropertyString(): ResourcePropertyString;
}