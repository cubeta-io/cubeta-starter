<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Migrations;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\PhpFileStubBuilder;

class ModelHasRoleMigrationStubBuilder extends PhpFileStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('/Migrations/ModelHasRoleMigration.stub');
    }
}