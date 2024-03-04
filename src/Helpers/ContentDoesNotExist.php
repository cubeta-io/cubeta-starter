<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\Logs\CubeError;

class ContentDoesNotExist extends CubeError
{
    public function __construct(string $content, string $filePath, ?string $happenedWhen = null)
    {
        parent::__construct(
            message: "The Content : $content \n
                      Does Not Exist In [$filePath] \n",
            happenedWhen: $happenedWhen
        );
    }
}
