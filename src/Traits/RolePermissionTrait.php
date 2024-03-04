<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\AlreadyExist;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

trait RolePermissionTrait
{
    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createRoleSeeder(): void
    {
        $seederPath = CubePath::make(config('cubeta-starter.seeder_path') . '/RoleSeeder.php');

        if ($seederPath->exist()) {
            CubeLog::add(new AlreadyExist($seederPath->fullPath, "Creating Role Seeder"));
            return;
        }

        FileUtils::generateFileFromStub(
            ['{namespace}' => config('cubeta-starter.seeder_namespace')],
            $seederPath->fullPath,
            __DIR__ . '/../Commands/stubs/RoleSeeder.stub'
        );
    }

    public function createRolesEnum(string $role, array $permissions = null): void
    {
        $enum = file_get_contents(__DIR__ . '/../Commands/stubs/RolesPermissionEnum-entity.stub');
        $roleEnum = $this->roleNaming($role);
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
        FileUtils::formatFile($enumDirectory . 'RolesPermissionEnum.php');
    }

    /**
     * return the role enum for a given string
     * @param string $name
     * @return string
     */
    public function roleNaming(string $name): string
    {
        return Str::singular(Str::upper(Str::snake($name)));
    }
}
