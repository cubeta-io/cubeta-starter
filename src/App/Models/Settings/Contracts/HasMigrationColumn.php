<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumn;

interface HasMigrationColumn
{
    public function migrationColumn(): MigrationColumn;
}