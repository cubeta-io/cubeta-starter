<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Javascript;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Javascript\DataTableColumnString;

interface HasDatatableColumnString
{
    public function dataTableColumnString(): DataTableColumnString;
}