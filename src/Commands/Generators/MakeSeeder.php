<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeSeeder extends BaseCommand
{

    public $description = 'Create a new seeder';

    public $signature = 'create:seeder
        {name? : The name of the model }
        {--force}';

    public function handle(): void
    {
        $modelName =  $this->argument('name') ?? $this->askForModelName("Model");;
        $override = $this->askForOverride();

        $generator = new GeneratorFactory("seeder");
        $generator->make(fileName: $modelName, override: $override);
    }
}
