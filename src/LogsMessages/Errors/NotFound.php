<?php

namespace Cubeta\CubetaStarter\LogsMessages\Errors;

use Cubeta\CubetaStarter\LogsMessages\CubeError;

class NotFound extends CubeError
{
    public function __construct(string $targeted, ?string $happenedWhen = null)
    {
        parent::__construct(
            message: "[$targeted] Does Not Exists",
            happenedWhen: $happenedWhen);
    }
}
