<?php

namespace Cubeta\CubetaStarter\LogsMessages\Errors;

use Cubeta\CubetaStarter\LogsMessages\CubeError;

class FailedAppendContent extends CubeError
{
    public function __construct(string $content, string $filePath = null, ?string $context = null)
    {
        parent::__construct(
            message: "Failed To Append The Following Content : \n" .
            "$content \n" .
            "To : [$filePath]",
            happenedWhen: $context
        );
    }
}
