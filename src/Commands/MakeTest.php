<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakeTest extends Command
{
    use AssistCommand;

    public $signature = 'create:test
        {name : The name of the model }
        {actor? : The actor of the endpoint } ?';

    public $description = 'Create a new feature test';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $actor = $this->argument('actor');

        $this->createTest($modelName, $actor);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createTest($modelName, $actor): void
    {
        $modelName = $this->modelNaming($modelName);
        $testName = $this->getTestName($modelName);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{{actor}}' => $actor,
        ];

        $testPath = $this->getTestPath($testName);
        if (file_exists($testPath)) {
            return;
        }

        new CreateFile(
            $stubProperties,
            $testPath,
            __DIR__ . '/stubs/test.stub'
        );

        $this->formatfile($testPath);
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
