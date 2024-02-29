<?php

namespace Cubeta\CubetaStarter\LogsMessages\Warnings;

use Cubeta\CubetaStarter\LogsMessages\Warning;

class ContentAlreadyExist extends Warning
{
    public function __construct(string $content, string $filePath = null, ?string $context = null)
    {
        parent::__construct(
            "The Content : \n \t $content \n Already Exists In : [$filePath]",
            $context
        );
    }
}
