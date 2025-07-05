<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\Stub\Builders\Exceptions\RoleDoesNotExistExceptionStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Interfaces\ActionsMustBeAuthorizedStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Migrations\ModelHasRoleMigrationStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Migrations\PermissionMigrationStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Migrations\RoleMigrationStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Models\ModelHasPermissionModelStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Models\ModelHasRoleModelStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Models\RoleModelStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Traits\HasPermissionsStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Traits\HasRoleStubBuilder;
use Cubeta\CubetaStarter\Stub\Publisher;

class PermissionsInstaller extends AbstractGenerator
{
    public static string $key = "install-permissions";

    public string $type = 'installer';

    public function run(bool $override = false): void
    {
        $this->generateMigrations();

        $this->generateModels();

        $this->generateTraits();

        $this->generateExceptions();

        $this->generateInterface();

        $this->addTraitToUserModel();

        $this->addMiddlewares();

        FileUtils::registerMiddleware(
            "'has-role' => HasRoleMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS,
            new PhpImportString("App\\Http\\Middleware\\HasRoleMiddleware")
        );

        FileUtils::registerMiddleware(
            "'has-permission' => HasPermissionMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS,
            new PhpImportString("App\\Http\\Middleware\\HasPermissionMiddleware")
        );

        Settings::make()->setInstalledRoles();
        CubeLog::info("Don't forget to run [php artisan migrate]");
    }

    public function generateMigrations(): void
    {
        $migrationPath = CubePath::make(
            config('cubeta-starter.migration_path') . '/'
            . now()->format('Y_m_d_His') . '_create_model_has_permissions_table.php');

        if (!FileUtils::migrationExists("model_has_permissions") || $this->override) {
            PermissionMigrationStubBuilder::make()
                ->generate($migrationPath, $this->override);
        } else {
            CubeLog::fileAlreadyExists($migrationPath->fullPath, "Installing Permissions");
        }

        $migrationPath = CubePath::make(
            config('cubeta-starter.migration_path') . '/'
            . now()->addSecond()->format('Y_m_d_His') . '_create_model_has_roles_table.php');

        if (!FileUtils::migrationExists("model_has_roles") || !$this->override) {
            ModelHasRoleMigrationStubBuilder::make()
                ->generate($migrationPath, $this->override);
        } else {
            CubeLog::fileAlreadyExists($migrationPath->fullPath, "Installing Permissions");
        }

        $migrationPath = CubePath::make(
            config('cubeta-starter.migration_path') .
            '/' .
            now()->addSeconds(2)->format('Y_m_d_His') .
            '_create_roles_table.php'
        );

        if (!FileUtils::migrationExists("roles") || $this->override) {
            RoleMigrationStubBuilder::make()
                ->generate($migrationPath, $this->override);
        } else {
            CubeLog::fileAlreadyExists($migrationPath->fullPath, "Installing Permissions");
        }
    }

    public function generateModels(): void
    {
        $modelPath = CubePath::make(config('cubeta-starter.model_path') . '/ModelHasPermission.php');
        ModelHasPermissionModelStubBuilder::make()
            ->namespace(config('cubeta-starter.model_namespace'))
            ->generate($modelPath, $this->override);

        $modelPath = CubePath::make(config('cubeta-starter.model_path') . '/Role.php');
        RoleModelStubBuilder::make()
            ->namespace(config('cubeta-starter.model_namespace'))
            ->traitsNamespace(config('cubeta-starter.trait_namespace'))
            ->generate($modelPath, $this->override);

        $modelPath = CubePath::make(config('cubeta-starter.model_path') . '/ModelHasRole.php');

        ModelHasRoleModelStubBuilder::make()
            ->namespace(config('cubeta-starter.model_namespace'))
            ->generate($modelPath, $this->override);
    }

    public function generateTraits(): void
    {
        $traitsPath = CubePath::make(config('cubeta-starter.trait_path') . '/HasPermissions.php');
        HasPermissionsStubBuilder::make()
            ->namespace(config('cubeta-starter.trait_namespace'))
            ->modelsNamespace(config('cubeta-starter.model_namespace'))
            ->generate($traitsPath, $this->override);

        $traitsPath = CubePath::make(config('cubeta-starter.trait_path') . '/HasRoles.php');
        HasRoleStubBuilder::make()
            ->namespace(config('cubeta-starter.trait_namespace'))
            ->exceptionsNamespace(config('cubeta-starter.exception_namespace'))
            ->modelsNamespace(config('cubeta-starter.model_namespace'))
            ->generate($traitsPath, $this->override);
    }

    public function generateExceptions(): void
    {
        $exceptionsPath = CubePath::make(config('cubeta-starter.exception_path') . '/RoleDoesNotExistException.php');
        RoleDoesNotExistExceptionStubBuilder::make()
            ->namespace(config('cubeta-starter.exception_namespace'))
            ->generate($exceptionsPath, $this->override);
    }

    public function generateInterface(): void
    {
        $interfacePath = CubePath::make("app/Interfaces/ActionsMustBeAuthorized.php");
        ActionsMustBeAuthorizedStubBuilder::make()
            ->generate($interfacePath, $this->override);
    }

    public function addTraitToUserModel(): void
    {
        $modelPath = CubePath::make(config('cubeta-starter.model_path') . '/User.php');

        if (!$modelPath->exist()) {
            CubeLog::notFound($modelPath->fullPath, "Trying to add HasRoles trait to [User] model");
            return;
        }

        $modelContent = $modelPath->getContent();
        $pattern = '/\s*class\s*User\s*(.*?)\s*\{\s*(.*?)\s*}/s';
        $hasRoleImportStatement = new PhpImportString(config("cubeta-starter.trait_namespace") . "\HasRoles");

        if (!preg_match($pattern, $modelContent, $matches)) {
            CubeLog::failedAppending("use HasRoles;", $modelPath, "Installing permissions");
        }

        if (empty($matches[2])) {
            CubeLog::failedAppending("use HasRoles;", $modelPath, "Installing permissions");
        }

        $modelContent = str_replace($matches[2], "\nuse HasRoles;\n$matches[2]", $modelContent);
        $modelPath->putContent($modelContent);
        FileUtils::addImportStatement($hasRoleImportStatement, $modelPath);
        $modelPath->format();
    }

    public function addMiddlewares(): void
    {
        Publisher::make()
            ->source(CubePath::stubPath('Middlewares/HasPermissionMiddleware.stub'))
            ->destination(CubePath::make('app/Http/Middleware/HasPermissionMiddleware.php'))
            ->publish($this->override);

        Publisher::make()
            ->source(CubePath::stubPath('Middlewares/HasRoleMiddleware.stub'))
            ->destination(CubePath::make('app/Http/Middleware/HasRoleMiddleware.php'))
            ->publish($this->override);
    }
}
