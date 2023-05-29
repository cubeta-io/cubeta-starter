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

    protected $signature = 'cubeta-init';

    protected $description = 'some initials will prepare the files to work with the package';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $this->editExceptionHandler();
        $this->handleActorsExistence();
    }

    /**
     * ask user for his actors and initialize them
     *
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handleActorsExistence(): void
    {
        $hasActors = $this->choice('Does Your Project Has Multi Actors ?', ['No', 'Yes'], 'No') == 'Yes';

        if ($hasActors) {

            $this->installSpatie();

            $actorsNumber = $this->ask('How Many Are They  ?', 2);

            while (empty(trim($actorsNumber))) {
                $this->error('Invalid Input');
                $actorsNumber = $this->ask('How Many Are They  ?', 2);
            }

            for ($i = 0; $i < $actorsNumber; $i++) {

                $this->info("Actor Number : $i");

                $role = $this->ask('What Is The Name Of This Actor ?  eg:admin,customer');

                while (empty(trim($role))) {
                    $this->error('Invalid Input');
                    $role = $this->ask('What Is The Name Of This Actor ?  eg:admin,customer');
                }

                $hasPermission = $this->choice('Does This Actor Has Permissions ? eg : can-edit , can-read , can publish , ....', ['No', 'Yes'], 'No') == 'Yes';

                $permissions = null;
                if ($hasPermission) {
                    $permissionsString = $this->ask("What Are The Permissions For This Actor \n <info>Please Note That You Have To Type Them like This : \n</info> can-edit , can-read , can publish , ....");

                    while (empty(trim($permissionsString))) {
                        $this->error('Invalid Input');
                        $permissionsString = $this->ask("What Are The Permissions For This Actor \n <info>Please Note That You Have To Type Them like This : \n</info> can-edit , can-read , can publish , ....");
                    }

                    $permissions = $this->convertInputStringToArray($permissionsString);
                }

                $this->createRolesEnum($role, $permissions);

                $container = $this->choice('<info>What is the container of the routes of your actors</info>', ['api', 'web', 'both'], 'api');

                if (! file_exists(base_path("routes/$container/$role.php"))) {
                    $this->addAppropriateRouteFile($container, $role);
                    $this->createRoleSeeder();
                    $this->createPermissionSeeder($container);
                    $this->info("$role role created successfully");
                }
            }
        }
    }

    /**
     * initialize Exception Handler
     */
    public function editExceptionHandler(): void
    {
        $this->warn('We have an exception handler for you and it will replace <fg=red>app/Exceptions/handler.php</fg=red> file with a file of the same name');
        $useHandler = $this->choice('<info>Do you want us to do that ? <fg=yellow>(note : the created feature tests depends on our handler)</fg=yellow></info>', ['No', 'Yes'], 'Yes');

        if ($useHandler == 'No') {
            return;
        }

        $handlerStub = file_get_contents(__DIR__.'/stubs/handler.stub');
        $handlerPath = base_path().'/app/Exceptions/Handler.php';
        if (! file_exists($handlerPath)) {
            File::makeDirectory($handlerPath, 077, true, true);
        }
        file_put_contents($handlerPath, $handlerStub);
        $this->formatFile($handlerPath);

        $this->info('Your handler file in <fg=yellow>app/Exceptions/handler.php</fg=yellow> has been initialized');
    }

    /**
     * ask for the need of spatie permissions and install it
     */
    public function installSpatie(): void
    {
        $install = $this->choice('</info>Using multi actors need to install <fg=yellow>spatie/permission</fg=yellow> do you want to install it ? </info>', ['No', 'Yes'], 'No');
        if ($install == 'Yes') {
            $this->info('Please wait until spatie/laravel-permission installed');
            $this->line($this->executeCommandInTheBaseDirectory('composer require spatie/laravel-permission'));
            $spatiePublishCommand = 'php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"';
            $this->line($this->executeCommandInTheBaseDirectory($spatiePublishCommand));
            $this->warn("Don't forgot to run php artisan migrate");
        }
    }
}
