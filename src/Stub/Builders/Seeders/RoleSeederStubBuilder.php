<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Seeders;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class RoleSeederStubBuilder extends ClassStubBuilder
{
    private string $modelsNamespace;

    public function init(): void
    {
        $this->modelsNamespace = config('cubeta-starter.model_namespace');
        $this->namespace = config('cubeta-starter.seeder_namespace');
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Seeders/RoleSeeder.stub');
    }

    public function modelNamespace(string $modelsNamespace): static
    {
        $this->modelsNamespace = $modelsNamespace;
        return $this;
    }

    protected function getStubPropertyArray(): array
    {
        return [
            'namespace' => $this->namespace,
            'model_namespace' => $this->modelsNamespace,
        ];
    }
}