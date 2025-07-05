<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components;

use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\HtmlTableHeaderString;

interface HasHtmlTableHeader
{
    public function htmlTableHeader(): HtmlTableHeaderString;
}