<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Api\Controllers;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;
use Illuminate\Support\Str;

class RoleAuthControllerStubBuilder extends ClassStubBuilder
{
    private string $serviceNamespace;
    private string $role;
    private string $roleEnumName;

    public function serviceNamespace(string $serviceNamespace): static
    {
        $this->serviceNamespace = $serviceNamespace;
        return $this;
    }

    public function role(string $role): static
    {
        $this->role = ucfirst(Str::singular(Str::studly($role)));
        return $this;
    }

    public function roleEnumName(string $roleEnumName): static
    {
        $this->roleEnumName = $roleEnumName;
        return $this;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Api/Controllers/RoleAuthController.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return [
            '{namespace}' => $this->namespace,
            '{service_namespace}' => $this->serviceNamespace,
            '{role}' => $this->role,
            '{role_enum_name}' => $this->roleEnumName,
        ];
    }
}