<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript;

use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;

interface HasDataTableColumnObjectString
{
    public function datatableColumnObject(string $actor): DataTableColumnObjectString;
}