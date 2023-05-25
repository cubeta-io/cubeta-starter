<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakeSeeder extends Command
{
    use AssistCommand;

    public $signature = 'create:seeder
        {name : The name of the model }';

    public $description = 'Create a new seeder';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');

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
        ];

        $seederPath = $this->getSeederPath($seederName);
        if (file_exists($seederPath)) {
            return;
        }

        new CreateFile(
            $stubProperties,
            $seederPath,
            __DIR__ . '/stubs/seeder.stub'
        );

        $this->formatFile($seederPath);
        $this->line("<info>Created seeder:</info> $seederName");
    }

    private function getSeederName($modelName): string
    {
        return $modelName . 'Seeder';
    }

    private function getSeederPath($seederName): string
    {
        $directory = base_path(config('repository.seeder_path'));
        $this->ensureDirectoryExists($directory);
        return "$directory/$seederName.php";
    }
}
