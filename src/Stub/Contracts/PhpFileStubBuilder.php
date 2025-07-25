<?php

namespace Cubeta\CubetaStarter\Stub\Contracts;

use Cubeta\CubetaStarter\Traits\Makable;

abstract class PhpFileStubBuilder extends StubBuilder
{
    use Makable;

    protected function getStubPropertyArray(): array
    {
        return $this->stubProperties;
    }
}