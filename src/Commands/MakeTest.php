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
     * @return void
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
     * @param $modelName
     * @param $actor
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createTest($modelName, $actor): void
    {
        $modelName = modelNaming($modelName);
        $testName = $modelName."Test";

        $stubProperties = [
            "{namespace}" => config('repository.test_namespace'),
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

        $this->formatFile($testPath);
        $this->line("<info>Created Test:</info> $testName");
    }

    /**
     * @param $testName
     * @return string
     */
    private function getTestPath($testName): string
    {
        $directory = base_path(config('repository.test_path'));

        $this->ensureDirectoryExists($directory);

        return $directory . "/$testName" . '.php';
    }
}
