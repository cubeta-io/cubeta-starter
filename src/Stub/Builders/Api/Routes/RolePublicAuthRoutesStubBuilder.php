<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Api\Routes;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Stub\Contracts\PhpFileStubBuilder;

class RolePublicAuthRoutesStubBuilder extends PhpFileStubBuilder
{
    private string $version;
    private string $role;
    private string $controllerName;
    private string $registerRoute;
    private string $loginRoute;
    private string $requestResetRoute;
    private string $validatePasswordResetRoute;
    private string $resetPasswordRoute;

    public function version(string $version): static
    {
        $this->version = $version;
        return $this;
    }

    public function role(string $role): static
    {
        $this->role = $role;
        $this->controllerName = Naming::model($role);
        return $this;
    }

    public function controllerName(string $controllerName): static
    {
        $this->controllerName = $controllerName;
        return $this;
    }

    public function registerRoute(string $registerRoute): static
    {
        $this->registerRoute = $registerRoute;
        return $this;
    }

    public function loginRoute(string $loginRoute): static
    {
        $this->loginRoute = $loginRoute;
        return $this;
    }

    public function requestResetRoute(string $requestResetRoute): static
    {
        $this->requestResetRoute = $requestResetRoute;
        return $this;
    }

    public function validatePasswordResetRoute(string $validatePasswordResetRoute): static
    {
        $this->validatePasswordResetRoute = $validatePasswordResetRoute;
        return $this;
    }

    public function resetPasswordRoute(string $resetPasswordRoute): static
    {
        $this->resetPasswordRoute = $resetPasswordRoute;
        return $this;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Api/Routes/RolePublicAuthRoutes.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return [
            '{{version}}' => $this->version,
            '{{role}}' => $this->role,
            '{{controller_name}}' => $this->controllerName,
            '{{register_route}}' => $this->registerRoute,
            '{{login_route}}' => $this->loginRoute,
            '{{password_reset_request}}' => $this->requestResetRoute,
            '{{validate_password_reset_code}}' => $this->validatePasswordResetRoute,
            '{{password_reset}}' => $this->resetPasswordRoute,
        ];
    }
}