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

class CubeFile extends CubeAttribute implements HasFakeMethod, HasMigrationColumn, HasDocBlockProperty
{
    public function fakeMethod(): FakeMethodString
    {
        return new FakeMethodString(
            $this->name,
            "UploadedFile::fake()->image(\"image.png\")",
            new ImportString("Illuminate\\Http\\UploadedFile")
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
            "array{url:string,size:string,extension:string,mime_type:string}"
        );
    }
}