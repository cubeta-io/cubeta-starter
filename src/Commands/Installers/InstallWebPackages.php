<?php

namespace Cubeta\CubetaStarter\Commands\Installers;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class InstallWebPackages extends BaseCommand
{
    public $description = 'install required npm packages';

    public $signature = 'cubeta:install-web-packages';

    public function handle(): void
    {
        $gen = new GeneratorFactory("install-web-packages");
        $gen->make();
        $this->handleCommandLogsAndErrors();
    }
}
