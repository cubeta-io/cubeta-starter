<?php

namespace Cubeta\CubetaStarter\Logs\Info;

use Cubeta\CubetaStarter\Logs\CubeInfo;

class SuccessMessage extends CubeInfo
{
    public function __construct(string $message)
    {
        parent::__construct("Success : $message");
    }
}
