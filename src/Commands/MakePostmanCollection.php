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
     * Handle the command
     *
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
    private function createPostmanCollection($modelName, $attributes)
    {
        $modelName = Str::singular(ucfirst($modelName));
        $routeName = 'api.' . $modelName;
        $projetName = env('APP_NAME');
        $collectionPath = base_path() . "/$projetName.postman_collection.json";

        $files = app()->make(Filesystem::class);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{indexRoute}' => $routeName . '.index',
            '{showRoute}' => $routeName . '.show',
            '{storeRoute}' => $routeName . '.store',
            '{updateRoute}' => $routeName . '.update',
            '{deleteRoute}' => $routeName . '.delete',
            '{formData}' => $this->generateBodyData($attributes)
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
            $collectionStub = str_replace(['{projetcName}', '// add-your-cruds-here'], [$projetName, $crudStub], $collectionStub);
            file_put_contents($collectionPath, $collectionStub);
        }

        $this->line("<info>Created Postman Collection:</info> $projetName.postman_collection.json ");
    }

    /**
     * @param $attributes
     * @return string
     */
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
