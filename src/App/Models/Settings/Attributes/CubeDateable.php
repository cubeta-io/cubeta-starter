<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumn;

class CubeDateable extends CubeAttribute implements HasFakeMethod, HasMigrationColumn, HasDocBlockProperty
{
    public function fakeMethod(): FakeMethodString
    {
        return new FakeMethodString(
            $this->name,
            "fake()->dateTime()"
        );
    }

    public function migrationColumn(): MigrationColumn
    {
        return new MigrationColumn(
            $this->columnNaming(),
            "dateTime",
            $this->nullable,
            $this->unique
        );
    }

    public function docBlockProperty(): DocBlockProperty
    {
        return new DocBlockProperty(
            $this->name,
            "Carbon",
            "property",
            new ImportString("Carbon\Carbon")
        );
    }
}