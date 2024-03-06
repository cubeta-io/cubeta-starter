<?php

namespace Cubeta\CubetaStarter\Logs\Info;

use Cubeta\CubetaStarter\Logs\CubeInfo;

class SuccessGenerating extends CubeInfo
{
    public function __construct(string $fileName, string $filePath, ?string $context = null)
    {
        parent::__construct(
            "($fileName) Generated Successfully \nPath : [$filePath]\n"
            , $context
        );
    }
}
