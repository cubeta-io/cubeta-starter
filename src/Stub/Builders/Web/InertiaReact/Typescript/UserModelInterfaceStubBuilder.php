<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Typescript;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\TypescriptFileBuilder;

class UserModelInterfaceStubBuilder extends TypescriptFileBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/InertiaReact/Typescript/User.stub');
    }
}