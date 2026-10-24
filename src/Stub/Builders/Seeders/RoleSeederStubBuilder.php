<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Seeders;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self modelNamespace(string $modelNamespace)
 */
class RoleSeederStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Seeders/RoleSeeder.stub');
    }

    public function init(): void
    {
        $this->namespace(config('cubeta-starter.seeder_namespace'))
            ->modelNamespace(config('cubeta-starter.model_namespace'));
    }
}
