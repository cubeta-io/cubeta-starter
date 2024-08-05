<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Generators\Sources\MigrationGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\AlreadyExist;

class PermissionsInstaller extends AbstractGenerator
{
    public static string $key = "install-permissions";

    public string $type = 'installer';

    public function run(bool $override = false): void
    {
        $this->generateMigrations($override);

        $this->generateModels($override);

        $this->generateTraits($override);

        $this->generateExceptions($override);

        $this->generateInterface($override);

        FileUtils::executeCommandInTheBaseDirectory("php artisan migrate");
    }

    public function generateMigrations(bool $override = false): void
    {
        $migrationPath = CubePath::make(
            config('cubeta-starter.migration_path') . '/'
            . now()->format('Y_m_d_His') . '_create_model_has_permissions_table.php');

        $migrationPath->ensureDirectoryExists();

        if (!MigrationGenerator::checkIfMigrationExists("model_has_permissions")) {
            $this->generateFileFromStub(
                [],
                $migrationPath->fullPath,
                $override,
                CubePath::stubPath('permissions/create_model_has_permissions_table.stub'),
            );
        } else {
            CubeLog::add(new AlreadyExist($migrationPath->fullPath, "Installing Permissions"));
        }

        $migrationPath = CubePath::make(
            config('cubeta-starter.migration_path') . '/'
            . now()->addSecond()->format('Y_m_d_His') . '_create_model_has_roles_table.php');

        if (!MigrationGenerator::checkIfMigrationExists("model_has_roles")) {
            $this->generateFileFromStub(
                [],
                $migrationPath->fullPath,
                $override,
                CubePath::stubPath('permissions/create_model_has_roles_table.stub'),
            );
        } else {
            CubeLog::add(new AlreadyExist($migrationPath->fullPath, "Installing Permissions"));
        }

        $migrationPath = CubePath::make(
            config('cubeta-starter.migration_path') .
            '/' .
            now()->addSeconds(2)->format('Y_m_d_His') .
            '_create_roles_table.php'
        );

        if (!MigrationGenerator::checkIfMigrationExists("roles")) {
            $this->generateFileFromStub(
                [],
                $migrationPath->fullPath,
                $override,
                CubePath::stubPath('permissions/create_roles_table.stub'),
            );
        } else {
            CubeLog::add(new AlreadyExist($migrationPath->fullPath, "Installing Permissions"));
        }
    }

    public function generateModels(bool $override = false): void
    {
        $modelPath = CubePath::make(config('cubeta-starter.model_path') . '/ModelHasPermission.php');

        $modelPath->ensureDirectoryExists();

        $this->generateFileFromStub(
            ['{{modelNamespace}}' => config('cubeta-starter.model_namespace'),],
            $modelPath->fullPath,
            $override,
            CubePath::stubPath('permissions/ModelHasPermission.stub'),
        );

        $modelPath = CubePath::make(config('cubeta-starter.model_path') . '/Role.php');

        $this->generateFileFromStub([
            '{{modelsNamespace}}' => config('cubeta-starter.model_namespace'),
            "{{traitsNamespace}}" => config('cubeta-starter.trait_namespace'),
        ],
            $modelPath->fullPath,
            $override,
            CubePath::stubPath('permissions/Role.stub'),
        );

        $modelPath = CubePath::make(config('cubeta-starter.model_path') . '/ModelHasRole.php');
        $this->generateFileFromStub([
            '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
        ],
            $modelPath->fullPath,
            $override,
            CubePath::stubPath('permissions/ModelHasRole.stub'),
        );
    }

    public function generateTraits(bool $override = false): void
    {
        $traitsPath = CubePath::make(config('cubeta-starter.trait_path') . '/HasPermissions.php');

        $traitsPath->ensureDirectoryExists();

        $this->generateFileFromStub(
            [
                "{{traitsNamespace}}" => config('cubeta-starter.trait_namespace'),
                '{{modelsNamespace}}' => config('cubeta-starter.model_namespace'),
            ],
            $traitsPath->fullPath,
            $override,
            CubePath::stubPath('permissions/HasPermissions.stub'),
        );

        $traitsPath = CubePath::make(config('cubeta-starter.trait_path') . '/HasRoles.php');

        $this->generateFileFromStub([
            "{{traitsNamespace}}"     => config('cubeta-starter.trait_namespace'),
            "{{exceptionsNamespace}}" => config('cubeta-starter.exception_namespace'),
            "{{modelsNamespace}}"     => config('cubeta-starter.model_namespace'),
        ], $traitsPath->fullPath,
            $override,
            __DIR__ . CubePath::stubPath('permissions/HasRoles.stub'),
        );
    }

    public function generateExceptions(bool $override = false): void
    {
        $exceptionsPath = CubePath::make(config('cubeta-starter.exception_path') . '/RoleDoesNotExistException.php');
        $exceptionsPath->ensureDirectoryExists();

        $this->generateFileFromStub(
            ["{{exceptionNamespace}}" => config('cubeta-starter.exception_namespace')],
            $exceptionsPath->fullPath,
            $override,
            CubePath::stubPath('permissions/RoleDoesNotExistException.stub'),
        );
    }

    public function generateInterface(bool $override = false): void
    {
        $interfacePath = CubePath::make("app/Interfaces/ActionsMustBeAuthorized.php");
        $interfacePath->ensureDirectoryExists();
        $this->generateFileFromStub(
            [],
            $interfacePath->fullPath,
            $override,
            CubePath::stubPath('permissions/ActionsMustBeAuthorized.stub'),
        );
    }
}
