<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class InstallPermissions extends Command
{
    use AssistCommand;

    protected $signature = 'cubeta:install-permissions {--force}';
    protected $description = "this command will initialize your project with the required classes to handle multi actor project";

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $override = $this->option('force') ?? false;

        $this->generateMigrations($override);

        $this->generateModels($override);

        $this->generateTraits($override);

        $this->generateExceptions($override);

        $this->generateInterface($override);
    }

    /**
     * @param bool $override
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateMigrations(bool $override = false): void
    {
        $migrationPath = base_path(config('cubeta-starter.migration_path'));
        ensureDirectoryExists($migrationPath);

        if (!$this->checkIfMigrationExists("model_has_permissions")) {
            generateFileFromStub(
                [],
                $migrationPath . '/' . now()->format('Y_m_d_His') . '_create_model_has_permissions_table.php',
                __DIR__ . '/stubs/permissions/create_model_has_permissions_table.stub',
                $override
            );
        } else {
            $this->error("model_has_permissions Migration Already Exists");
        }


        if (!$this->checkIfMigrationExists("model_has_roles")) {
            generateFileFromStub(
                [],
                $migrationPath . '/' . now()->addSecond()->format('Y_m_d_His') . '_create_model_has_roles_table.php',
                __DIR__ . '/stubs/permissions/create_model_has_roles_table.stub',
                $override
            );
        } else {
            $this->error("roleables Migration Already Exists");
        }


        if (!$this->checkIfMigrationExists("roles")) {
            generateFileFromStub(
                [],
                $migrationPath . '/' . now()->addSeconds(2)->format('Y_m_d_His') . '_create_roles_table.php',
                __DIR__ . '/stubs/permissions/create_roles_table.stub',
                $override
            );
        } else {
            $this->error("roles Migration Already Exists");
        }
    }

    /**
     * @param bool $override
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateModels(bool $override = false): void
    {
        $modelPath = base_path(config('cubeta-starter.model_path'));
        ensureDirectoryExists($modelPath);

        generateFileFromStub(['{{modelNamespace}}' => config('cubeta-starter.model_namespace'),],
            $modelPath . '/ModelHasPermission.php',
            __DIR__ . '/stubs/permissions/ModelHasPermission.stub',
            $override,
            fn() => $this->error("The ModelHasPermission Is Already Exists"),
            fn() => $this->info('ModelHasPermission Generated Successfully')
        );

        generateFileFromStub([
            '{{modelsNamespace}}' => config('cubeta-starter.model_namespace'),
            "{{traitsNamespace}}" => config('cubeta-starter.trait_namespace'),
        ],
            $modelPath . '/Role.php',
            __DIR__ . '/stubs/permissions/Role.stub',
            $override,
            fn() => $this->error("The Role Is Already Exists"),
            fn() => $this->info('Role Model Generated Successfully')
        );

        generateFileFromStub([
            '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
        ],
            $modelPath . '/Roleable.php',
            __DIR__ . '/stubs/permissions/Roleable.stub',
            $override,
            fn() => $this->error("The Roleable Is Already Exists"),
            fn() => $this->info('Roleable Model Generated Successfully')
        );
    }

    /**
     * @param bool $override
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateTraits(bool $override = false): void
    {
        $traitsPath = base_path(config('cubeta-starter.trait_path'));
        ensureDirectoryExists($traitsPath);

        generateFileFromStub(
            [
                "{{traitsNamespace}}" => config('cubeta-starter.trait_namespace'),
                '{{modelsNamespace}}' => config('cubeta-starter.model_namespace'),
            ],
            $traitsPath . '/HasPermissions.php',
            __DIR__ . '/stubs/permissions/HasPermissions.stub',
            $override,
            fn() => $this->error("HasPermission Trait Is Already Exists"),
            fn() => $this->info('HasPermissions Trait Generated Successfully')
        );

        generateFileFromStub([
            "{{traitsNamespace}}" => config('cubeta-starter.trait_namespace'),
            "{{exceptionsNamespace}}" => config('cubeta-starter.exception_namespace'),
            "{{modelsNamespace}}" => config('cubeta-starter.model_namespace'),
        ], $traitsPath . '/HasRolesPermissions.php',
            __DIR__ . '/stubs/permissions/HasRolesPermissions.stub',
            $override,
            fn() => $this->error("HasRolePermissions Trait Already Exists"),
            fn() => $this->info("HasRolePermissions Trait Generated Successfully")
        );
    }

    /**
     * @param bool $override
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateExceptions(bool $override = false): void
    {
        $exceptionsPath = base_path(config('cubeta-starter.exception_path'));
        ensureDirectoryExists($exceptionsPath);

        generateFileFromStub(
            ["{{exceptionNamespace}}" => config('cubeta-starter.exception_namespace')],
            $exceptionsPath . '/RoleDoesNotExistException.php',
            __DIR__ . '/stubs/permissions/RoleDoesNotExistException.stub',
            $override,
            fn() => $this->error("The RoleDoesNotExistExceptions Is Already Exist"),
            fn() => $this->info('Permissions Exceptions Generated Successfully')
        );
    }

    /**
     * @param bool $override
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateInterface(bool $override = false): void
    {
        $interfacePath = app_path('Interfaces');
        ensureDirectoryExists($interfacePath);
        generateFileFromStub(
            [],
            "$interfacePath/ActionsMustBeAuthorized.php",
            __DIR__ . '/stubs/permissions/ActionsMustBeAuthorized.stub',
            $override,
            fn() => $this->error("The ActionsMustBeAuthorized Interface Is Already Exist"),
            fn() => $this->info("ActionsMustBeAuthorized Interface Generated Successfully")
        );
    }
}
