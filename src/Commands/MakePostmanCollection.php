<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

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
     * @param $modelName
     * @param $attributes
     * @return void
     * @throws BindingResolutionException
     */
    private function createPostmanCollection($modelName, $attributes): void
    {
        $modelName = modelNaming($modelName);
        $endpoint = '/' . routeUrlNaming($modelName);
        $projectName = config('repository.project_name');
        $collectionDirectory = base_path(config('repository.postman_collection _path'));
        ensureDirectoryExists($collectionDirectory);
        $collectionPath = "$collectionDirectory/$projectName.postman_collection.json";

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

        $crudStub = str_replace(
            ['{modelName}', '{indexRoute}', '{showRoute}', '{storeRoute}', '{updateRoute}', '{deleteRoute}', '{formData}'],
            $stubProperties,
            $crudStub
        );

        if ($files->exists($collectionPath)) {
            $collection = file_get_contents($collectionPath);

            if (Str::contains(preg_replace('/\s+/', '', $collection), trim("\"name\":\"$modelName\","))) {
                $this->error("An endpoint for " . $modelName . "Controller is already exist in the Postman collection");
                return;
            }

            $collection = str_replace('"// add-your-cruds-here"', $crudStub, $collection);
            file_put_contents($collectionPath, $collection);
        } else {
            $collectionStub = file_get_contents(__DIR__ . '/stubs/postman-collection.stub');
            $collectionStub = str_replace(['{projectName}', '// add-your-cruds-here'], [$projectName, $crudStub], $collectionStub);
            file_put_contents($collectionPath, $collectionStub);
        }

        $this->info("Created Postman Collection: $projectName.postman_collection.json ");
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
