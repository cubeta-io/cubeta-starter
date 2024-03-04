<?php

namespace Cubeta\CubetaStarter\Logs\Errors;

use Cubeta\CubetaStarter\Logs\CubeError;

class NotFound extends CubeError
{
    public function __construct(string $targeted, ?string $happenedWhen = null)
    {
        parent::__construct(
            message: "[$targeted] Does Not Exists",
            happenedWhen: $happenedWhen
        );
    }
}
