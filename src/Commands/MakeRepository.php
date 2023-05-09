<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeRepository extends Command
{
    use AssistCommand;

    public $signature = 'create:repository
        {name : The name of the repository }';

    public $description = 'Create a new repository class';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        $this->createRepository($name);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createRepository($modelName): void
    {
        $namespace = $this->getNameSpace();
        $modelName = $this->modelNaming($modelName);

        $repositoryName = $modelName . 'Repository';
        $modelVar = Str::singular(lcfirst($modelName));

        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelVar}' => $modelVar,
        ];

        $repositoryPath = base_path() . '/app/Repositories/' . $repositoryName . '.php';
        if (file_exists($repositoryPath)) {
            return;
        }

        // check folder exist
        $folder = str_replace('\\', '/', $namespace);
        if (!file_exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }

        // create file
        new CreateFile(
            $stubProperties,
            $this->getRepositoryPath($repositoryName),
            __DIR__ . '/stubs/repository.stub'
        );

        $this->line("<info>Created Repository:</info> $repositoryName");
    }

    private function getNameSpace(): string
    {
        return config('repository.repository_namespace');
    }

    private function getRepositoryPath($repositoryName): string
    {
        return $this->appPath() . '/' .
            config('repository.repository_directory') .
            "/$repositoryName" . '.php';
    }
}
