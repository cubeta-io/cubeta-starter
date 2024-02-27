<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\App\Models\Postman\Postman;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakePostmanCollection extends Command
{
    use AssistCommand;

    public $description = 'Create a new postman collection';

    public $signature = 'create:postman-collection
        {name : The name of the model }
        {attributes? : columns with data types}';

    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes');

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createPostmanCollection($modelName, $attributes);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createPostmanCollection($modelName, $attributes): void
    {
        $modelName = modelNaming($modelName);
        $endpoint = routeUrlNaming($modelName);

        $collection = Postman::make()
            ->getCollection()
            ->newCrud($modelName, $endpoint, $attributes)
            ->save();

        $this->info("Created Postman Collection: {$collection->name}.postman_collection.json ");
    }
}
