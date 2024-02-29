<?php

namespace Cubeta\CubetaStarter\LogsMessages\Errors;

use Cubeta\CubetaStarter\LogsMessages\Error;

class AlreadyExist extends Error
{
    public function __construct(string $filePath, string $happenedWhen)
    {
        parent::__construct(
            message: "$filePath Already Exists !",
            happenedWhen: $happenedWhen
        );
    }
}
