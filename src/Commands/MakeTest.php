<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;

class MakeTest extends Command
{
    use AssistCommand;

    public $signature = 'create:test
        {name : The name of the model }';

    public $description = 'Create a new feature test';

    /**
     * Handle the command
     *
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');

        $this->createTest($modelName);
    }

    /**
     * @throws BindingResolutionException
     */
    private function createTest($modelName)
    {
        $modelName = ucfirst(Str::singular($modelName));
        $testName = $this->getTestName($modelName);

        $stubProperties = [
            '{modelName}' => $modelName,
        ];

        new CreateFile(
            $stubProperties,
            $this->getTestPath($testName),
            __DIR__ . '/stubs/test.stub'
        );
        $this->line("<info>Created Test:</info> $testName");
    }

    private function getTestName($modelName): string
    {
        return $modelName . 'Test';
    }

    /**
     * @throws BindingResolutionException
     */
    private function getTestPath($testName): string
    {
        $path = $this->appPath() . '/tests/Feature/';

        $this->ensureDirectoryExists($path);

        return $path . "$testName" . '.php';
    }
}
