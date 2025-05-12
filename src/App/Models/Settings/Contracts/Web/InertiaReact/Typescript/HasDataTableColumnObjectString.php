<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Typescript;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;

interface HasDataTableColumnObjectString
{
    public function datatableColumnObject(string $actor): DataTableColumnObjectString;
}