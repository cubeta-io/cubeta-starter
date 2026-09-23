<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\PackageManager;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

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

        Settings::make()->setInstalledRoles();
        CubeLog::info("Don't forget to run [php artisan migrate]");
    }

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
