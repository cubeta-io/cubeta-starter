<?php

namespace Cubeta\CubetaStarter\Stub\Contracts;

use Cubeta\CubetaStarter\Stub\Contracts\StubBuilder;
use Cubeta\CubetaStarter\Traits\Makable;

abstract class TypescriptFileBuilder extends StubBuilder
{
    use Makable;

    protected array $stubProperties = [
        "{{imports}}" => "",
        "{{functions}}" => ""
    ];

    protected function getStubPropertyArray(): array
    {
        return $this->stubProperties;
    }
}