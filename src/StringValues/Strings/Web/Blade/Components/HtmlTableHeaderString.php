<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components;

class HtmlTableHeaderString
{
    private string $header;

    /**
     * @param string $header
     */
    public function __construct(string $header)
    {
        $this->header = $header;
    }


    public function __toString(): string
    {
        return "<th>$this->header</th>";
    }
}