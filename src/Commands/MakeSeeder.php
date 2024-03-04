<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeSeeder extends BaseCommand
{

    public $description = 'Create a new seeder';

    public $signature = 'create:seeder
        {name : The name of the model }';

    public function handle(): void
    {
        $modelName = $this->argument('name');

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $generator = new GeneratorFactory("seeder");
        $generator->make(fileName: $modelName);
        $this->handleCommandLogsAndErrors();
    }
}
