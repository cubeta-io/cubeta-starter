<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\Sources\ActorFilesGenerator;
use Cubeta\CubetaStarter\Traits\RouteBinding;

class AddActor extends BaseCommand
{
    use RouteBinding;

    protected $description = 'Add New Actor To The Project';
    protected $signature = 'create:actor';

    public function handle(): void
    {
        ["actor" => $actor, "permissions" => $permissions] = $this->askForActorsAndPermissions();
        $container = $this->askForContainer();

        $authenticated = false;
        if (ContainerType::isApi($container)) {
            $authenticated = $this->confirm("Do You Want To Create Authentication Api Controller For This Actor ?", true);
        }

        $override = $this->askForOverride();

        $generator = new ActorFilesGenerator($actor, $permissions, $authenticated, $container);
        $generator->run($override);

        $this->handleCommandLogsAndErrors();

        $this->warn("Don't Forgot To Run <php artisan cubeta:install permissions> and <php artisan install:cubeta-auth> If You Haven't Run Them");
    }
}
