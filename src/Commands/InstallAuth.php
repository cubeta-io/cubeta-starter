<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Traits\RouteBinding;

class InstallAuth extends BaseCommand
{
    use RouteBinding;

    protected $description = 'initialize authentication tools';

    protected $signature = 'auth:install';

    public function handle(): void
    {
        $gen = new GeneratorFactory("install-auth");
        $gen->make(generatedFor: ContainerType::BOTH, override: true);
        $this->handleCommandLogsAndErrors();
    }
}
