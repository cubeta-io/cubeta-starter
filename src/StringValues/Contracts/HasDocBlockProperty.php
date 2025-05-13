<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts;

use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;

interface HasDocBlockProperty
{
    public function docBlockProperty(): DocBlockPropertyString;
}