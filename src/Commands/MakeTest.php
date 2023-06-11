<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakeTest extends Command
{
    use AssistCommand;

    public $signature = 'create:test
        {name : The name of the model }
        {actor? : The actor of the endpoint }';

    public $description = 'Create a new feature test';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $actor = $this->argument('actor');

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createTest($modelName, $actor);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createTest($modelName, $actor): void
    {
        $modelName = modelNaming($modelName);
        $testName = $modelName . 'Test';

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.test_namespace'),
            '{modelName}' => $modelName,
            '{{actor}}' => $actor,
        ];

        $testPath = $this->getTestPath($testName);
        if (file_exists($testPath)) {
            $this->error("$testName Already Exists");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $testPath,
            __DIR__ . '/stubs/test.stub'
        );

        $this->formatFile($testPath);
        $this->info("<info>Created Test:</info> $testName");
    }

    private function getTestPath($testName): string
    {
        $directory = base_path(config('cubeta-starter.test_path'));

        ensureDirectoryExists($directory);

        return $directory . "/$testName" . '.php';
    }
}
