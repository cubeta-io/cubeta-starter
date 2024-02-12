<?php

namespace Cubeta\CubetaStarter\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class InstallPermissions extends Command
{
    protected $signature = 'cubeta:install-permissions';
    protected $description = "this command will initialize your project with the required classes to handle multi actor project";

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $this->generateMigrations();
        $this->info("Permissions Migrations Generated Successfully");

        $this->generateModels();
        $this->info('Permissions Models Generated Successfully');

        $this->generateTraits();
        $this->info('Permissions Traits Generated Successfully');

        $this->generateExceptions();
        $this->info('Permissions Exceptions Generated Successfully');
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function generateMigrations(): void
    {
        generateFileFromStub(
            [],
            config('cubeta-starter.migration_path') . '/' . now()->format('Y_m_d_His') . '_create_model_has_permissions_table.php',
            __DIR__ . '/stubs/permissions/create_model_has_permissions_table.stub'
        );

        generateFileFromStub(
            [],
            config('cubeta-starter.migration_path') . '/' . now()->addSecond()->format('Y_m_d_His') . '_create_roleables_table.php',
            __DIR__ . '/stubs/permissions/create_roleables_table.stub'
        );

        generateFileFromStub(
            [],
            config('cubeta-starter.migration_path') . '/' . now()->addSeconds(2)->format('Y_m_d_His') . '_create_roles_table.php',
            __DIR__ . '/stubs/permissions/create_roles_table.stub'
        );
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateModels(): void
    {
        generateFileFromStub([
            '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
        ],
            config('cubeta-starter.model_path') . '/ModelHasPermission.php',
            __DIR__ . '/stubs/permissions/ModelHasPermission.stub'
        );

        generateFileFromStub([
            '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
        ],
            config('cubeta-starter.model_path') . '/Role.php',
            __DIR__ . '/stubs/permissions/Role.stub'
        );

        generateFileFromStub([
            '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
        ],
            config('cubeta-starter.model_path') . '/Roleable.php',
            __DIR__ . '/stubs/permissions/Roleable.stub'
        );

        $this->info('Permissions Models Generated Successfully');
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function generateTraits(): void
    {
        generateFileFromStub(
            [
                "{{traitsNamespace}}" => config('cubeta-starter.trait_namespace'),
                '{{exceptionsNameSpace}}' => config('cubeta-starter.exception_namespace'),
                '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
            ],
            config('cubeta-starter.trait_path') . '/HasPermissions.php',
            __DIR__ . '/stubs/permissions/HasPermissions.stub'
        );
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateExceptions(): void
    {
        generateFileFromStub(
            ["{{exceptionNamespace}}" => config('cubeta-starter.exception_namespace')],
            config('cubeta-starter.exception_path') . '/RoleDoesNotExistException.php',
            __DIR__ . '/stubs/permissions/RoleDoesNotExistException.stub'
        );
    }
}
