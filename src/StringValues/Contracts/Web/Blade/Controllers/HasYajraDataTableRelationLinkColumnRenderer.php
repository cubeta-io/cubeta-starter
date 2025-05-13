<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Controllers;

use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Controllers\YajraDataTableRelationLinkColumnRenderer;

interface HasYajraDataTableRelationLinkColumnRenderer
{
    public function yajraDataTableAdditionalColumnRenderer(string $actor): YajraDataTableRelationLinkColumnRenderer;
}