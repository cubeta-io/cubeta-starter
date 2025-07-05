<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Migrations;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\PhpFileStubBuilder;

/**
 * @method self pivotTableName(string $pivotTableName)
 * @method self firstModelName(string $firstModelName)
 * @method self secondModelName(string $secondModelName)
 */
class PivotMigrationStubBuilder extends PhpFileStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Migrations/PivotMigration.stub');
    }
}