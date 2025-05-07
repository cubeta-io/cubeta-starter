<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\HtmlTableHeaderString;

interface HasHtmlTableHeader
{
    public function htmlTableHeader(): HtmlTableHeaderString;
}