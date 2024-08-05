<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;

class RepositoryGenerator extends AbstractGenerator
{
    public static string $key = 'repository';

    public function run(bool $override = false): void
    {
        $repositoryPath = $this->table->getRepositoryPath();

        if ($repositoryPath->exist()) {
            $repositoryPath->logAlreadyExist("Generating Repository Class For ({$this->table->modelName}) Model");
            return;
        }

        $repositoryPath->ensureDirectoryExists();

        $stubProperties = [
            '{namespace}'      => $this->table->getRepositoryNameSpace(false, true),
            '{modelName}'      => $this->table->modelName,
            '{modelVar}'       => $this->table->variableNaming(),
            '{modelNamespace}' => $this->table->getModelNameSpace(false),
        ];

        $this->generateFileFromStub($stubProperties, $repositoryPath->fullPath);

        $repositoryPath->format();
    }

    protected function stubsPath(): string
    {
        return CubePath::stubPath('repository.stub');
    }
}
