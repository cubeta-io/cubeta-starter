<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Migrations;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Migrations\MigrationColumnString;

interface HasMigrationColumn
{
    public function migrationColumn(): MigrationColumnString;
}