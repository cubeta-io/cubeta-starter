<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
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

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $hasActors = $this->choice('Does Your Project Has Multi Actors ?', ['No', 'Yes'], 'No') == 'Yes';

        if ($hasActors) {
            $actorsNumber = $this->ask('How Many Are They  ?', 2);

            for ($i = 0; $i < $actorsNumber; $i++) {

                $this->line("<info>Actor Number : $i</info>");

                $role = $this->ask('What Is The Name Of This Actor ?  eg:admin,customer');

                $hasPermission = $this->choice('Does This Actor Has Permissions ? eg : can-edit , can-read , can publish , ....', ['No', 'Yes'], 'No') == 'Yes';

                $permissions = null;
                if ($hasPermission) {
                    $permissionsString = $this->ask("What Are The Permissions For This Actor \n <info>Please Note That You Have To Type Them like This : \n</info> can-edit , can-read , can publish , ....");
                    $permissions = $this->convertPermissionStringToArray($permissionsString);
                }

                $this->createRolesEnum($role, $permissions);
                $this->addApiFile($role);
                $this->createRoleSeeder();
                $this->createPermissionSeeder();
                $this->line("<info>$role role created successfully</info>");
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
        $enum = file_get_contents(__DIR__.'/stubs/RolesPermissionEnum-entity.stub');
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

        $enumDirectory = base_path().'/app/Enums/';

        $files = new Filesystem();
        $files->makeDirectory($enumDirectory, 0777, true, true);

        if (file_exists($enumDirectory.'RolesPermissionEnum.php')) {
            $enumFileContent = file_get_contents($enumDirectory.'RolesPermissionEnum.php');
            if (! str_contains($enumFileContent, $enum)) {
                // If the new code does not exist, add it to the end of the class definition
                $pattern = '/}\s*$/';
                $replacement = "{$enum}}";

                $enumFileContent = preg_replace($pattern, $replacement, $enumFileContent, 1);
                $enumFileContent = str_replace(
                    [
                        '//add-your-roles',
                        '//add-all-your-enums-roles-here',
                        '//add-all-your-enums-here',
                    ],
                    [
                        $enum,
                        'self::'.$roleEnum."['role'], \n //add-all-your-enums-roles-here \n",
                        'self::'.$roleEnum.", \n //add-all-your-enums-here \n",
                    ],
                    $enumFileContent);

                // Write the modified contents back to the file
                file_put_contents($enumDirectory.'RolesPermissionEnum.php', $enumFileContent);
            }
        } else {
            $enumFile = file_get_contents(__DIR__.'/stubs/RolesPermissionEnum.stub');

            $enumFile = str_replace(
                [
                    '//add-your-roles',
                    '//add-all-your-enums-roles-here',
                    '//add-all-your-enums-here',
                ],
                [
                    $enum,
                    'self::'.$roleEnum."['role'], \n //add-all-your-enums-roles-here \n",
                    'self::'.$roleEnum.", \n //add-all-your-enums-here \n",
                ],
                $enumFile);
            file_put_contents($enumDirectory.'RolesPermissionEnum.php', $enumFile);
        }
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function addApiFile($role): void
    {
        $role = Str::singular(Str::lower($role));

        $apiFile = 'api/'.$role.'.php';

        $apiPath = base_path().'\routes\\'.$apiFile;

        ! (File::makeDirectory(dirname($apiPath), 0777, true, true)) ??
        $this->line('<info>Failed To Create Your Route Specified Directory</info>');

        new CreateFile(
            ['{route}' => '//add-your-routes-here'],
            $apiPath,
            __DIR__.'/stubs/api.stub'
        );

        $this->addApiFileToServiceProvider($apiFile);
    }

    public function addApiFileToServiceProvider(string $apiFilePath): void
    {
        $routeServiceProvider = app_path('Providers/RouteServiceProvider.php');
        $line_to_add = "\t\t Route::middleware('api')\n".
            "\t\t\t->prefix('api')\n".
            "\t\t\t->group(base_path('routes/$apiFilePath'));\n";

        // Read the contents of the file
        $file_contents = file_get_contents($routeServiceProvider);

        // Check if the line to add already exists in the file
        if (! str_contains($file_contents, $line_to_add)) {
            // If the line does not exist, add it to the boot() method
            $pattern = '/\$this->routes\(function\s*\(\)\s*{\s*/';
            $replacement = "$0{$line_to_add}";

            $file_contents = preg_replace($pattern, $replacement, $file_contents, 1);
            // Write the modified contents back to the file
            file_put_contents($routeServiceProvider, $file_contents);
        }
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createRoleSeeder(): void
    {
        new CreateFile(
            [],
            database_path('seeders/RoleSeeder.php'),
            __DIR__.'/stubs/RoleSeeder.stub'
        );
    }

    public function createPermissionSeeder(): void
    {
        new CreateFile(
            [],
            database_path('seeders/PermissionSeeder.php'),
            __DIR__.'/stubs/PermissionSeeder.stub'
        );
    }
}
