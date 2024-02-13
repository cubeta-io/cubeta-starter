<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Exception;
use Illuminate\Console\Command;

class InstallPermissions extends Command
{
    use AssistCommand;

    protected $signature = 'cubeta:install-permissions {--force}';
    protected $description = "this command will initialize your project with the required classes to handle multi actor project";

    /**
     * @return void
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
     */
    public function generateMigrations(bool $override = false): void
    {
        $migrationPath = base_path(config('cubeta-starter.migration_path'));
        ensureDirectoryExists($migrationPath);

        try {
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


            if (!$this->checkIfMigrationExists("roleables")) {
                generateFileFromStub(
                    [],
                    $migrationPath . '/' . now()->addSecond()->format('Y_m_d_His') . '_create_roleables_table.php',
                    __DIR__ . '/stubs/permissions/create_roleables_table.stub',
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

        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * @param bool $override
     * @return void
     */
    public function generateModels(bool $override = false): void
    {
        $modelPath = base_path(config('cubeta-starter.model_path'));
        ensureDirectoryExists($modelPath);
        try {
            generateFileFromStub(['{{modelNamespace}}' => config('cubeta-starter.model_namespace'),],
                $modelPath . '/ModelHasPermission.php',
                __DIR__ . '/stubs/permissions/ModelHasPermission.stub',
                $override
            );
            $this->info('ModelHasPermission Generated Successfully');
        } catch
        (Exception $e) {
            if ($e->getMessage() == "The class exists!") {
                $this->error("The ModelHasPermission Is Already Exists");
            } else $this->error($e->getMessage());
        }

        try {
            generateFileFromStub([
                '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
            ],
                $modelPath . '/Role.php',
                __DIR__ . '/stubs/permissions/Role.stub',
                $override
            );
            $this->info('Role Model Generated Successfully');
        } catch (Exception $e) {
            if ($e->getMessage() == "The class exists!") {
                $this->error("The Role Is Already Exists");
            } else $this->error($e->getMessage());
        }

        try {
            generateFileFromStub([
                '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
            ],
                $modelPath . '/Roleable.php',
                __DIR__ . '/stubs/permissions/Roleable.stub',
                $override
            );
            $this->info('Roleable Model Generated Successfully');

        } catch (Exception $e) {
            if ($e->getMessage() == "The class exists!") {
                $this->error("The Roleable Is Already Exists");
            } else $this->error($e->getMessage());
        }
    }

    /**
     * @param bool $override
     * @return void
     */
    public function generateTraits(bool $override = false): void
    {
        $traitsPath = base_path(config('cubeta-starter.trait_path'));
        ensureDirectoryExists($traitsPath);

        try {
            generateFileFromStub(
                [
                    "{{traitsNamespace}}" => config('cubeta-starter.trait_namespace'),
                    '{{exceptionsNameSpace}}' => config('cubeta-starter.exception_namespace'),
                    '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
                ],
                $traitsPath . '/HasPermissions.php',
                __DIR__ . '/stubs/permissions/HasPermissions.stub',
                $override
            );

            $this->info('Permissions Traits Generated Successfully');
        } catch (Exception $exception) {
            if ($exception->getMessage() == "The class exists!") {
                $this->error("HasPermission Trait Is Already Exists");
            } else $this->error($exception->getMessage());
        }
    }

    /**
     * @param bool $override
     * @return void
     */
    public function generateExceptions(bool $override = false): void
    {
        $exceptionsPath = base_path(config('cubeta-starter.exception_path'));
        ensureDirectoryExists($exceptionsPath);

        try {
            generateFileFromStub(
                ["{{exceptionNamespace}}" => config('cubeta-starter.exception_namespace')],
                $exceptionsPath . '/RoleDoesNotExistException.php',
                __DIR__ . '/stubs/permissions/RoleDoesNotExistException.stub',
                $override
            );

            $this->info('Permissions Exceptions Generated Successfully');
        } catch (Exception $e) {
            if ($e->getMessage() == "The class exists!") {
                $this->error("The RoleDoesNotExistExceptions Is Already Exist");
            } else $this->error($e->getMessage());
        }
    }

    public function generateInterface(bool $override = false): void
    {
        $interfacePath = app_path('Interfaces');
        ensureDirectoryExists($interfacePath);
        try {
            generateFileFromStub(
                [],
                "$interfacePath/ActionsMustBeAuthorized.php",
                __DIR__ . '/stubs/permissions/ActionsMustBeAuthorized.stub',
                $override
            );

            $this->info("ActionsMustBeAuthorized Interface Generated Successfully");
        } catch (Exception $e) {
            if ($e->getMessage() == "The class exists!") {
                $this->error("The ActionsMustBeAuthorized Interface Is Already Exist");
            } else $this->error($e->getMessage());
        }
    }
}
