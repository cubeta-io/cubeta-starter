<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class InstallPermissions extends Command
{
    use AssistCommand;

    protected $signature = 'cubeta:install-permissions';
    protected $description = "this command will initialize your project with the required classes to handle multi actor project";

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $this->generateMigrations();

        $this->generateModels();

        $this->generateTraits();

        $this->generateExceptions();
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function generateMigrations(): void
    {
        try {
            if (!$this->checkIfMigrationExists("model_has_permissions")) {
                generateFileFromStub(
                    [],
                    config('cubeta-starter.migration_path') . '/' . now()->format('Y_m_d_His') . '_create_model_has_permissions_table.php',
                    __DIR__ . '/stubs/permissions/create_model_has_permissions_table.stub'
                );
            } else {
                $this->warn("model_has_permissions Migration Already Exists");
            }


            if (!$this->checkIfMigrationExists("roleables")) {
                generateFileFromStub(
                    [],
                    config('cubeta-starter.migration_path') . '/' . now()->addSecond()->format('Y_m_d_His') . '_create_roleables_table.php',
                    __DIR__ . '/stubs/permissions/create_roleables_table.stub'
                );
            } else {
                $this->warn("roleables Migration Already Exists");
            }


            if (!$this->checkIfMigrationExists("roles")) {
                generateFileFromStub(
                    [],
                    config('cubeta-starter.migration_path') . '/' . now()->addSeconds(2)->format('Y_m_d_His') . '_create_roles_table.php',
                    __DIR__ . '/stubs/permissions/create_roles_table.stub'
                );
            } else {
                $this->warn("roles Migration Already Exists");
            }

        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * @return void
     */
    public function generateModels(): void
    {
        try {
            generateFileFromStub([
                '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
            ],
                config('cubeta-starter.model_path') . '/ModelHasPermission.php',
                __DIR__ . '/stubs/permissions/ModelHasPermission.stub'
            );
            $this->info('ModelHasPermission Generated Successfully');
        } catch (\Exception $e) {
            if ($e->getMessage() == "The class exists!") {
                $this->warn("The ModelHasPermission Is Already Exists");
            } else $this->error($e->getMessage());
        }

        try {
            generateFileFromStub([
                '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
            ],
                config('cubeta-starter.model_path') . '/Role.php',
                __DIR__ . '/stubs/permissions/Role.stub'
            );
            $this->info('Role Model Generated Successfully');
        } catch (\Exception $e) {
            if ($e->getMessage() == "The class exists!") {
                $this->warn("The Role Is Already Exists");
            } else $this->error($e->getMessage());
        }

        try {
            generateFileFromStub([
                '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
            ],
                config('cubeta-starter.model_path') . '/Roleable.php',
                __DIR__ . '/stubs/permissions/Roleable.stub'
            );
            $this->info('Roleable Model Generated Successfully');

        } catch (\Exception $e) {
            if ($e->getMessage() == "The class exists!") {
                $this->warn("The Roleable Is Already Exists");
            } else $this->error($e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function generateTraits(): void
    {
        try {
            generateFileFromStub(
                [
                    "{{traitsNamespace}}" => config('cubeta-starter.trait_namespace'),
                    '{{exceptionsNameSpace}}' => config('cubeta-starter.exception_namespace'),
                    '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
                ],
                config('cubeta-starter.trait_path') . '/HasPermissions.php',
                __DIR__ . '/stubs/permissions/HasPermissions.stub'
            );

            $this->info('Permissions Traits Generated Successfully');
        } catch (\Exception $exception) {
            if ($exception->getMessage() == "The class exists!") {
                $this->warn("HasPermission Trait Is Already Exists");
            } else $this->error($exception->getMessage());
        }
    }

    /**
     * @return void
     */
    public function generateExceptions(): void
    {
        try {
            generateFileFromStub(
                ["{{exceptionNamespace}}" => config('cubeta-starter.exception_namespace')],
                config('cubeta-starter.exception_path') . '/RoleDoesNotExistException.php',
                __DIR__ . '/stubs/permissions/RoleDoesNotExistException.stub'
            );

            $this->info('Permissions Exceptions Generated Successfully');
        } catch (\Exception $e) {
            if ($e->getMessage() == "The class exists!") {
                $this->warn("The RoleDoesNotExistExceptions Is Already Exist");
            } else $this->error($e->getMessage());
        }
    }
}
