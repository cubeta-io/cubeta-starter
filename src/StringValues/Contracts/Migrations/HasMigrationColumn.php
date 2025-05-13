<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Migrations;

use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;

interface HasMigrationColumn
{
    public function migrationColumn(): MigrationColumnString;
}