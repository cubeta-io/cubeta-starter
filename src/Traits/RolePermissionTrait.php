<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

trait RolePermissionTrait
{
    public function createRolesEnum(string $role, array $permissions = null): void
    {
        $enum = file_get_contents(__DIR__.'/../Commands/stubs/RolesPermissionEnum-entity.stub');
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
            if (! str_contains($enumFileContent, $role)) {
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
                        'self::'.$roleEnum."['role'], \n //add-all-your-enums-roles-here \n",
                        'self::'.$roleEnum.", \n //add-all-your-enums-here \n",
                    ],
                    $enumFileContent);

                // Write the modified contents back to the file
                file_put_contents($enumDirectory.'RolesPermissionEnum.php', $enumFileContent);
            } else {
                $this->info("The role : $role already exists");
            }
        } else {
            $enumFile = file_get_contents(__DIR__.'/../Commands/stubs/RolesPermissionEnum.stub');

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
        $this->formatFile($enumDirectory.'RolesPermissionEnum.php');
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createRoleSeeder(): void
    {
        $directory = base_path(config('repository.seeder_path').'/RoleSeeder.php');

        if (file_exists($directory)) {
            $this->warn('RoleSeeder is Already Exist');

            return;
        }

        generateFileFromStub(
            ['{namespace}' => config('repository.seeder_namespace')],
            $directory,
            __DIR__.'/../Commands/stubs/RoleSeeder.stub'
        );
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createPermissionSeeder($container): void
    {
        $directory = base_path(config('repository.seeder_path').'/PermissionSeeder.php');

        if ($container == 'both') {
            $container = 'api';
            $assignWebGuard = "\$createdPermission->assignGuard('web');";
        }

        if (file_exists($directory)) {
            $this->warn('PermissionSeeder is Already Exist');

            return;
        }

        $stubProperties = [
            '{namespace}' => config('repository.seeder_namespace'),
            '{container}' => $container,
            '// another guard assign' => $assignWebGuard ?? '',
        ];

        generateFileFromStub(
            $stubProperties,
            $directory,
            __DIR__.'/../Commands/stubs/PermissionSeeder.stub'
        );
    }
}
