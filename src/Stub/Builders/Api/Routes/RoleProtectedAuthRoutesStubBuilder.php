<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Api\Routes;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Stub\Contracts\PhpFileStubBuilder;

class RoleProtectedAuthRoutesStubBuilder extends PhpFileStubBuilder
{
    private string $version;
    private string $role;
    private string $controllerName;
    private string $refreshRouteName;
    private string $logoutRouteName;
    private string $updateUserRouteName;
    private string $userDetailRouteName;

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

    public function refreshRouteName(string $refreshRouteName): static
    {
        $this->refreshRouteName = $refreshRouteName;
        return $this;
    }

    public function logoutRouteName(string $logoutRouteName): static
    {
        $this->logoutRouteName = $logoutRouteName;
        return $this;
    }

    public function updateUserRouteName(string $updateUserRouteName): static
    {
        $this->updateUserRouteName = $updateUserRouteName;
        return $this;
    }

    public function userDetailRouteName(string $userDetailRouteName): static
    {
        $this->userDetailRouteName = $userDetailRouteName;
        return $this;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Api/Routes/RoleProtectedAuthRoutes.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return [
            '{{version}}' => $this->version,
            '{{role}}' => $this->role,
            '{{controller_name}}' => $this->controllerName,
            '{{refresh_route}}' => $this->refreshRouteName,
            '{{logout_route}}' => $this->logoutRouteName,
            '{{update_user_details}}' => $this->updateUserRouteName,
            '{{user_details_route}}' => $this->userDetailRouteName,
        ];
    }
}