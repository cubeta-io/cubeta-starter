<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Console\Command;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

class MakeTest extends Command
{
    use AssistCommand;
    use RouteBinding;

    public $description = 'Create a new feature test';

    public $signature = 'create:test
        {name : The name of the model }
        {attributes? : model attributes}
        {actor? : The actor of the endpoint }';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $actor = $this->argument('actor');
        $attributes = $this->argument('attributes');

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createTest($modelName, $actor, $attributes);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createTest(string $modelName, string $actor = null, array $attributes = []): void
    {
        $modelName = modelNaming($modelName);
        $testName = $modelName . 'Test';
        $baseRouteName = $this->getRouteName($modelName, 'api', $actor) . '.';

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.test_namespace'),
            '{modelName}' => $modelName,
            '{{actor}}' => $actor,
            '{baseRouteName}' => $baseRouteName,
            '{modelNamespace}' => config('cubeta-starter.model_namespace'),
            '{resourceNamespace}' => config('cubeta-starter.resource_namespace'),
            '{additionalFactoryData}' => $this->getAdditionalFactoryData($attributes)
        ];

        $testPath = $this->getTestPath($testName);
        if (file_exists($testPath)) {
            $this->error("{$testName} Already Exists");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $testPath,
            __DIR__ . '/stubs/test.stub'
        );

        $this->formatFile($testPath);
        $this->info("<info>Created Test:</info> {$testName}");
    }

    private function getTestPath($testName): string
    {
        $directory = base_path(config('cubeta-starter.test_path'));

        ensureDirectoryExists($directory);

        return $directory . "/{$testName}" . '.php';
    }

    /**
     * @param array $attributes
     * @return string
     */
    private function getAdditionalFactoryData(array $attributes = []): string
    {
        $data = '';
        foreach ($attributes as $attribute => $type) {
            if ($type == 'file') {
                $data .= "'{$attribute}' => \Illuminate\Http\UploadedFile::fake()->image('image.jpg'),\n";
            } elseif ($type == 'dateTime') {
                $data .= "'{$attribute}' => now()->format('Y-m-d H:i:s'), \n";
            }
        }

        return $data;
    }

}
