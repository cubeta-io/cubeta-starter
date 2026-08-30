<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Dtos;

use Cubeta\CubetaStarter\StringValues\Strings\Dtos\DtoCastString;

interface HasDtoCast
{
    public function dtoCast(): DtoCastString;
}
