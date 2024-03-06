<?php

namespace Cubeta\CubetaStarter\Commands\Installers;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class InstallWeb extends BaseCommand
{
    protected $description = 'Add Package Files For Web Based Usage';

    protected $signature = 'cubeta:install-web {--force}';

    public function handle(): void
    {
        $override = $this->option('force') ?? false;
        $gen = new GeneratorFactory("install-web");
        $gen->make($override);
        $this->handleCommandLogsAndErrors();
    }
}
