<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Traits\RouteFileTrait;
use Illuminate\Console\Command;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Artisan;

class InitAuth extends Command
{
    use AssistCommand, RouteFileTrait;

    protected $description = 'initialize authentication tools';

    protected $signature = 'init-auth {container?}';

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $container = $this->argument('container') ?? 'both';

        $this->generateUserMigration();
        $this->generateUserModel();

        if ($container == 'api' || $container == 'both') {
            $this->generateUserResource();
            $this->generateBaseAuthController();
        }

        if ($container == 'web' || $container == 'both') {
            $this->generateBaseAuthWebController();
            $this->generateWebAuthRouteFile();
            Artisan::call('vendor:publish', [
                '--tag' => 'cubeta-auth-views',
                '--force' => true
            ]);
        }
        $this->generateUserService($container);
        $this->generateUserRepository();
        $this->generateAuthRequests();
        $this->generateResetPasswordNotification();
        $this->generateResetPasswordEmailView();
        $this->generateUserFactory();
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateUserRepository(): void
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.repository_namespace'),
            '{modelNamespace}' => config('cubeta-starter.model_namespace'),
        ];

        $repositoryDirectory = base_path(config('cubeta-starter.repository_path'));
        $repositoryPath = "$repositoryDirectory/UserRepository.php";

        ensureDirectoryExists($repositoryDirectory);

        generateFileFromStub(
            $stubProperties,
            $repositoryPath,
            __DIR__ . '/stubs/Auth/UserRepository.stub',
            true
        );

        $this->info("Created Repository: UserRepository");
    }

    /**
     * @param string $container
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateUserService(string $container = 'api'): void
    {
        // user service
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.service_namespace'),
            '{resourceNamespace}' => config('cubeta-starter.resource_namespace'),
            '{repositoryNamespace}' => config('cubeta-starter.repository_namespace'),
        ];

        $serviceDirectory = base_path(config('cubeta-starter.service_path') . '/User');

        if ($container == 'api' || $container == 'both') {
            $servicePath = "$serviceDirectory/UserService.php";

            ensureDirectoryExists($serviceDirectory);

            generateFileFromStub($stubProperties, $servicePath, __DIR__ . '/stubs/Auth/UserService.stub', true);

            $this->info("Created Service: UserService");
        }
        if ($container == 'web' || $container == 'both') {
            $servicePath = "$serviceDirectory/UserWebService.php";

            ensureDirectoryExists($serviceDirectory);

            generateFileFromStub($stubProperties, $servicePath, __DIR__ . '/stubs/Auth/UserWebService.stub', true);

            $this->info("Created Service: UserWebService");
        }

        // service interface
        $interfacePath = "$serviceDirectory/IUserService.php";
        generateFileFromStub([
            '{namespace}' => config('cubeta-starter.service_namespace')
        ], $interfacePath, __DIR__ . '/stubs/Auth/IUserService.stub', true);

        $this->info("Created Service Interface: IUserService");
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateUserModel(): void
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.model_namespace'),
        ];

        $modelDirectory = base_path(config('cubeta-starter.model_path'));
        $modelPath = "$modelDirectory/User.php";

        ensureDirectoryExists($modelDirectory);

        generateFileFromStub($stubProperties, $modelPath, __DIR__ . '/stubs/Auth/UserModel.stub', true);

        $this->info("Created Model: User");
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateUserMigration(): void
    {
        $migrationPath = base_path(config('cubeta-starter.migration_path') . '/2014_10_12_000000_create_users_table.php');
        generateFileFromStub([], $migrationPath, __DIR__ . '/stubs/Auth/UserMigration.stub', true);

        $this->info("Created Migration: 2014_10_12_000000_create_users_table.php");
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateBaseAuthController(): void
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.api_controller_namespace'),
            '{requestsNamespace}' => config('cubeta-starter.request_namespace'),
            '{ServiceNameSpace}' => config('cubeta-starter.service_namespace'),
            '{resourceNamespace}' => config('cubeta-starter.resource_namespace'),
        ];

        $controllerDirectory = base_path(config('cubeta-starter.api_controller_path'));
        $controllerPath = "$controllerDirectory/BaseAuthController.php";

        ensureDirectoryExists($controllerDirectory);

        generateFileFromStub($stubProperties, $controllerPath, __DIR__ . '/stubs/Auth/BaseAuthController.stub', true);

        $this->info("Created Controller: BaseAuthController");
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateAuthRequests(): void
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.request_namespace'),
        ];

        $requestDirectory = base_path(config('cubeta-starter.request_path') . '/AuthRequests');

        ensureDirectoryExists($requestDirectory);

        generateFileFromStub($stubProperties, "$requestDirectory/AuthLoginRequest.php", __DIR__ . '/stubs/Auth/AuthRequests/AuthLoginRequest.stub', true);
        generateFileFromStub($stubProperties, "$requestDirectory/AuthRegisterRequest.php", __DIR__ . '/stubs/Auth/AuthRequests/AuthRegisterRequest.stub', true);
        generateFileFromStub($stubProperties, "$requestDirectory/CheckPasswordResetRequest.php", __DIR__ . '/stubs/Auth/AuthRequests/CheckPasswordResetRequest.stub', true);
        generateFileFromStub($stubProperties, "$requestDirectory/RequestResetPasswordRequest.php", __DIR__ . '/stubs/Auth/AuthRequests/RequestResetPasswordRequest.stub', true);
        generateFileFromStub($stubProperties, "$requestDirectory/ResetPasswordRequest.php", __DIR__ . '/stubs/Auth/AuthRequests/ResetPasswordRequest.stub', true);
        generateFileFromStub($stubProperties, "$requestDirectory/UpdateUserRequest.php", __DIR__ . '/stubs/Auth/AuthRequests/UpdateUserRequest.stub', true);

        $this->info("Created Requests: AuthRequests");
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateUserResource(): void
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.resource_namespace'),
        ];

        $resourceDirectory = base_path(config('cubeta-starter.resource_path'));
        $resourcePath = "$resourceDirectory/UserResource.php";

        ensureDirectoryExists($resourceDirectory);

        generateFileFromStub($stubProperties, $resourcePath, __DIR__ . '/stubs/Auth/UserResource.stub', true);

        $this->info("Created Resource: UserResource");
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateResetPasswordNotification(): void
    {
        $notificationDirectory = base_path('app/Notifications');
        ensureDirectoryExists($notificationDirectory);
        generateFileFromStub([], "$notificationDirectory/ResetPasswordCodeEmail.php", __DIR__ . '/stubs/Auth/ResetPasswordCodeEmail.stub', true);
        $this->info("Created Notification: ResetPasswordCodeEmail");
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateResetPasswordEmailView(): void
    {
        $viewDirectory = resource_path('views/emails');
        ensureDirectoryExists($viewDirectory);
        generateFileFromStub([], "$viewDirectory/reset-password-email.blade.php", __DIR__ . '/stubs/Auth/reset-password.stub', true);
        $this->info("Created View: reset-password");
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateUserFactory(): void
    {
        $factoryDirectory = base_path(config('cubeta-starter.factory_path'));
        ensureDirectoryExists($factoryDirectory);
        $factoryPath = "$factoryDirectory/UserFactory.php";

        generateFileFromStub([], $factoryPath, __DIR__ . '/stubs/Auth/user-factory.stub', true);
        $this->info("Created Factory: UserFactory");
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateBaseAuthWebController(): void
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.web_controller_namespace'),
            '{requestsNamespace}' => config('cubeta-starter.request_namespace'),
            '{ServiceNameSpace}' => config('cubeta-starter.service_namespace')
        ];

        $controllerDirectory = base_path(config('cubeta-starter.web_controller_path'));
        $controllerPath = "$controllerDirectory/BaseAuthController.php";

        ensureDirectoryExists($controllerDirectory);

        generateFileFromStub($stubProperties, $controllerPath, __DIR__ . '/stubs/Auth/BaseAuthWebController.stub', true);

        $this->info("Created Controller: BaseAuthController");
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateWebAuthRouteFile(): void
    {
        $routeFileName = "dashboard-auth.php";
        $routeFilePath = "v1/web/$routeFileName";
        ensureDirectoryExists(base_path("routes/v1/web/"));
        generateFileFromStub(
            [
                '{controllerNamespace}' => config('cubeta-starter.web_controller_namespace')
            ],
            base_path("routes/$routeFilePath"),
            __DIR__ . '/stubs/Auth/auth-web-routes.stub');

        $this->addRouteFileToServiceProvider($routeFilePath, ContainerType::WEB);
    }
}
