<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\Sources\ActorFilesGenerator;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use function Laravel\Prompts\confirm;

class AddActor extends BaseCommand
{
    use RouteBinding;

    protected $description = 'Add New Actor To The Project';
    protected $signature = 'create:actor {--force}';

    public function handle(): void
    {
        ["actor" => $actor, "permissions" => $permissions] = $this->askForActorsAndPermissions();
        $container = $this->askForContainer();

        $authenticated = false;
        if (ContainerType::isApi($container)) {
            $authenticated = confirm("Do You Want To Create Authentication Api Controller For This Actor ?");
        }

        $override = $this->askForOverride();

        $generator = new ActorFilesGenerator($actor, $permissions, $authenticated, $container , override: $override);
        $generator->run();
    }
}
