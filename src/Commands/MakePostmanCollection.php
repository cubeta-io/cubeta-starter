<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;

class MakePostmanCollection extends Command
{
    use AssistCommand;

    public $signature = 'create:postman-collection
        {name : The name of the model }
        {attributes : columns with data types}?';

    public $description = 'Create a new postman collection';

    /**
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes');

        $this->createPostmanCollection($modelName, $attributes);
    }

    /**
     * @throws BindingResolutionException
     */
    private function createPostmanCollection($modelName, $attributes): void
    {
        $modelName = $this->modelNaming($modelName);
        $endpoint = '/' . $this->routeNaming($modelName);
        $projectName = env('APP_NAME');
        $collectionPath = base_path() . "/$projectName.postman_collection.json";

        $files = app()->make(Filesystem::class);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{indexRoute}' => $endpoint,
            '{showRoute}' => $endpoint . '/1',
            '{storeRoute}' => $endpoint,
            '{updateRoute}' => $endpoint . '/1',
            '{deleteRoute}' => $endpoint . '/1',
            '{formData}' => $this->generateBodyData($attributes),
        ];

        $crudStub = file_get_contents(__DIR__ . '/stubs/postman-crud.stub');

        $crudStub = str_replace(['{modelName}', '{indexRoute}', '{showRoute}', '{storeRoute}', '{updateRoute}', '{deleteRoute}', '{formData}'],
            $stubProperties,
            $crudStub
        );

        if ($files->exists($collectionPath)) {
            $collection = file_get_contents($collectionPath);
            $collection = str_replace('"// add-your-cruds-here"', $crudStub, $collection);
            file_put_contents($collectionPath, $collection);
        } else {
            $collectionStub = file_get_contents(__DIR__ . '/stubs/postman-collection.stub');
            $collectionStub = str_replace(['{projectName}', '// add-your-cruds-here'], [$projectName, $crudStub], $collectionStub);
            file_put_contents($collectionPath, $collectionStub);
        }

        $this->line("<info>Created Postman Collection:</info> $projectName.postman_collection.json ");
    }

    public function generateBodyData($attributes): string
    {
        $fields = '';
        $attributesCount = count($attributes);
        foreach ($attributes as $attribute => $type) {
            $separator = ($attributesCount > 1) ? ', ' : '';
            $fields .= sprintf(
                '{ "key": "%s", "type": "%s" }%s',
                $attribute, $type == 'file' ? 'file' : 'text', $separator
            );
            $attributesCount--;
        }

        return $fields;
    }
}
