<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Resources;


use Cubeta\CubetaStarter\StringValues\Strings\Resources\ResourcePropertyString;

interface HasResourcePropertyString
{
    public function resourcePropertyString(): ResourcePropertyString;
}