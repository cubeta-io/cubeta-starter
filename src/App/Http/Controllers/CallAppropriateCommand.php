<?php

namespace Cubeta\CubetaStarter\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\Sources\ActorFilesGenerator;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CallAppropriateCommand extends Controller
{
    use  RouteBinding;

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

    private function configureRequestArray($array = null)
    {
        if (!isset($array) || $array == []) {
            return [];
        }

        return collect($array)->mapWithKeys(fn($item) => [$item['name'] => $item['type']])->toArray();
    }

    public function callAddActorCommand(Request $request)
    {
        $roles = $request->roles ?? [];
        $authenticated = $request->authenticated ?? [];

        $result = $this->convertRolesPermissionsToArray($roles);

        if (!$result) {
            return redirect()->route('cubeta-starter.generate-add-actor.page', ['error' => 'Invalid Role Name']);
        }

        $rolesPermissions = $result['rolesPermissions'];
        $roleContainer = $result['roleContainer'];

        foreach ($rolesPermissions as $role => $permissions) {
            (new ActorFilesGenerator(
                $role,
                $permissions,
                in_array($role, $authenticated),
                $roleContainer[$role]
            ))->run();
        }

        return $this->handleLogs('cubeta-starter.generate-add-actor.page', "New Roles Added");
    }

    private function convertRolesPermissionsToArray(array $rolesPermissionArray = [])
    {
        $rolesPermissions = [];
        $roleContainer = [];

        foreach ($rolesPermissionArray as $array) {
            $rolesPermissions[$array['name']] = explode(",", $array['permissions']);
            $roleContainer[$array['name']] = $array['container'];
        }

        foreach ($rolesPermissions as $role => $permissions) {
            if (empty(trim($role))) {
                return false;
            }
        }

        return ['rolesPermissions' => $rolesPermissions, 'roleContainer' => $roleContainer];
    }

    private function handleLogs($redirectRouteName, $successMessage)
    {
        [$logs, $exceptions] = CubeLog::splitExceptions();
        Cache::put('logs', $logs);

        foreach ($exceptions as &$exception) {
            $exception = CubeLog::exceptionToHtml($exception);
        }

        Cache::put('exceptions', $exceptions);

        foreach ($logs as $log) {
            if (count($exceptions)) {
                return redirect()->route($redirectRouteName, ['error' => "Check The Logs There Was An Error While Generating"]);
            }
            if ($log instanceof CubeError || $log instanceof CubeLog) {
                $msg = $log instanceof CubeError
                    ? $log->error()
                    : $log->warning();
                return redirect()->route($redirectRouteName, ['warning' => $msg]);
            }
        }

        return redirect()->route($redirectRouteName, ['success' => $successMessage]);
    }

    public function callCreateApiControllerCommand()
    {
        $command = [
            'name' => 'create:controller',
            'route' => 'cubeta-starter.generate-api-controller.page'
        ];

        return $this->callCommand($command);
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
                foreach ($this->nullables as &$value) {
                    $value = columnNaming($value);
                }
                $arguments['nullables'] = $this->nullables;
            }

            if (isset($this->uniques) && count($this->uniques) > 0) {
                foreach ($this->uniques as &$value) {
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

            return $this->handleLogs($output, $command['route'], $command['name'] . ' successfully');

        } catch (Exception $exception) {
            $error = $exception->getMessage();
            if (strlen($error) <= 30) {
                return redirect()->route($command['route'], ['error' => $error]);
            }
            return view('CubetaStarter::command-output', compact('error'));

        }
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

    public function installingWebPackages()
    {
        set_time_limit(0);
        try {
            Artisan::call('init-web-packages');
            $output = Artisan::output();
            return $this->handleLogs($output, 'cubeta-starter.complete-installation', 'The Packages Have Been Installed Successfully');

        } catch (Exception $e) {
            $error = $e->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    public function initAuth($container = null)
    {
        try {
            Artisan::call('init-auth', [
                'container' => $container
            ]);

            $output = Artisan::output();
            return $this->handleLogs($output, 'cubeta-starter.generate-add-actor.page', 'Authentication Tools Prepared Successfully');

        } catch (Exception $e) {
            $error = $e->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    public function callPublishApi()
    {
        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'cubeta-starter-api',
            ]);

            $this->addSetLocalRoute();
            $this->addRouteFile('public', ContainerType::API);
            $this->addRouteFile('protected', ContainerType::API);

            $output = Artisan::output();
            return $this->handleLogs($output, 'cubeta-starter.complete-installation', 'Published Successfully');

        } catch (Exception $exception) {
            $error = $exception->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    public function callPublishWeb()
    {
        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'cubeta-starter-web',
            ]);

            $this->addSetLocalRoute();
            $this->addRouteFile('public', ContainerType::WEB);
            $this->addRouteFile('protected', ContainerType::WEB);

            $output = Artisan::output();
            return $this->handleLogs($output, 'cubeta-starter.complete-installation', 'Published Successfully');

        } catch (Exception $exception) {
            $error = $exception->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    public function publishAll()
    {
        try {
            Artisan::call('cubeta-publish');

            $this->addRouteFile('public', ContainerType::API);
            $this->addRouteFile('protected', ContainerType::API);
            $this->addRouteFile('public', ContainerType::WEB);
            $this->addRouteFile('protected', ContainerType::WEB);

            $output = Artisan::output();
            return $this->handleLogs($output, 'cubeta-starter.complete-installation', 'Published Successfully');
        } catch (Exception $e) {
            $error = $e->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    private function callPublishCommand(string $tag)
    {
        try {
            Artisan::call('vendor:publish', [
                '--tag' => $tag,
            ]);

            $output = Artisan::output();
            return $this->handleLogs($output, 'cubeta-starter.complete-installation', 'Published Successfully');

        } catch (Exception $exception) {
            $error = $exception->getMessage();
            return view('CubetaStarter::command-output', compact('error'));
        }
    }
}
