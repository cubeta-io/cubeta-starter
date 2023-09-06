<?php

namespace Cubeta\CubetaStarter\App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Cubeta\CubetaStarter\Enums\ErrorTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;

class CallAppropriateCommand extends Controller
{
    use  AssistCommand;

    public Request $request;
    private mixed $actor;
    private mixed $columns;
    private mixed $container;

    private mixed $modelName;
    private mixed $nullables;
    private mixed $relations;
    private mixed $uniques;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->modelName = $request->model_name;
        $this->relations = $this->configureRequestArray($request->relations);
        $this->columns = $this->configureRequestArray($request->columns);
        $this->actor = $request->actor;
        $this->container = $request->containerType;
        $this->nullables = $request->nullables;
        $this->uniques = $request->uniques;
    }

    public function callAddActorCommand(Request $request)
    {
        $roles = $request->roles ?? [];
        $authenticated = $request->authenticated ?? [];

        $result = $this->convertRolesPermissionArrayToCommandAcceptableFormat($roles);
        if (!$result) {
            return redirect()->route('cubeta-starter.generate-add-actor.page', ['error' => 'Invalid Role Name']);
        }

        $rolesPermissions = $result['rolesPermissions'];
        $roleContainer = $result['roleContainer'];

        Artisan::call('cubeta-init', [
            'useGui' => true,
            'rolesPermissionsArray' => $rolesPermissions,
            'installSpatie' => false,
            'roleContainer' => $roleContainer,
            'authenticated' => $authenticated,
        ]);

        $output = Artisan::output();
        return $this->handleWarningAndLogsBeforeRedirecting($output, 'cubeta-starter.generate-add-actor.page', "New Roles Added");
    }

    public function callCommand($command)
    {
        set_time_limit(0);
        try {

            if (empty(trim($this->modelName))) {
                return redirect()->route($command['route'], ['error' => "Invalid Model Name"]);
            }

            $arguments['name'] = $this->modelName;

            $tempColsArray = [];
            if (isset($this->columns) && count($this->columns) > 0) {
                foreach ($this->columns as $col => $type) {
                    if (empty(trim($col))) {
                        return redirect()->route($command['route'], ['error' => "Invalid Column Name"]);
                    }
                    $tempColsArray[columnNaming($col)] = $type;
                }
                $arguments['attributes'] = $tempColsArray;
            }

            if (isset($this->relations) && count($this->relations) > 0) {
                foreach ($this->relations as $relation => $type) {
                    if (empty(trim($relation))) {
                        return redirect()->route($command['route'], ['error' => "Invalid Relation Name"]);
                    }
                }
                $arguments['relations'] = $this->relations;
            }

            if (isset($this->nullables) && count($this->nullables) > 0) {
                foreach ($this->nullables as $key => &$value) {
                    $value = columnNaming($value);
                }
                $arguments['nullables'] = $this->nullables;
            }

            if (isset($this->uniques) && count($this->uniques) > 0) {
                foreach ($this->uniques as $key => &$value) {
                    $value = columnNaming($value);
                }
                $arguments['uniques'] = $this->uniques;
            }

            if (isset($this->actor)) {
                $arguments['actor'] = $this->actor;
            }

            if ($command['name'] == 'create:model') {
                $arguments['gui'] = true;
                $arguments['container'] = $this->container;
            }

            if (isset($this->container) && !in_array($this->container, ['api', 'web', 'both'])) {
                return redirect()->route($command['route'], ['error' => "Invalid container name"]);
            }

            Artisan::call($command['name'], $arguments);

            $output = Artisan::output();

            return $this->handleWarningAndLogsBeforeRedirecting($output, $command['route'], $command['name'] . ' successfully');

        } catch (Exception $exception) {
            $error = $exception->getMessage();
            if (strlen($error) <= 30) {
                return redirect()->route($command['route'], ['error' => $error]);
            }
            return view('CubetaStarter::command-output', compact('error'));

        }
    }

    public function callCreateApiControllerCommand()
    {
        $command = [
            'name' => 'create:controller',
            'route' => 'cubeta-starter.generate-api-controller.page'
        ];

        return $this->callCommand($command);
    }

    public function callCreateFactoryCommand()
    {
        $command = [
            'name' => 'create:factory',
            'route' => 'cubeta-starter.generate-factory.page'
        ];

        return $this->callCommand($command);
    }

    public function callCreateMigrationCommand()
    {
        $command = [
            'name' => 'create:migration',
            'route' => 'cubeta-starter.generate-migration.page'
        ];

        return $this->callCommand($command);
    }

    public function callCreateModelCommand()
    {
        $command = [
            'name' => 'create:model',
            'route' => 'cubeta-starter.generate-full.page'
        ];

        return $this->callCommand($command);
    }

    public function callCreatePolicyCommand()
    {
        $command = [
            'name' => 'create:policy',
            'route' => 'cubeta-starter.generate-policy.page'
        ];

        return $this->callCommand($command);
    }

    public function callCreatePostmanCollectionCommand()
    {
        $command = [
            'name' => 'create:postman-collection',
            'route' => 'cubeta-starter.generate-postman-collection.page'
        ];

        return $this->callCommand($command);
    }

    public function callCreateRepositoryCommand()
    {
        $command = [
            'name' => 'create:repository',
            'route' => 'cubeta-starter.generate-repository.page'
        ];

        return $this->callCommand($command);
    }

    public function callCreateRequestCommand()
    {
        $command = [
            'name' => 'create:request',
            'route' => 'cubeta-starter.generate-request.page'
        ];

        return $this->callCommand($command);
    }

    public function callCreateResourceCommand()
    {
        $command = [
            'name' => 'create:resource',
            'route' => 'cubeta-starter.generate-resource.page'
        ];

        return $this->callCommand($command);
    }

    public function callCreateSeederCommand()
    {
        $command = [
            'name' => 'create:seeder',
            'route' => 'cubeta-starter.generate-seeder.page'
        ];

        return $this->callCommand($command);
    }

    public function callCreateServiceCommand()
    {
        $command = [
            'name' => 'create:service',
            'route' => 'cubeta-starter.generate-service.page'
        ];

        return $this->callCommand($command);
    }

    public function callCreateTestCommand()
    {
        $command = [
            'name' => 'create:test',
            'route' => 'cubeta-starter.generate-test.page'
        ];

        return $this->callCommand($command);
    }

    public function callCreateWebControllerCommand()
    {
        $command = [
            'name' => 'create:web-controller',
            'route' => 'cubeta-starter.generate-web-controller.page'
        ];

        return $this->callCommand($command);
    }

    public function callInstallSpatie()
    {
        try {
            set_time_limit(0);

            Artisan::call('cubeta-init', [
                'useGui' => true,
                'installSpatie' => true,
                'rolesPermissionsArray' => null
            ]);

            $output = Artisan::output();
            return $this->handleWarningAndLogsBeforeRedirecting($output, 'cubeta-starter.generate-add-actor.page', "Spatie Has Been Installed \n Don't Forgot To Run Your Migrations");

        } catch (Exception $e) {
            $error = $e->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    public function installingWebPackages()
    {
        set_time_limit(0);
        try {
            Artisan::call('init-web-packages');
            $output = Artisan::output();
            return $this->handleWarningAndLogsBeforeRedirecting($output, 'cubeta-starter.complete-installation', 'The Packages Have Been Installed Successfully');

        } catch (Exception $e) {
            $error = $e->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    public function publishAssets()
    {
        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'cubeta-starter-assets',
            ]);

            if (file_exists(base_path('app/Http/Controllers/SetLocaleController.php'))) {
                $route = "Route::post('/locale', [\App\Http\Controllers\SetLocaleController::class, 'setLanguage'])->middleware('web')->name('set-locale');";
            } else {
                $route = "Route::post('/blank', function () {
                    return response()->noContent();
                })->middleware('web')->name('set-locale');";
            }

            $routePath = base_path("routes/web.php");

            if (file_exists($routePath)) {
                file_put_contents($routePath, $route, FILE_APPEND);
            }

            $output = Artisan::output();
            return $this->handleWarningAndLogsBeforeRedirecting($output, 'cubeta-starter.complete-installation', 'The Assets Has Been Published Successfully');
        } catch (Exception $e) {
            $error = $e->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    public function publishConfig()
    {
        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'cubeta-starter-config',
            ]);

            $output = Artisan::output();
            return $this->handleWarningAndLogsBeforeRedirecting($output, 'cubeta-starter.complete-installation', 'Config File Published Successfully');

        } catch (Exception $e) {
            $error = $e->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    public function publishHandler()
    {
        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'cubeta-starter-handler',
                '--force' => true
            ]);

            $output = Artisan::output();
            return $this->handleWarningAndLogsBeforeRedirecting($output, 'cubeta-starter.complete-installation', 'Exception Handler Published Successfully');

        } catch (Exception $e) {
            $error = $e->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    private function configureRequestArray($array = null)
    {
        if (!isset($array) || $array == []) {
            return [];
        }

        return collect($array)->mapWithKeys(function ($item) {
            return [$item['name'] => $item['type']];
        })->toArray();
    }

    private function convertRolesPermissionArrayToCommandAcceptableFormat(array $rolesPermissionArray = [])
    {
        $rolesPermissions = [];
        $roleContainer = [];

        foreach ($rolesPermissionArray as $array) {
            $rolesPermissions[$array['name']] = $this->convertInputStringToArray($array['permissions']);
            $roleContainer[$array['name']] = $array['container'];
        }

        foreach ($rolesPermissions as $role => $permissions) {
            if (empty(trim($role))) {
                return false;
            }
        }

        return ['rolesPermissions' => $rolesPermissions, 'roleContainer' => $roleContainer];
    }

    private function handleWarningAndLogsBeforeRedirecting($output, $redirectRouteName, $successMessage)
    {
        foreach (ErrorTypeEnum::ALL_ERRORS as $error) {
            if (Str::contains($output, $error, true)) {
                Cache::put('logs', $output);
                return redirect()->route($redirectRouteName, ['warning' => $error]);
            }
        }
        Cache::put('logs', $output);
        return redirect()->route($redirectRouteName, ['success' => $successMessage]);
    }

    public function initAuth($container = null)
    {
        try {
            Artisan::call('init-auth', [
                'container' => $container
            ]);

            $output = Artisan::output();
            return $this->handleWarningAndLogsBeforeRedirecting($output, 'cubeta-starter.generate-add-actor.page', 'Authentication Tools Prepared Successfully');

        } catch (Exception $e) {
            $error = $e->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    protected function callPublishCommand(string $tag)
    {
        try {
            Artisan::call('vendor:publish', [
                '--tag' => $tag,
            ]);

            $output = Artisan::output();
            return $this->handleWarningAndLogsBeforeRedirecting($output, 'cubeta-starter.publishes', 'Published Successfully');

        } catch (Exception $exception) {
            $error = $exception->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    public function publishRepositories()
    {
        return $this->callPublishCommand('cubeta-starter-repositories');
    }

    public function publishServices()
    {
        return $this->callPublishCommand('cubeta-starter-services');
    }

    public function publishApiController()
    {
        return $this->callPublishCommand('cubeta-starter-api-controller');
    }

    public function publishMiddlewares()
    {
        return $this->callPublishCommand('cubeta-starter-middlewares');
    }

    public function publishHelpers()
    {
        return $this->callPublishCommand('cubeta-starter-helpers');
    }

    public function publishValidationRules()
    {
        return $this->callPublishCommand('cubeta-starter-validation-rules');
    }

    public function publishTraits()
    {
        return $this->callPublishCommand('cubeta-starter-traits');
    }

    public function publishProviders()
    {
        return $this->callPublishCommand('cubeta-starter-providers');
    }

    public function publishAll()
    {
        $this->callPublishCommand('cubeta-starter-repositories');
        $this->callPublishCommand('cubeta-starter-services');
        $this->callPublishCommand('cubeta-starter-api-controller');
        $this->callPublishCommand('cubeta-starter-middlewares');
        $this->callPublishCommand('cubeta-starter-helpers');
        $this->callPublishCommand('cubeta-starter-validation-rules');
        $this->callPublishCommand('cubeta-starter-traits');
        return $this->callPublishCommand('cubeta-starter-providers');
    }
}
