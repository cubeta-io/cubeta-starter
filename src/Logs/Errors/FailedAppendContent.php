<?php

namespace Cubeta\CubetaStarter\Logs\Errors;

use Cubeta\CubetaStarter\Logs\CubeError;

class FailedAppendContent extends CubeError
{
    public function __construct(string $content, string $filePath = null, ?string $context = null)
    {
        parent::__construct(
            message: "Failed To Append The Following Content : \n" .
            "```\n{$content}\n```\n" .
            "To : [$filePath]",
            happenedWhen: $context
        );
    }
}
