<?php

namespace Cubeta\CubetaStarter\app\Http\Controllers;

use Artisan;
use Exception;
use Illuminate\Support\Facades\Session;
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
        $result = $this->convertRolesPermissionArrayToCommandAcceptableFormat($roles);

        if (!$result) {
            return redirect()->route('cubeta-starter.generate-add-actor.page', ['error' => 'Invalid Role Name']);
        }
        Artisan::call('cubeta-init', [
            'useGui' => true,
            'rolesPermissionsArray' => $result,
            'installSpatie' => false
        ]);

        return redirect()->route('cubeta-starter.generate-add-actor.page', ['success' => 'New Roles Added']);

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
            if (Str::contains($output, ErrorTypeEnum::ALL_ERRORS, true)) {
                $lines = explode(PHP_EOL, $output);
                $lastLine = trim(array_pop($lines));
                return redirect()->route($command['route'], ['error' => explode("\n", $lastLine)]);
            }

            return redirect()->route($command['route'], ['success' => $command['name'] . ' successfully']);
        } catch (Exception $exception) {
            Session::put('error', $exception->getMessage());
            return redirect()->route($command['route']);
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
        set_time_limit(0);

        Artisan::call('cubeta-init', [
            'useGui' => true,
            'installSpatie' => true,
            'rolesPermissionsArray' => null
        ]);

        return redirect()->route('cubeta-starter.generate-add-actor.page', ['success' => "Spatie Has Been Installed \n Don't Forgot To Run Your Migrations"]);
    }

    public function installingWebPackages()
    {
        set_time_limit(0);
        try {
            Artisan::call('init-web-packages');
            return redirect()->route('cubeta-starter.complete-installation', ['success' => 'The Packages Have Been Installed Successfully']);
        } catch (Exception $e) {
            return redirect()->route('cubeta-starter.complete-installation', ['error' => $e->getMessage()]);
        }
    }

    public function publishAssets()
    {
        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'cubeta-starter-assets',
            ]);
            return redirect()->route('cubeta-starter.complete-installation', ['success' => 'The Assets Has Been Published Successfully']);
        } catch (Exception $e) {
            return redirect()->route('cubeta-starter.complete-installation', ['error' => $e->getMessage()]);
        }
    }

    public function publishConfig()
    {
        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'cubeta-starter-config',
            ]);
            return redirect()->route('cubeta-starter.complete-installation', ['success' => 'Config File Published Successfully']);
        } catch (Exception $e) {
            return redirect()->route('cubeta-starter.complete-installation', ['error' => $e->getMessage()]);
        }
    }

    public function publishHandler()
    {
        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'cubeta-starter-handler',
                '--force' => true
            ]);
            return redirect()->route('cubeta-starter.complete-installation', ['success' => 'Exception Handler Published Successfully']);
        } catch (Exception $e) {
            return redirect()->route('cubeta-starter.complete-installation', ['error' => $e->getMessage()]);
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
        $result = [];

        foreach ($rolesPermissionArray as $array) {
            $result[$array['name']] = $this->convertInputStringToArray($array['permissions']);
        }

        foreach ($result as $role => $permissions) {
            if (empty(trim($role))) {
                return false;
            }
        }

        return $result;
    }
}
