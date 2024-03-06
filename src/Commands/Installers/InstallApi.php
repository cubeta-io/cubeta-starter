<?php

namespace Cubeta\CubetaStarter\Commands\Installers;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class InstallApi extends BaseCommand
{
    protected $description = 'Add Package Files For Api Based Usage';

    protected $signature = 'cubeta:install-api {--force}';

    public function handle(): void
    {
        $override = $this->option('force') ?? false;
        $gen = new GeneratorFactory("install-api");
        $gen->make($override);
        $this->handleCommandLogsAndErrors();
    }
}
