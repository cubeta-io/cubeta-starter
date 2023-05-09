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
        $modelName = $this->modelNaming($modelName);
        $seederName = $this->getSeederName($modelName);

        $stubProperties = [
            '{modelName}' => $modelName,
        ];

        $seederPath = base_path() . '/database/seeders/' . $seederName . '.php';
        if (file_exists($seederPath)) {
            return;
        }

        new CreateFile(
            $stubProperties,
            $this->getSeederPath($seederName),
            __DIR__ . '/stubs/seeder.stub'
        );
        $this->line("<info>Created seeder:</info> $seederName");
    }

    private function getSeederName($modelName): string
    {
        return $modelName . 'Seeder';
    }

    private function getSeederPath($seederName): string
    {
        return $this->appDatabasePath() . '/seeders' .
            "/$seederName" . '.php';
    }
}
