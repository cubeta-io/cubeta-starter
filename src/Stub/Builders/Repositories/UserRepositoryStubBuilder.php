<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Repositories;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self modelNamespace(string $namespace)
 * @method self repositoryNamespace(string $namespace)
 */
class UserRepositoryStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Repositories/UserRepository.stub');
    }
}