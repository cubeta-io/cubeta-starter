<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Repositories;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self repositoriesNamespace(string $repositoriesNamespace)
 * @method self modelNamespace(string $modelNamespace)
 * @method self modelName(string $modelName)
 */
class RepositoryStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Repositories/Repository.stub');
    }
}