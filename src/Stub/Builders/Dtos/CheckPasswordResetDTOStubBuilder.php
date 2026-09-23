<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Dtos;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class CheckPasswordResetDTOStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Dtos/CheckPasswordResetDTO.stub');
    }
}
