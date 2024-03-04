<?php

namespace Cubeta\CubetaStarter\Logs\Info;

use Cubeta\CubetaStarter\Logs\CubeInfo;

class ContentAppended extends CubeInfo
{
    public function __construct(string $content, string $filePath)
    {
        parent::__construct(
            "The Content :\n{$content}\nHas Been Appended Successfully To : [{$filePath}] \n"
        );
    }
}
