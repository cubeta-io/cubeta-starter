<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Api\Routes;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\PhpFileStubBuilder;

/**
 * @method self version(string $version)
 * @method self role(string $role)
 * @method self controllerName(string $controllerName)
 * @method self registerRouteName(string $registerRouteName)
 * @method self loginRouteName(string $loginRouteName)
 * @method self passwordResetRequestRouteName(string $passwordResetRequestRouteName)
 * @method self validatePasswordResetCodeRouteName(string $validatePasswordResetCodeRouteName)
 * @method self passwordResetRouteName(string $passwordResetRouteName)
 */
class RolePublicAuthRoutesStubBuilder extends PhpFileStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Api/Routes/RolePublicAuthRoutes.stub');
    }
}