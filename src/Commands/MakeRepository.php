<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeRepository extends Command
{
    use AssistCommand;

    public $signature = 'create:repository
        {name : The name of the repository }';

    public $description = 'Create a new repository class';

    /**
     * Handle the command
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        $modelName = ucfirst(Str::singular($name));

        $this->createRepository($modelName);
    }

    /**
     * Create repository
     */
    private function createRepository($modelName): void
    {
        $namespace = $this->getNameSpace();

        $repositoryName = $modelName.'Repository';
        $modelVar = Str::singular(lcfirst($modelName));

        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelVar}' => $modelVar,
        ];

        // check folder exist
        $folder = str_replace('\\', '/', $namespace);
        if (! file_exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }

        // create file
        new CreateFile(
            $stubProperties,
            $this->getRepositoryPath($repositoryName),
            __DIR__.'/stubs/repository.stub'
        );

        if(! file_exists($this->appPath().'/app/providers/RepositoryServiceProvider.php')){
            // create file
            new CreateFile(
                [] ,
                $this->getRepositoryPath($repositoryName),
                __DIR__.'/stubs/repository.stub'
            );
        }

        $this->line("<info>Created Repository:</info> $repositoryName");
    }

    /**
     * Get repository path
     */
    private function getRepositoryPath($repositoryName): string
    {
        return $this->appPath().'/'.
            config('repository.repository_directory').
            "/$repositoryName".'.php';
    }

    /**
     * get namespace
     */
    private function getNameSpace(): string
    {
        return config('repository.repository_namespace');
    }
}
