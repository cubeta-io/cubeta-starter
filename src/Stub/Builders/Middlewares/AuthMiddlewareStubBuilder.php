<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Middlewares;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self webLoginPageRoute(string $routeName)
 */
class AuthMiddlewareStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('/Middlewares/Authenticate.stub');
    }
}