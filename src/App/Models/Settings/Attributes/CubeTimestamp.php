<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumn;

class CubeTimestamp extends CubeDateable implements HasFakeMethod, HasMigrationColumn
{
    public function migrationColumn(): MigrationColumn
    {
        return new MigrationColumn(
            $this->columnNaming(),
            "timestamp",
            $this->nullable,
            $this->unique
        );
    }
}