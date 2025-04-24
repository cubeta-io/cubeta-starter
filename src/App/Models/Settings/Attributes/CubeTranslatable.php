<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumn;

class CubeTranslatable extends CubeStringable implements HasFakeMethod, HasMigrationColumn, HasDocBlockProperty
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

    public function docBlockProperty(): DocBlockProperty
    {
        return new DocBlockProperty(
            $this->name,
            "\\App\\Serializers\\Translatable"
        );
    }
}