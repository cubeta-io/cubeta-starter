<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\FileHandler;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InitialProject extends Command
{
    use AssistCommand;

    protected $signature = 'cubeta-init';

    protected $description = 'some initials will prepare the files to work with the package';

    protected Filesystem $files;

    protected bool $roleExist = false;

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle()
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
                $this->line('<fg=red>Invalid Input</fg=red>');
                $actorsNumber = $this->ask('How Many Are They  ?', 2);
            }

            for ($i = 0; $i < $actorsNumber; $i++) {

                $this->line("<info>Actor Number : $i</info>");

                $role = $this->ask('What Is The Name Of This Actor ?  eg:admin,customer');

                while (empty(trim($role))) {
                    $this->line('<fg=red>Invalid Input</fg=red>');
                    $role = $this->ask('What Is The Name Of This Actor ?  eg:admin,customer');
                }

                $hasPermission = $this->choice('Does This Actor Has Permissions ? eg : can-edit , can-read , can publish , ....', ['No', 'Yes'], 'No') == 'Yes';

                $permissions = null;
                if ($hasPermission) {
                    $permissionsString = $this->ask("What Are The Permissions For This Actor \n <info>Please Note That You Have To Type Them like This : \n</info> can-edit , can-read , can publish , ....");

                    while (empty(trim($permissionsString))) {
                        $this->line('<fg=red>Invalid Input</fg=red>');
                        $permissionsString = $this->ask("What Are The Permissions For This Actor \n <info>Please Note That You Have To Type Them like This : \n</info> can-edit , can-read , can publish , ....");
                    }

                    $permissions = $this->convertPermissionStringToArray($permissionsString);
                }

                $this->createRolesEnum($role, $permissions);

                if (!$this->roleExist) {
                    $this->addApiFile($role);
                    $this->createRoleSeeder();
                    $this->createPermissionSeeder();
                    $this->line("<info>$role role created successfully</info>");
                }

            }
        }
    }

    public function convertPermissionStringToArray(string $permissions = null): ?array
    {
        if (is_null($permissions)) {
            return null;
        }

        $permissions = preg_replace('/\s+/', '', $permissions);

        return explode(',', $permissions);
    }

    public function createRolesEnum(string $role, array $permissions = null): void
    {
        $enum = file_get_contents(__DIR__ . '/stubs/RolesPermissionEnum-entity.stub');
        $roleEnum = Str::singular(Str::upper($role));
        $roleEnumValue = Str::singular(Str::lower($role));

        if ($permissions) {
            for ($i = 0; $i < count($permissions); $i++) {
                $permissions[$i] = Str::lower($permissions[$i]);
            }
        }

        $placedPermission = $permissions ? json_encode($permissions, JSON_PRETTY_PRINT) : 'null';

        $enum = str_replace(
            ['{enum}', '{roleValue}', '{permissions}'],
            [$roleEnum, $roleEnumValue, $placedPermission],
            $enum);

        $enumDirectory = base_path() . '/app/Enums/';

        $files = new Filesystem();
        $files->makeDirectory($enumDirectory, 0777, true, true);

        if (file_exists($enumDirectory . 'RolesPermissionEnum.php')) {
            $enumFileContent = file_get_contents($enumDirectory . 'RolesPermissionEnum.php');
            if (!str_contains($enumFileContent, $role)) {
                // If the new code does not exist, add it to the end of the class definition
                $pattern = '/}\s*$/';
                $replacement = "$enum}";

                $enumFileContent = preg_replace($pattern, $replacement, $enumFileContent, 1);
                $enumFileContent = str_replace(
                    [
                        '//add-your-roles',
                        '//add-all-your-enums-roles-here',
                        '//add-all-your-enums-here',
                    ],
                    [
                        $enum,
                        'self::' . $roleEnum . "['role'], \n //add-all-your-enums-roles-here \n",
                        'self::' . $roleEnum . ", \n //add-all-your-enums-here \n",
                    ],
                    $enumFileContent);

                // Write the modified contents back to the file
                file_put_contents($enumDirectory . 'RolesPermissionEnum.php', $enumFileContent);
            } else {
                $this->line("<info>The role : $role already exists</info>");
                $this->roleExist = true;
            }
        } else {
            $enumFile = file_get_contents(__DIR__ . '/stubs/RolesPermissionEnum.stub');

            $enumFile = str_replace(
                [
                    '//add-your-roles',
                    '//add-all-your-enums-roles-here',
                    '//add-all-your-enums-here',
                ],
                [
                    $enum,
                    'self::' . $roleEnum . "['role'], \n //add-all-your-enums-roles-here \n",
                    'self::' . $roleEnum . ", \n //add-all-your-enums-here \n",
                ],
                $enumFile);
            file_put_contents($enumDirectory . 'RolesPermissionEnum.php', $enumFile);
        }
        $this->formatFile($enumDirectory . 'RolesPermissionEnum.php');
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function addApiFile($role): void
    {
        $role = Str::singular(Str::lower($role));

        $apiFile = 'api/' . $role . '.php';

        $apiPath = base_path() . '\routes\\' . $apiFile;

        !(File::makeDirectory(dirname($apiPath), 0777, true, true)) ??
        $this->line('<info>Failed To Create Your Route Specified Directory</info>');

        new CreateFile(
            ['{route}' => '//add-your-routes-here'],
            $apiPath,
            __DIR__ . '/stubs/api.stub'
        );

        $this->addApiFileToServiceProvider($apiFile);
    }

    public function addApiFileToServiceProvider(string $apiFilePath): void
    {
        $routeServiceProvider = app_path('Providers/RouteServiceProvider.php');
        $line_to_add = "\t\t Route::middleware('api')\n" .
            "\t\t\t->prefix('api')\n" .
            "\t\t\t->group(base_path('routes/$apiFilePath'));\n";

        // Read the contents of the file
        $file_contents = file_get_contents($routeServiceProvider);

        // Check if the line to add already exists in the file
        if (!str_contains($file_contents, $line_to_add)) {
            // If the line does not exist, add it to the boot() method
            $pattern = '/\$this->routes\(function\s*\(\)\s*{\s*/';
            $replacement = "$0$line_to_add";

            $file_contents = preg_replace($pattern, $replacement, $file_contents, 1);
            // Write the modified contents back to the file
            file_put_contents($routeServiceProvider, $file_contents);
        }

        $this->formatFile($routeServiceProvider);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createRoleSeeder(): void
    {
        new CreateFile(
            ['{namespace}' => config('repository.seeder_namespace')],
            base_path(config('repository.seeder_path').'/RoleSeeder.php') ,
            __DIR__ . '/stubs/PermissionSeeder.stub'
        );
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createPermissionSeeder(): void
    {
        new CreateFile(
            ['{namespace}' => config('repository.seeder_namespace')],
            base_path(config('repository.seeder_path').'/PermissionSeeder.php') ,
            __DIR__ . '/stubs/PermissionSeeder.stub'
        );
    }

    /** initialize handler file
     */
    public function editExceptionHandler(): void
    {
        $this->line("We have an exception handler for you and it will replace <fg=yellow>app/Exceptions/handler.php</fg=yellow> file with a file of the same name");
        $useHandler = $this->choice("<info>Do you want us to do that ? <fg=yellow>(note : the created feature tests depends on our handler)</fg=yellow></info>", ['No', 'Yes'], 'Yes');

        if ($useHandler == 'No') {
            return;
        }

        $handlerStub = file_get_contents(__DIR__ . '/stubs/handler.stub');
        $handlerPath = base_path() . '/app/Exceptions/Handler.php';
        if (!file_exists($handlerPath)) {
            File::makeDirectory($handlerPath, 077, true, true);
        }
        file_put_contents($handlerPath, $handlerStub);
        $this->formatFile($handlerPath);

        $this->line('<info>Your handler file in <fg=red>app/Exceptions/handler.php</fg=red> has been initialized</info>');
    }

    /**
     * @return void
     */
    public function installSpatie(): void
    {
        $install = $this->choice("</info>Using multi actors need to install <fg=red>spatie/permission</fg=red> do you want to install it ? </info>", ['No', 'Yes'], 'No');
        if ($install == 'Yes') {
            $this->line('<info>Please wait until spatie/laravel-permission installed</info>');
            $this->line($this->executeCommandInTheBaseDirectory('composer require spatie/laravel-permission'));
            $spatiePublishCommand = 'php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"';
            $this->line($this->executeCommandInTheBaseDirectory($spatiePublishCommand));
            $this->line("<info>Don't forgot to run <fg=blue>php artisan migrate</fg=blue></info>");
        }
    }


    public function initForWeb()
    {
        $this->executeCommandInTheBaseDirectory('composer require yajra/laravel-datatables');
        $this->executeCommandInTheBaseDirectory('npm i laravel-datatables-vite --save-dev');
    }
}
