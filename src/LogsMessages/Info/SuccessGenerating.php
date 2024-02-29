<?php

namespace Cubeta\CubetaStarter\LogsMessages\Info;

use Cubeta\CubetaStarter\LogsMessages\CubeInfo;

class SuccessGenerating extends CubeInfo
{
    public function __construct(string $fileName, string $filePath, ?string $context = null)
    {
        parent::__construct(
            "$fileName Generated Successfully \n Path : [$filePath] \n"
            , $context
        );
    }
}
