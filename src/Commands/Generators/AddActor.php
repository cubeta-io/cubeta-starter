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

    protected $description = 'Add a new actor (role) to the project, with its permissions and route bindings';
    protected $signature = 'create:actor
        {actor? : the name of the actor, e.g. admin, customer, ... }
        {permissions? : comma separated list of permissions, e.g. "can-read,can-edit" (leave empty for none) }
        {container? : api, web or both }
        {--authenticated : also generate an authentication api controller for this actor }
        {--force : overwrite existing files instead of skipping/prompting }';

    public function getHelp(): string
    {
        return <<<HELP
          Registers a new actor (role) via spatie/laravel-permission - the RolesPermissionEnum
          entry, its permissions, gates/policies and route model bindings. Requires the
          "permissions" package to be installed first via "php artisan cubeta:install permissions".

          Examples:
            php artisan create:actor admin
            php artisan create:actor admin "can-read,can-edit" api --authenticated --force --no-interaction
          HELP;
    }

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
