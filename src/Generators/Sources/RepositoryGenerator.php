<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Stub\Builders\Repositories\RepositoryStubBuilder;

class RepositoryGenerator extends AbstractGenerator
{
    public static string $key = 'repository';

    public function run(bool $override = false): void
    {
        $repositoryPath = $this->table->getRepositoryPath();

        RepositoryStubBuilder::make()
            ->repositoriesNamespace($this->table->getRepositoryNameSpace(false, true))
            ->modelNamespace($this->table->getModelNameSpace(false))
            ->modelName($this->table->modelName)
            ->generate($repositoryPath, $this->override);
    }
}
