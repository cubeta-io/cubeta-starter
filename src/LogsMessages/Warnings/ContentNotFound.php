<?php

namespace Cubeta\CubetaStarter\LogsMessages\Warnings;

use Cubeta\CubetaStarter\LogsMessages\CubeWarning;

class ContentNotFound extends CubeWarning
{
    /**
     * @param string $content
     * @param string $filePath
     * @param string|null $context
     */
    public function __construct(string $content, string $filePath, ?string $context = null)
    {
        parent::__construct("Content : $content \n Couldn't Be Found In [$filePath]", $context);
    }
}
