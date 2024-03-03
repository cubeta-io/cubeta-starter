<?php

namespace Cubeta\CubetaStarter\LogsMessages\Info;

use Cubeta\CubetaStarter\LogsMessages\CubeInfo;

class SuccessMessage extends CubeInfo
{
    public function __construct(string $message)
    {
        parent::__construct("Success : $message");
    }
}
