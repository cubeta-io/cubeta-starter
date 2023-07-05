<?php

namespace Cubeta\CubetaStarter\Commands;

use Illuminate\Console\Command;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

class MakeSeeder extends Command
{
    use AssistCommand;

    public $description = 'Create a new seeder';

    public $signature = 'create:seeder
        {name : The name of the model }';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');

        if (! $modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createSeeder($modelName);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createSeeder($modelName): void
    {
        $modelName = modelNaming($modelName);
        $seederName = $this->getSeederName($modelName);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelNamespace}' => config('cubeta-starter.model_namespace')
        ];

        $seederPath = $this->getSeederPath($seederName);
        if (file_exists($seederPath)) {
            $this->error("{$seederName} Already Exists");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $seederPath,
            __DIR__ . '/stubs/seeder.stub'
        );

        $this->formatFile($seederPath);
        $this->info("Created seeder: {$seederName}");
    }

    private function getSeederName($modelName): string
    {
        return $modelName . 'Seeder';
    }

    private function getSeederPath($seederName): string
    {
        $directory = base_path(config('cubeta-starter.seeder_path'));
        ensureDirectoryExists($directory);

        return "{$directory}/{$seederName}.php";
    }
}
