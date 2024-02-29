<?php

namespace Cubeta\CubetaStarter\LogsMessages\Info;

use Cubeta\CubetaStarter\LogsMessages\Info;

class SuccessMessage extends Info
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
