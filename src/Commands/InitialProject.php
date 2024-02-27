<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\App\Models\Postman\Postman;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\RolePermissionTrait;
use Cubeta\CubetaStarter\Traits\RouteFileTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

class InitialProject extends Command
{
    use AssistCommand;
    use RouteFileTrait;
    use RolePermissionTrait;

    protected $description = 'Prepare the necessary files to work with the package';

    protected $signature = 'cubeta-init {useGui?} {rolesPermissionsArray?} {authenticated?} {roleContainer?}';


    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $useGui = $this->argument('useGui') ?? false;
        $rolesPermissionsArray = $this->argument('rolesPermissionsArray') ?? false;
        $authenticated = $this->argument('authenticated') ?? [];
        $roleContainer = $this->argument('roleContainer') ?? [];

        $spatiePublishCommand = 'php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"';
        $this->line($this->executeCommandInTheBaseDirectory($spatiePublishCommand));

        if ($useGui) {
            if ($rolesPermissionsArray) {
                $this->handleRolesPermissionsArray($rolesPermissionsArray, $authenticated, $roleContainer);
            }
            return;
        }

        $this->handleActorsExistenceAsQuestionsInput();
    }

    /**
     * Handle the roles and permissions passed as arguments
     *
     * @param array $rolesPermissions
     * @param array $authenticated
     * @param array $roleContainer
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function handleRolesPermissionsArray(array $rolesPermissions, array $authenticated = [], array $roleContainer = []): void
    {
        foreach ($rolesPermissions as $role => $permissions) {
            $this->generateActorsFiles($role, $permissions, $authenticated, $roleContainer);
        }
    }

    /**
     * @param mixed $role
     * @param array|null $permissions
     * @param array $authenticated
     * @param array $roleContainer
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateActorsFiles(mixed $role, ?array $permissions, array $authenticated = [], array $roleContainer = []): void
    {
        $this->createRolesEnum($role, $permissions);

        if (in_array($roleContainer[$role], [ContainerType::WEB, ContainerType::BOTH])) {
            if (!file_exists(base_path("routes/v1/web/{$role}.php"))) {
                $this->addRouteFile($role, ContainerType::WEB);
            }
        }

        if (in_array($roleContainer[$role], [ContainerType::API, ContainerType::BOTH])) {
            if (!file_exists(base_path("routes/v1/api/{$role}.php"))) {
                $this->addRouteFile($role, ContainerType::API);
            }
        }

        $this->createRoleSeeder();
        $this->createPermissionSeeder();
        $this->generateAuthControllers($role, $authenticated, $roleContainer);

        $this->info("{$role} role created successfully");
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    private function generateAuthControllers(string $role, array $authenticated = [], array $roleContainer = []): void
    {
        if (in_array($role, $authenticated)) {
            if (in_array($roleContainer[$role], [ContainerType::API, ContainerType::BOTH])) {
                $apiControllerNamespace = config('cubeta-starter.api_controller_namespace');
                $apiServiceNamespace = config('cubeta-starter.service_namespace');
                $apiControllerDirectory = base_path(config('cubeta-starter.api_controller_path'));
                $controllerName = ucfirst(Str::studly($role)) . "AuthController";
                ensureDirectoryExists($apiControllerDirectory);

                $stubProperties = [
                    '{namespace}' => $apiControllerNamespace,
                    '{serviceNamespace}' => $apiServiceNamespace,
                    '{role}' => ucfirst(Str::studly($role)),
                    '{roleEnumName}' => roleNaming($role)
                ];

                $controllerPath = "{$apiControllerDirectory}/{$controllerName}.php";

                if (file_exists($controllerPath)) {
                    $this->error('Controller Already Exists');
                    return;
                }
                generateFileFromStub($stubProperties, "{$controllerPath}", __DIR__ . '/stubs/Auth/auth-controller.stub');
                $this->info("Created Controller : {$controllerName}");

                if (!file_exists(base_path("routes/v1/api/{$role}.php"))) {
                    $this->warn("$role API Route File Doesn't Exist \n");
                    return;
                }

                $routes = file_get_contents(__DIR__ . '/stubs/Auth/auth-api-routes.stub');

                $routes = str_replace('{role}', $role, $routes);
                $routes = str_replace("{controllerName}", ucfirst($role), $routes);
                $routeFilePath = base_path("routes/v1/api/{$role}.php");
                $importStatement = "use " . config('cubeta-starter.api_controller_namespace') . ";";

                file_put_contents($routeFilePath, $routes, FILE_APPEND);
                addImportStatement($importStatement, $routeFilePath);
                $this->info("{$role} auth routes has been added");

                $this->addPostmanAuthCollection($role);
                $this->info("Auth collection added to postman");
            }
        }
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    private function addPostmanAuthCollection(string $role): void
    {
        $collection = Postman::make()->getCollection()->newAuthApi($role)->save();
        $this->info("Created Postman Collection: {$collection->name}.postman_collection.json ");
    }

    /**
     * Handle the actors and initialize them based on user input
     *
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handleActorsExistenceAsQuestionsInput(): void
    {
        $hasActors = $this->choice('Does your project have multiple actors?', ['No', 'Yes'], 'No') == 'Yes';

        if ($hasActors) {
            $actorsNumber = $this->ask('How many actors are there?', 2);

            while (!is_numeric($actorsNumber) || $actorsNumber < 0) {
                $this->error('Invalid input');
                $actorsNumber = $this->ask('How many actors are there?', 2);
            }

            $authenticated = [];
            $roleContainer = [];

            for ($i = 0; $i < $actorsNumber; $i++) {
                $this->info("Actor Number: {$i}");

                $role = $this->ask('What is the name of this actor? (e.g., admin, customer)');

                while (empty(trim($role))) {
                    $this->error('Invalid input');
                    $role = $this->ask('What is the name of this actor? (e.g., admin, customer)');
                }

                $hasPermission = $this->choice('Does this actor have permissions? (e.g., can-edit, can-read, can-publish)', ['No', 'Yes'], 'No') == 'Yes';

                $permissions = null;
                if ($hasPermission) {
                    $permissionsString = $this->ask("What are the permissions for this actor? (e.g., can-edit, can-read, can-publish)");

                    while (empty(trim($permissionsString))) {
                        $this->error('Invalid input');
                        $permissionsString = $this->ask("What are the permissions for this actor? (e.g., can-edit, can-read, can-publish)");
                    }

                    $permissions = $this->convertInputStringToArray($permissionsString);
                }

                $isAuthenticated = $this->choice("Do you want us to generate an authentication controller for this actor ? (note: you need to run <<<php artisan init-auth>>> if you hit yes)", ['No', 'Yes'], 'yes');

                if ($isAuthenticated == 'Yes') {
                    $authenticated[] = $role;
                }

                $container = $this->choice("What is the container of this actor ? ", ContainerType::ALL, ContainerType::API);
                $roleContainer[$role] = $container;

                $this->generateActorsFiles($role, $permissions, $authenticated, $roleContainer);
            }
        }
    }

    /**
     * Ask about the need for Spatie permissions and install it
     */
    public function installSpatie(bool $skipQuestions = false): void
    {
        $spatiePublishCommand = 'php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"';

        // this mean that the user using the web ui
        if ($skipQuestions) {
            $install = 'Yes';
        } else {
            $install = $this->choice('Using multiple actors requires installing "spatie/permission". Do you want to install it?', ['No', 'Yes'], 'No');
        }

        if ($install == 'Yes') {
            $this->info('Please wait while spatie/laravel-permission is being installed');
            $this->line($this->executeCommandInTheBaseDirectory('composer require spatie/laravel-permission'));
            $this->line($this->executeCommandInTheBaseDirectory($spatiePublishCommand));
            $this->warn("Don't forget to run 'php artisan migrate'");
        }
    }
}
