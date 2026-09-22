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
    protected $signature = 'create:actor
        {actor? : The name of the actor}
        {permissions? : comma separated list of permissions, e.g. can-read,can-edit}
        {container? : web, api or both}
        {--authenticated : create an authentication api controller for this actor}
        {--force}';

    public function handle(): void
    {
        $actorArgument = $this->argument('actor');

        if ($actorArgument) {
            $actor = $actorArgument;
            $permissions = $this->resolveList($this->argument('permissions'));
            $permissions = count($permissions) ? $permissions : null;
        } else {
            ["actor" => $actor, "permissions" => $permissions] = $this->askForActorsAndPermissions();
        }

        $container = $this->argument('container') ?? $this->askForContainer();

        $authenticated = (bool) $this->option('authenticated');
        if (!$this->option('authenticated') && ContainerType::isApi($container) && $this->input->isInteractive()) {
            $authenticated = confirm("Do You Want To Create Authentication Api Controller For This Actor ?");
        }

        $override = $this->askForOverride();

        $generator = new ActorFilesGenerator($actor, $permissions, $authenticated, $container , override: $override);
        $generator->run();
    }
}
