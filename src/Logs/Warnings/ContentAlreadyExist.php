<?php

namespace Cubeta\CubetaStarter\Logs\Warnings;

use Cubeta\CubetaStarter\Logs\CubeWarning;

class ContentAlreadyExist extends CubeWarning
{
    public function __construct(string $content, string $filePath = null, ?string $context = null)
    {
        parent::__construct(
            "The Content :\n\t$content\nAlready Exists In : [$filePath]",
            $context
        );
    }
}
