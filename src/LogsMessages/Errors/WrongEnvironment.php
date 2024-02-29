<?php

namespace Cubeta\CubetaStarter\LogsMessages\Errors;

use Cubeta\CubetaStarter\LogsMessages\CubeError;

class WrongEnvironment extends CubeError
{
    public function __construct(string $happenedWhen)
    {
        parent::__construct(
            message: "You Are Trying To Use cubeta-starter In A Production Environment \n",
            happenedWhen: $happenedWhen
        );
    }
}
