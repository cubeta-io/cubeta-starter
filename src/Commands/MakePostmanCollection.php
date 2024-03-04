<?php

namespace Cubeta\CubetaStarter\Commands;

use Illuminate\Console\Command;

class MakePostmanCollection extends Command
{
    public $description = 'Create a new postman collection';

    public $signature = 'create:postman-collection
        {name : The name of the model }
        {attributes? : columns with data types}';

    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes');
    }
}
