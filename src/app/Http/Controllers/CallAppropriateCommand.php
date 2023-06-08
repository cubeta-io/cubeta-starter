<?php

namespace Cubeta\CubetaStarter\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Artisan;
use Illuminate\Http\Request;

class CallAppropriateCommand extends Controller
{
    private $modelName;
    private $relations;
    private $columns;
    private $actor;
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->modelName = $request->model_name;
        $this->relations = $this->configureRequestArray($request->relations);
        $this->columns = $this->configureRequestArray($request->columns);
        $this->actor = $request->actor;
    }

    public function callCommand($command)
    {
        if (empty(trim($this->modelName))) {
            return redirect()->route($command['route'], ['error' => "invalid input"]);
        }

        $arguments['name'] = $this->modelName;

        if (isset($this->columns) && count($this->columns) > 0) {
            $arguments['attributes'] = $this->columns;
        }

        if (isset($this->relations) && count($this->relations) > 0) {
            $arguments['relations'] = $this->relations;
        }

        if (isset($this->actor)) {
            $arguments['actor'] = $this->actor;
        }

        if ($command['name'] == 'create:model') {
            $arguments['gui'] = true;
        }

        Artisan::call($command['name'], $arguments);

        return redirect()->route($command['route'], ['success' => $command['name'] . 'successfully']);
    }

    public function callCreateModelCommand()
    {
        $command = [
            'name' => 'create:model',
            'route' => 'cubeta-starter.generate-full.page'
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

    public function callCreateFactoryCommand()
    {
        $command = [
            'name' => 'create:factory',
            'route' => 'cubeta-starter.generate-factory.page'
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

    public function callCreateRepositoryCommand()
    {
        $command = [
            'name' => 'create:repository',
            'route' => 'cubeta-starter.generate-repository.page'
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

    public function callCreateApiControllerCommand()
    {
        $command = [
            'name' => 'create:controller',
            'route' => 'cubeta-starter.generate-api-controller.page'
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

    private function configureRequestArray($array = null)
    {
        if (!isset($array) || $array == []) {
            return [];
        }

        return collect($array)->mapWithKeys(function ($item) {
            return [$item['name'] => $item['type']];
        })->toArray();
    }
}
