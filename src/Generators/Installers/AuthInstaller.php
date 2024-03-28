<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Info\SuccessMessage;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Facades\Artisan;

class AuthInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-auth";

    public static string $type = 'installer';

    /**
     * @param bool $override
     * @return void
     */
    public function run(bool $override = true): void
    {
        $this->generateUserMigration($override);
        $this->generateUserModel($override);
        $this->generateUserService($override);
        $this->generateUserRepository($override);
        $this->generateAuthRequests($override);
        $this->generateResetPasswordNotification($override);
        $this->generateAuthViews($override);
        $this->generateUserFactory($override);

        if ($this->generatedFor == ContainerType::API || $this->generatedFor == ContainerType::BOTH) {
            $this->generateUserResource($override);
            $this->generateBaseAuthController($override);
        }

        if ($this->generatedFor == ContainerType::WEB || $this->generatedFor == ContainerType::BOTH) {
            $this->generateBaseAuthWebController($override);
            $this->generateWebAuthRoutes();
        }
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateUserMigration(bool $override = false): void
    {
        $migrationPath = CubePath::make(config('cubeta-starter.migration_path') . '/2014_10_12_000000_create_users_table.php');

        $this->generateFileFromStub(
            [],
            $migrationPath->fullPath,
            $override,
            __DIR__ . '/../../stubs/Auth/UserMigration.stub',
        );
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateUserModel(bool $override = false): void
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.model_namespace'),
        ];

        $modelPath = CubePath::make(config('cubeta-starter.model_path') . "/User.php");

        $modelPath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $modelPath->fullPath, $override, __DIR__ . '/../../stubs/Auth/UserModel.stub');
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateUserService(bool $override = false): void
    {
        // user service
        $stubProperties = [
            '{{namespace}}' => config('cubeta-starter.service_namespace'),
            '{{modelNamespace}}' => config('cubeta-starter.model_namespace'),
            '{{repositoryNamespace}}' => config('cubeta-starter.repository_namespace'),
        ];


        $servicePath = CubePath::make(config('cubeta-starter.service_path') . '/User' . "/UserService.php");

        $servicePath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $servicePath->fullPath, $override, __DIR__ . '/../../stubs/Auth/UserService.stub');

        // service interface
        $interfacePath = CubePath::make(config('cubeta-starter.service_path') . "/User/IUserService.php");
        $interfacePath->ensureDirectoryExists();

        $this->generateFileFromStub([
            '{{namespace}}' => config('cubeta-starter.service_namespace'),
            "{{modelNamespace}}" => config('cubeta-starter.model_namespace'),
        ], $interfacePath->fullPath, $override, __DIR__ . '/../../stubs/Auth/IUserService.stub');
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateUserRepository(bool $override = false): void
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.repository_namespace'),
            '{modelNamespace}' => config('cubeta-starter.model_namespace'),
        ];

        $repositoryPath = CubePath::make(config('cubeta-starter.repository_path') . "/UserRepository.php");

        $repositoryPath->ensureDirectoryExists();

        $this->generateFileFromStub(
            $stubProperties,
            $repositoryPath->fullPath,
            $override,
            __DIR__ . '/../../stubs/Auth/UserRepository.stub'
        );
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateAuthRequests(bool $override = false): void
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.request_namespace'),
        ];

        $requestDirectory = base_path(config('cubeta-starter.request_path') . '/AuthRequests');

        FileUtils::ensureDirectoryExists($requestDirectory);

        $this->generateFileFromStub($stubProperties, "{$requestDirectory}/AuthLoginRequest.php", $override, __DIR__ . '/../../stubs/Auth/AuthRequests/AuthLoginRequest.stub');
        $this->generateFileFromStub($stubProperties, "{$requestDirectory}/AuthRegisterRequest.php", $override, __DIR__ . '/../../stubs/Auth/AuthRequests/AuthRegisterRequest.stub');
        $this->generateFileFromStub($stubProperties, "{$requestDirectory}/CheckPasswordResetRequest.php", $override, __DIR__ . '/../../stubs/Auth/AuthRequests/CheckPasswordResetRequest.stub');
        $this->generateFileFromStub($stubProperties, "{$requestDirectory}/RequestResetPasswordRequest.php", $override, __DIR__ . '/../../stubs/Auth/AuthRequests/RequestResetPasswordRequest.stub');
        $this->generateFileFromStub($stubProperties, "{$requestDirectory}/ResetPasswordRequest.php", $override, __DIR__ . '/../../stubs/Auth/AuthRequests/ResetPasswordRequest.stub');
        $this->generateFileFromStub($stubProperties, "{$requestDirectory}/UpdateUserRequest.php", $override, __DIR__ . '/../../stubs/Auth/AuthRequests/UpdateUserRequest.stub');
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateResetPasswordNotification(bool $override = false): void
    {
        $notificationPath = CubePath::make("app/Notifications/ResetPasswordCodeEmail.php");
        $notificationPath->ensureDirectoryExists();

        $this->generateFileFromStub([], $notificationPath->fullPath, $override, __DIR__ . '/../../stubs/Auth/ResetPasswordCodeEmail.stub');
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateAuthViews(bool $override = false): void
    {
        $viewsPath = CubePath::make("resources/views/emails/reset-password-email.blade.php");
        $viewsPath->ensureDirectoryExists();
        $this->generateFileFromStub([], $viewsPath->fullPath, $override, __DIR__ . '/../../stubs/Auth/reset-password.stub');

        if (ContainerType::isWeb($this->generatedFor)) {
            Artisan::call('vendor:publish', [
                '--tag' => 'cubeta-auth-views',
                '--force' => $override
            ]);

            CubeLog::add(Artisan::output());
        }
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateUserFactory(bool $override = false): void
    {
        $factoryPath = CubePath::make(config('cubeta-starter.factory_path') . "/UserFactory.php");
        $factoryPath->ensureDirectoryExists();

        $this->generateFileFromStub([], $factoryPath->fullPath, $override, __DIR__ . '/../../stubs/Auth/user-factory.stub');
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateUserResource(bool $override = false): void
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.resource_namespace'),
        ];

        $resourcePath = CubePath::make(config('cubeta-starter.resource_path') . "/UserResource.php");

        $resourcePath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $resourcePath->fullPath, $override, __DIR__ . '/../../stubs/Auth/UserResource.stub');
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateBaseAuthController(bool $override = false): void
    {
        $stubProperties = [
            '{{namespace}}' => config('cubeta-starter.api_controller_namespace'),
            '{{requestNamespace}}' => config('cubeta-starter.request_namespace'),
            '{{serviceNamespace}}' => config('cubeta-starter.service_namespace'),
            '{{resourceNamespace}}' => config('cubeta-starter.resource_namespace'),
        ];

        $controllerPath = CubePath::make(config('cubeta-starter.api_controller_path') . "/BaseAuthController.php");

        $controllerPath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $controllerPath->fullPath, $override, __DIR__ . '/../../stubs/Auth/BaseAuthController.stub');
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateBaseAuthWebController(bool $override = false): void
    {
        $stubProperties = [
            '{{namespace}}' => config('cubeta-starter.web_controller_namespace'),
            '{{requestNamespace}}' => config('cubeta-starter.request_namespace'),
            '{{serviceNamespace}}' => config('cubeta-starter.service_namespace')
        ];

        $controllerPath = CubePath::make(config('cubeta-starter.web_controller_path') . "/BaseAuthController.php");

        $controllerPath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $controllerPath->fullPath, $override, __DIR__ . '/../../stubs/Auth/BaseAuthWebController.stub');
    }

    /**
     * @return void
     */
    private function generateWebAuthRoutes(): void
    {
        $protectedRoutes = file_get_contents(__DIR__ . '/../../stubs/Auth/auth-web-routes-protected.stub');
        $publicRoutes = file_get_contents(__DIR__ . '/../../stubs/Auth/auth-web-routes-public.stub');

        $publicRouteFile = CubePath::make("routes/v1/web/public.php");
        $protectedRouteFile = CubePath::make("routes/v1/web/protected.php");

        $publicRouteFile->ensureDirectoryExists();
        $protectedRouteFile->ensureDirectoryExists();

        $importStatement = "use " . config('cubeta-starter.web_controller_namespace') . ";";

        if (!$publicRouteFile->exist()) {
            $this->generateFileFromStub(["{route}" => $publicRoutes], $publicRouteFile->fullPath, true, __DIR__ . '/../../stubs/api.stub');
            $this->addRouteFileToServiceProvider($publicRouteFile, ContainerType::WEB);
            FileUtils::addImportStatement($importStatement, $publicRouteFile);
            CubeLog::add(new SuccessMessage("Auth Web Public Routes Has Been Added Successfully To : [{$publicRouteFile->fullPath}]"));
        } else {
            $publicRoutes = explode(";", $publicRoutes);
        }

        if (!$protectedRouteFile->exist()) {
            $this->generateFileFromStub(["{route}" => $protectedRoutes], $protectedRouteFile->fullPath, true, __DIR__ . '/../../stubs/api.stub');
            $this->addRouteFileToServiceProvider($protectedRouteFile, ContainerType::WEB);
            FileUtils::addImportStatement($importStatement, $protectedRouteFile);
            CubeLog::add(new SuccessMessage("Auth Web Protected Routes Has Been Added Successfully To : [{$protectedRouteFile->fullPath}]"));
        } else {
            $protectedRoutes = explode(";", $protectedRoutes);
        }

        if (is_array($protectedRoutes)) {
            foreach ($protectedRoutes as $protectedRoute) {
                if (!FileUtils::contentExistInFile($protectedRouteFile, $protectedRoute)) {
                    $protectedRouteFile->putContent("$protectedRoute;", FILE_APPEND);
                    FileUtils::addImportStatement($importStatement, $protectedRouteFile);
                    CubeLog::add(new ContentAppended($protectedRoute, $protectedRouteFile->fullPath));
                }
            }
        }

        if (is_array($publicRoutes)) {
            foreach ($publicRoutes as $publicRoute) {
                if (!FileUtils::contentExistInFile($publicRouteFile, $publicRoute)) {
                    $publicRouteFile->putContent("$publicRoute;", FILE_APPEND);
                    FileUtils::addImportStatement($importStatement, $publicRouteFile);
                    CubeLog::add(new ContentAppended($publicRoute, $publicRouteFile->fullPath));
                }
            }
        }
    }
}
