<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

trait RolePermissionTrait
{
    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createRoleSeeder(): void
    {
        $directory = base_path(config('cubeta-starter.seeder_path') . '/RoleSeeder.php');

        if (file_exists($directory)) {
            $this->warn('RoleSeeder is Already Exists');

            return;
        }

        generateFileFromStub(
            ['{namespace}' => config('cubeta-starter.seeder_namespace')],
            $directory,
            __DIR__ . '/../Commands/stubs/RoleSeeder.stub'
        );
    }

    public function createRolesEnum(string $role, array $permissions = null): void
    {
        $enum = file_get_contents(__DIR__ . '/../Commands/stubs/RolesPermissionEnum-entity.stub');
        $roleEnum = roleNaming($role);
        $roleEnumValue = Str::singular(Str::lower($role));

        if ($permissions) {
            for ($i = 0; $i < count($permissions); $i++) {
                $permissions[$i] = Str::lower($permissions[$i]);
            }
        }

        $placedPermission = $permissions ? json_encode($permissions, JSON_PRETTY_PRINT) : '[]';

        $enum = str_replace(
            ['{enum}', '{roleValue}', '{permissions}'],
            [$roleEnum, $roleEnumValue, $placedPermission],
            $enum
        );

        $enumDirectory = base_path() . '/app/Enums/';

        $files = new Filesystem();
        $files->makeDirectory($enumDirectory, 0777, true, true);

        if (file_exists($enumDirectory . 'RolesPermissionEnum.php')) {
            $enumFileContent = file_get_contents($enumDirectory . 'RolesPermissionEnum.php');
            if (!str_contains($enumFileContent, $role)) {
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
                        'self::' . $roleEnum . "['role'], \n //add-all-your-enums-roles-here \n",
                        'self::' . $roleEnum . ", \n //add-all-your-enums-here \n",
                    ],
                    $enumFileContent
                );

                // Write the modified contents back to the file
                file_put_contents($enumDirectory . 'RolesPermissionEnum.php', $enumFileContent);
            } else {
                $this->info("The role : {$role} already exists");
            }
        } else {
            $enumFile = file_get_contents(__DIR__ . '/../Commands/stubs/RolesPermissionEnum.stub');

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
                $enumFile
            );
            file_put_contents($enumDirectory . 'RolesPermissionEnum.php', $enumFile);
        }
        $this->formatFile($enumDirectory . 'RolesPermissionEnum.php');
    }
}
