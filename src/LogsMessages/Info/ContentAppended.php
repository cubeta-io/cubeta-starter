<?php

namespace Cubeta\CubetaStarter\LogsMessages\Info;

use Cubeta\CubetaStarter\LogsMessages\Info;

class ContentAppended extends Info
{
    public function __construct(string $content, string $filePath)
    {
        parent::__construct(
            "The Content : {$content} \n \t Has Been Appended Successfully To : \n
                     File : [{$filePath}] \n"
        );
    }
}
