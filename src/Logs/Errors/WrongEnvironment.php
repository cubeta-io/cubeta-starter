<?php

namespace Cubeta\CubetaStarter\Logs\Errors;

use Cubeta\CubetaStarter\Logs\CubeError;

class WrongEnvironment extends CubeError
{
    public function __construct(string $happenedWhen)
    {
        parent::__construct(
            message: "You Are Trying To Use cubeta-starter In A Production Environment",
            happenedWhen: $happenedWhen
        );
    }
}
