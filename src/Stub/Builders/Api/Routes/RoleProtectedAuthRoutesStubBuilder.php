<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Api\Routes;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\PhpFileStubBuilder;

/**
 * @method self version(string $version)
 * @method self role(string $role)
 * @method self controllerName(string $controllerName)
 * @method self refreshRouteName(string $refreshRouteName)
 * @method self logoutRouteName(string $logoutRouteName)
 * @method self updateUserRouteName(string $updateUserRouteName)
 * @method self userDetailsRouteName(string $userDetailsRouteName)
 */
class RoleProtectedAuthRoutesStubBuilder extends PhpFileStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Api/Routes/RoleProtectedAuthRoutes.stub');
    }
}