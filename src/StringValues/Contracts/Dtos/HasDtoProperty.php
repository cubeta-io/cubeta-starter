<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Dtos;

use Cubeta\CubetaStarter\StringValues\Strings\Dtos\DtoPropertyString;

interface HasDtoProperty
{
    public function dtoProperty(): DtoPropertyString;
}
