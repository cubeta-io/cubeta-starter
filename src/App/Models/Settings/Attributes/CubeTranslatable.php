<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumn;

class CubeTranslatable extends CubeStringable implements HasFakeMethod, HasMigrationColumn
{
    public function fakeMethod(): FakeMethodString
    {
        $method = $this->guessStringMethod();
        return new FakeMethodString(
            $this->name,
            "Translatable::fake('$method')",
            new ImportString("App\\Serializers\\Translatable")
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