<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\RolePermissionTrait;
use Cubeta\CubetaStarter\Traits\RouteFileTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

class InitialProject extends Command
{
    use AssistCommand, RouteFileTrait, RolePermissionTrait;

    protected $signature = 'cubeta-init {useGui?} {installSpatie?} {rolesPermissionsArray?}';

    protected $description = 'Prepare the necessary files to work with the package';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $useGui = $this->argument('useGui') ?? false;
        $installSpatie = $this->argument('installSpatie') ?? false;
        $rolesPermissionsArray = $this->argument('rolesPermissionsArray') ?? false;

        if ($useGui) {
            if ($installSpatie) {
                $this->installSpatie(true);
            }
            if ($rolesPermissionsArray) {
                $this->handleRolesPermissionsArray($rolesPermissionsArray);
            }

            return;
        }

        $this->handleActorsExistenceAsQuestionsInput();
    }

    /**
     * Handle the actors and initialize them based on user input
     *
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handleActorsExistenceAsQuestionsInput(): void
    {
        $hasActors = $this->choice('Does your project have multiple actors?', ['No', 'Yes'], 'No') == 'Yes';

        if ($hasActors) {
            $this->installSpatie();

            $actorsNumber = $this->ask('How many actors are there?', 2);

            while (!is_numeric($actorsNumber) || $actorsNumber < 0) {
                $this->error('Invalid input');
                $actorsNumber = $this->ask('How many actors are there?', 2);
            }

            for ($i = 0; $i < $actorsNumber; $i++) {
                $this->info("Actor Number: $i");

                $role = $this->ask('What is the name of this actor? (e.g., admin, customer)');

                while (empty(trim($role))) {
                    $this->error('Invalid input');
                    $role = $this->ask('What is the name of this actor? (e.g., admin, customer)');
                }

                $hasPermission = $this->choice('Does this actor have permissions? (e.g., can-edit, can-read, can-publish)', ['No', 'Yes'], 'No') == 'Yes';

                $permissions = null;
                if ($hasPermission) {
                    $permissionsString = $this->ask("What are the permissions for this actor? (e.g., can-edit, can-read, can-publish)");

                    while (empty(trim($permissionsString))) {
                        $this->error('Invalid input');
                        $permissionsString = $this->ask("What are the permissions for this actor? (e.g., can-edit, can-read, can-publish)");
                    }

                    $permissions = $this->convertInputStringToArray($permissionsString);
                }

                $this->createRolesEnum($role, $permissions);

                $container = 'api';

                if (!file_exists(base_path("routes/$container/$role.php"))) {
                    $this->addAppropriateRouteFile($container, $role);
                    $this->createRoleSeeder();
                    $this->createPermissionSeeder($container);
                    $this->info("$role role created successfully");
                }
            }
        }
    }


    /**
     * Ask about the need for Spatie permissions and install it
     */
    public function installSpatie(bool $skipQuestions = false): void
    {
        $spatiePublishCommand = 'php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"';

        // this mean that the use using the web ui
        if ($skipQuestions) {
            $this->line($this->executeCommandInTheBaseDirectory('composer require spatie/laravel-permission'));
            $this->line($this->executeCommandInTheBaseDirectory($spatiePublishCommand));
            return;
        }

        $install = $this->choice('Using multiple actors requires installing "spatie/permission". Do you want to install it?', ['No', 'Yes'], 'No');

        if ($install == 'Yes') {
            $this->info('Please wait while spatie/laravel-permission is being installed');
            $this->line($this->executeCommandInTheBaseDirectory('composer require spatie/laravel-permission'));
            $this->line($this->executeCommandInTheBaseDirectory($spatiePublishCommand));
            $this->warn("Don't forget to run 'php artisan migrate'");
        }
    }

    /**
     * Handle the roles and permissions passed as arguments
     *
     * @param array $rolesPermissions
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function handleRolesPermissionsArray(array $rolesPermissions): void
    {
        foreach ($rolesPermissions as $role => $permissions) {
            $this->createRolesEnum($role, $permissions);

            $container = 'api';

            if (!file_exists(base_path("routes/$container/$role.php"))) {
                $this->addAppropriateRouteFile($container, $role);
                $this->createRoleSeeder();
                $this->createPermissionSeeder($container);
                $this->info("$role role created successfully");
            }
        }
    }
}
