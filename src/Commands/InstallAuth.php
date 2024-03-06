<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Traits\RouteBinding;

class InstallAuth extends BaseCommand
{
    use RouteBinding;

    protected $description = 'initialize authentication tools';

    protected $signature = 'install:cubeta-auth';

    public function handle(): void
    {
        $container = $this->askForContainer();
        $override = $this->askForOverride();
        $gen = new GeneratorFactory("install-auth");
        $gen->make(generatedFor: $container, override: $override);
        $this->handleCommandLogsAndErrors();
    }
}
