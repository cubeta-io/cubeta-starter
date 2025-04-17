<?php

namespace Cubeta\CubetaStarter\Stub\Contracts;

use Cubeta\CubetaStarter\Traits\Makable;

abstract class PhpFileStubBuilder extends StubBuilder
{
    use Makable;

    protected array $imports = [];

    public function import(string|array $import): static
    {
        if (is_array($import)) {
            $this->imports = array_merge($import, $this->imports);
        } else {
            $this->imports[] = $import;
        }

        return $this;
    }
}