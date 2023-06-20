<?php

namespace Cubeta\CubetaStarter\Commands;

use Illuminate\Console\Command;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

class MakeRepository extends Command
{
    use AssistCommand;

    public $description = 'Create a new repository class';

    public $signature = 'create:repository
        {name : The name of the repository }';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');

        if ( ! $modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createRepository($modelName);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createRepository($modelName): void
    {
        $modelName = modelNaming($modelName);

        $repositoryName = $modelName . 'Repository';
        $modelVar = variableNaming($modelName);

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.repository_namespace'),
            '{modelName}' => $modelName,
            '{modelVar}' => $modelVar,
            '{modelNamespace}' => config('cubeta-starter.model_namespace')
        ];

        $repositoryPath = $this->getRepositoryPath($repositoryName);

        if (file_exists($repositoryPath)) {
            $this->error("{$repositoryName} Already Exists");

            return;
        }

        // create file
        generateFileFromStub(
            $stubProperties,
            $repositoryPath,
            __DIR__ . '/stubs/repository.stub'
        );

        $this->formatFile($repositoryPath);
        $this->info("Created Repository: {$repositoryName}");
    }

    private function getRepositoryPath($repositoryName): string
    {
        $directory = base_path(config('cubeta-starter.repository_path'));
        ensureDirectoryExists($directory);

        return "{$directory}/{$repositoryName}.php";
    }
}
