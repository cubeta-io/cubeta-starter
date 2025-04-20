<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumn;

class CubeJson extends CubeStringable implements HasFakeMethod, HasMigrationColumn
{
    public function fakeMethod(): FakeMethodString
    {
        $isUnique = $this->unique ? "->unique()" : "";
        return new FakeMethodString(
            $this->name,
            "json_encode([fake(){$isUnique}->word() => fake(){$isUnique}->word()])"
        );
    }

    public function migrationColumn(): MigrationColumn
    {
        return new MigrationColumn(
            $this->columnNaming(),
            "json",
            $this->nullable,
            $this->unique
        );
    }
}