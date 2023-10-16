<?php

namespace Cubeta\CubetaStarter\Commands;

use Illuminate\Console\Command;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\RouteFileTrait;
use Cubeta\CubetaStarter\Traits\RolePermissionTrait;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InitialProject extends Command
{
    use AssistCommand;
    use RouteFileTrait;
    use RolePermissionTrait;

    protected $description = 'Prepare the necessary files to work with the package';

    protected $signature = 'cubeta-init {useGui?} {installSpatie?} {rolesPermissionsArray?} {authenticated?} {roleContainer?}';


    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $useGui = $this->argument('useGui') ?? false;
        $installSpatie = $this->argument('installSpatie') ?? false;
        $rolesPermissionsArray = $this->argument('rolesPermissionsArray') ?? false;
        $authenticated = $this->argument('authenticated') ?? [];
        $roleContainer = $this->argument('roleContainer') ?? [];

        if ($useGui) {
            if ($installSpatie) {
                $this->installSpatie(true);
            }
            if ($rolesPermissionsArray) {
                $this->handleRolesPermissionsArray($rolesPermissionsArray, $authenticated, $roleContainer);
            }

            return;
        }

        $this->handleActorsExistenceAsQuestionsInput();
    }

    /**
     * @param  mixed                      $role
     * @param  array|null                 $permissions
     * @param  array                      $authenticated
     * @param  array                      $roleContainer
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function generateActorsFiles(mixed $role, ?array $permissions, array $authenticated = [], array $roleContainer = []): void
    {
        $this->createRolesEnum($role, $permissions);

        if (in_array($roleContainer[$role], ['web', 'both'])) {
            if (!file_exists(base_path("routes/web/{$role}.php"))) {
                $this->addRouteFile($role, 'web');
            }
        }

        if (in_array($roleContainer[$role], ['api', 'both'])) {
            if (!file_exists(base_path("routes/api/{$role}.php"))) {
                $this->addRouteFile($role, 'api');
            }
        }

        $this->createRoleSeeder();
        $this->createPermissionSeeder();
        $this->generateAuthControllers($role, $authenticated, $roleContainer);

        $this->info("{$role} role created successfully");
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
            $this->installSpatie();

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

                $container = $this->choice("What is the container of this actor ? ", ['api', 'web', 'both'], 'api');
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

    /**
     * Handle the roles and permissions passed as arguments
     *
     * @param  array                      $rolesPermissions
     * @param  array                      $authenticated
     * @param  array                      $roleContainer
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
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    private function generateAuthControllers(string $role, array $authenticated = [], array $roleContainer = [])
    {
        if (in_array($role, $authenticated)) {
            if (in_array($roleContainer[$role], ['api', 'both'])) {
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

                if (!file_exists(base_path("routes/api/{$role}.php"))) {
                    return;
                }

                $routes = file_get_contents(__DIR__ . '/stubs/Auth/auth-api-routes.stub');

                $routes = str_replace('{role}', $role, $routes);
                $routeFilePath = base_path("routes/api/{$role}.php");
                $importStatement = "use " . config('cubeta-starter.api_controller_namespace') . ";";

                file_put_contents($routeFilePath, $routes, FILE_APPEND);
                addImportStatement($importStatement, $routeFilePath);
                $this->info("{$role} auth routes has been added");

                $this->addPostmanAuthCollection($role);
                $this->info("Auth collection added to postman");
            }
        }
    }

    private function addPostmanAuthCollection(string $role)
    {
        $authPostmanEntity = file_get_contents(__DIR__ . "/stubs/Auth/auth-postman-entity.stub");
        $authPostmanEntity = str_replace("{role}", $role, $authPostmanEntity);

        $projectName = config('cubeta-starter.project_name');
        $collectionDirectory = base_path(config('cubeta-starter.postman_collection _path'));
        ensureDirectoryExists($collectionDirectory);
        $collectionPath = "{$collectionDirectory}/{$projectName}.postman_collection.json";

        if (File::exists($collectionPath)) {
            $collection = file_get_contents($collectionPath);

            if (Str::contains(preg_replace('/\s+/', '', $collection), trim("\"name\":\"{$role} auth\","))) {
                $this->error('An endpoint for ' . $role . 'Auth Controller Endpoint is Already Exists in the Postman collection');

                return;
            }

            $collection = str_replace('"// add-your-cruds-here"', $authPostmanEntity, $collection);
            file_put_contents($collectionPath, $collection);
        } else {
            $projectURL = config('cubeta-starter.project_url') ?? "http://localhost/" . config('cubeta-starter.project_name') . "/public/";
            $collectionStub = file_get_contents(__DIR__ . '/stubs/postman-collection.stub');
            $collectionStub = str_replace(
                ['{projectName}', '{project-url}', '// add-your-cruds-here'],
                [$projectName, $projectURL, $authPostmanEntity],
                $collectionStub
            );
            file_put_contents($collectionPath, $collectionStub);
        }

        $this->info("Created Postman Collection: {$projectName}.postman_collection.json ");
    }
}
