<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\PackageManager;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\Stub\Builders\Resources\PermissionResourceStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Resources\RoleResourceStubBuilder;

class PermissionsInstaller extends AbstractGenerator
{
    public static string $key = 'install-permissions';

    public string $type = 'installer';

    public function run(bool $override = false): void
    {
        PackageManager::composerInstall('spatie/laravel-permission');

        FileUtils::executeCommandInTheBaseDirectory(
            'php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider"'
        );

        $this->addTraitToUserModel();

        $this->addMiddlewares();

        if (Settings::make()->installedApi() || Settings::make()->getFrontendType() == FrontendTypeEnum::REACT_TS) {
            $this->generateRoleResource();
            $this->generatePermissionResource();
            $this->addRolesAndPermissionsToUserResource();
        }

        Settings::make()->setInstalledRoles();
        CubeLog::info("Don't forget to run [php artisan migrate]");
    }

    public function generateRoleResource(): void
    {
        $resourcePath = CubePath::make(config('cubeta-starter.resource_path')."/$this->version/RoleResource.php");
        RoleResourceStubBuilder::make()
            ->namespace(config('cubeta-starter.resource_namespace')."\\$this->version")
            ->generate($resourcePath, $this->override);
    }

    public function generatePermissionResource(): void
    {
        $resourcePath = CubePath::make(config('cubeta-starter.resource_path')."/$this->version/PermissionResource.php");
        PermissionResourceStubBuilder::make()
            ->namespace(config('cubeta-starter.resource_namespace')."\\$this->version")
            ->generate($resourcePath, $this->override);
    }

    /**
     * @throws \Exception
     */
    public function addRolesAndPermissionsToUserResource(): void
    {
        $resourcePath = CubePath::make(config('cubeta-starter.resource_path')."/$this->version/UserResource.php");

        if (! $resourcePath->exist()) {
            CubeLog::notFound($resourcePath->fullPath, 'Trying to add roles and permissions fields to [UserResource]');

            return;
        }

        $content = $resourcePath->getContent();

        if (FileUtils::contentExistsInString($content, 'RoleResource::collection')) {
            CubeLog::contentAlreadyExists('roles and permissions fields', $resourcePath->fullPath, 'Installing permissions');

            return;
        }

        $pattern = '/(\'email\'\s*=>\s*\$this->email\s*,)/';

        if (! preg_match($pattern, $content)) {
            CubeLog::failedAppending("'roles' => RoleResource::collection(...)", $resourcePath, 'Installing permissions');

            return;
        }

        $rolesField = new ResourcePropertyString(
            'roles',
            "RoleResource::collection(\$this->whenLoaded('roles'))",
            [new PhpImportString(config('cubeta-starter.resource_namespace')."\\$this->version\\RoleResource")]
        );

        $permissionsField = new ResourcePropertyString(
            'permissions',
            "PermissionResource::collection(\$this->whenLoaded('permissions'))",
            [new PhpImportString(config('cubeta-starter.resource_namespace')."\\$this->version\\PermissionResource")]
        );

        $addition = "\n$rolesField,\n$permissionsField,";

        $content = preg_replace($pattern, '$1'.$addition, $content);
        $resourcePath->putContent($content);

        foreach ([...$rolesField->imports, ...$permissionsField->imports] as $import) {
            FileUtils::addImportStatement($import, $resourcePath);
        }

        $resourcePath->format();
    }

    /**
     * @throws \Exception
     */
    public function addTraitToUserModel(): void
    {
        $modelPath = CubePath::make(config('cubeta-starter.model_path').'/User.php');

        if (! $modelPath->exist()) {
            CubeLog::notFound($modelPath->fullPath, 'Trying to add HasRoles trait to [User] model');

            return;
        }

        $modelContent = $modelPath->getContent();
        $pattern = '/\s*class\s*User\s*(.*?)\s*\{\s*(.*?)\s*}/s';
        $hasRoleImportStatement = new PhpImportString("Spatie\Permission\Traits\HasRoles");

        if (! preg_match($pattern, $modelContent, $matches)) {
            CubeLog::failedAppending('use HasRoles;', $modelPath, 'Installing permissions');

            return;
        }

        if (empty($matches[2])) {
            CubeLog::failedAppending('use HasRoles;', $modelPath, 'Installing permissions');

            return;
        }

        if (FileUtils::contentExistsInString($matches[2], 'use HasRoles;')) {
            CubeLog::contentAlreadyExists('use HasRoles;', $modelPath->fullPath, 'Installing permissions');

            return;
        }

        $modelContent = str_replace($matches[2], "\nuse HasRoles;\n$matches[2]", $modelContent);
        $modelPath->putContent($modelContent);
        FileUtils::addImportStatement($hasRoleImportStatement, $modelPath);
        $modelPath->format();
    }

    public function addMiddlewares(): void
    {
        FileUtils::registerMiddleware(
            "'role' => RoleMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS,
            new PhpImportString("Spatie\Permission\Middleware\RoleMiddleware")
        );

        FileUtils::registerMiddleware(
            "'permission' => PermissionMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS,
            new PhpImportString("Spatie\Permission\Middleware\PermissionMiddleware")
        );

        FileUtils::registerMiddleware(
            "'role_or_permission' => RoleOrPermissionMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS,
            new PhpImportString("Spatie\Permission\Middleware\RoleOrPermissionMiddleware")
        );
    }
}
