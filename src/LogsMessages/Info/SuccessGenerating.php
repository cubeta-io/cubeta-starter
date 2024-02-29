<?php

namespace Cubeta\CubetaStarter\LogsMessages\Info;

use Cubeta\CubetaStarter\LogsMessages\Info;

class SuccessGenerating extends Info
{
    public function __construct(string $fileName, string $filePath, ?string $context = null)
    {
        parent::__construct(
            "$fileName Generated Successfully \n Path : [$filePath] \n"
            , $context
        );
    }
}
