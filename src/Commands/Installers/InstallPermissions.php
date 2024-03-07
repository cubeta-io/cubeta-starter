<?php

namespace Cubeta\CubetaStarter\Commands\Installers;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Generators\Installers\PermissionsInstaller;

class InstallPermissions extends BaseCommand
{
    protected $signature = 'cubeta:install-permissions {--force}';
    protected $description = "this command will initialize your project with the required classes to handle multi actor project";

    public function handle(): void
    {
        $override = $this->option('force') ?? false;

        $gen = new GeneratorFactory(PermissionsInstaller::$key);
        $gen->make(override: $override);
        $this->handleCommandLogsAndErrors();
    }
}
