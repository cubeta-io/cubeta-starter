<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Repositories;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class BaseRepositoryStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Repositories/BaseRepository.stub');
    }
}