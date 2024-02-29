<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Throwable;

class RepositoryGenerator extends AbstractGenerator
{
    public static string $key = 'repository';
    public static string $configPath = 'cubeta-starter.repository_path';

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $repositoryPath = $this->table->getRepositoryPath();

        if ($repositoryPath->exist()) {
            $repositoryPath->logAlreadyExist("Generating Repository Class For ({$this->table->modelName}) Model");
        }

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.repository_namespace'),
            '{modelName}' => $this->table->modelName,
            '{modelVar}' => $this->table->variableNaming(),
            '{modelNamespace}' => config('cubeta-starter.model_namespace')
        ];

        $this->generateFileFromStub($stubProperties, $repositoryPath->fullPath);

        $repositoryPath->format();
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/repository.stub';
    }
}
