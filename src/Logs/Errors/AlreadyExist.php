<?php

namespace Cubeta\CubetaStarter\Logs\Errors;

use Cubeta\CubetaStarter\Logs\CubeError;

class AlreadyExist extends CubeError
{
    public function __construct(string $filePath, string $happenedWhen)
    {
        parent::__construct(
            message: "[$filePath] Already Exists !",
            happenedWhen: $happenedWhen
        );
    }
}
