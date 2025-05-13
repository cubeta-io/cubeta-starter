<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Javascript;

use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Javascript\DataTableColumnString;

interface HasDatatableColumnString
{
    public function dataTableColumnString(): DataTableColumnString;
}