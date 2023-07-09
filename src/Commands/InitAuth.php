<?php

namespace Cubeta\CubetaStarter\Commands;

use Illuminate\Console\Command;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class InitAuth extends Command
{
    use AssistCommand;

    protected $description = 'initialize authentication tools';

    protected $signature = 'init-auth';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $this->generateUserMigration();
        $this->generateUserModel();
        $this->generateUserRepository();
        $this->generateUserService();
        $this->generateUserResource();
        $this->generateAuthRequests();
        $this->generateBaseAuthController();
        $this->generateResetPasswordNotification();
        $this->generateResetPasswordEmailView();
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    private function generateUserRepository()
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
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    private function generateUserService()
    {
        // user service
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.service_namespace'),
            '{resourceNamespace}' => config('cubeta-starter.resource_namespace'),
            '{repositoryNamespace}' => config('cubeta-starter.repository_namespace'),
        ];

        $serviceDirectory = base_path(config('cubeta-starter.service_path') . '/User');
        $servicePath = "$serviceDirectory/UserService.php";

        ensureDirectoryExists($serviceDirectory);

        generateFileFromStub($stubProperties, $servicePath, __DIR__ . '/stubs/Auth/UserService.stub', true);

        $this->info("Created Service: UserService");

        // service interface
        $interfacePath = "$serviceDirectory/IUserService.php";
        generateFileFromStub([
            '{namespace}' => config('cubeta-starter.service_namespace')
        ], $interfacePath, __DIR__ . '/stubs/Auth/IUserService.stub', true);

        $this->info("Created Service Interface: IUserService");
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateUserModel()
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
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    private function generateUserMigration()
    {
        $migrationPath = config('cubeta-starter.migration_path') . '/2014_10_12_000000_create_users_table.php';
        generateFileFromStub([], $migrationPath, __DIR__ . '/stubs/Auth/UserMigration.stub', true);

        $this->info("Created Migration: 2014_10_12_000000_create_users_table.php");
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    private function generateBaseAuthController()
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.api_controller_namespace'),
            '{requestsNamespace}' => config('cubeta-starter.request_namespace'),
            '{ServiceNameSpace}' => config('cubeta-starter.service_namespace')
        ];

        $controllerDirectory = base_path(config('cubeta-starter.api_controller_path'));
        $controllerPath = "$controllerDirectory/BaseAuthController.php";

        ensureDirectoryExists($controllerDirectory);

        generateFileFromStub($stubProperties, $controllerPath, __DIR__ . '/stubs/Auth/BaseAuthController.stub', true);

        $this->info("Created Controller: BaseAuthController");
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateAuthRequests()
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
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function generateUserResource()
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
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    private function generateResetPasswordNotification()
    {
        $notificationDirectory = 'app/Notifications';
        ensureDirectoryExists($notificationDirectory);
        generateFileFromStub([], "$notificationDirectory/ResetPasswordCodeEmail.php", __DIR__ . '/stubs/Auth/ResetPasswordCodeEmail.stub', true);
        $this->info("Created Notification: ResetPasswordCodeEmail");
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    private function generateResetPasswordEmailView()
    {
        $viewDirectory = resource_path('views/emails');
        ensureDirectoryExists($viewDirectory);
        generateFileFromStub([], "$viewDirectory/reset-password.blade.php", __DIR__ . '/stubs/Auth/ResetPasswordCodeEmail.stub', true);
        $this->info("Created View: reset-password");
    }
}
