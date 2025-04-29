<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumnString;

interface HasMigrationColumn
{
    public function migrationColumn(): MigrationColumnString;
}