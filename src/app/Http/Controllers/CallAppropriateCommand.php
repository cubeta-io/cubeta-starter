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
        $this->$request = $request;
        $this->modelName = $request->model_name;
        $this->relations = $request->relations;
        $this->columns = $request->columns;
        $this->actor = $request->actor;

        $this->relations = $this->configureRequestArray($this->relations);
        $this->columns = $this->configureRequestArray($this->columns);
    }

    public function callCreateModelCommand()
    {
        if (isset($this->actor)) {
            Artisan::call('create:model', [
                'name' => $this->modelName,
                'gui' => true,
                'attributes' => $this->columns,
                'relations' => $this->relations,
                'actor' => $this->actor
            ]);
        } else {
            Artisan::call('create:model', [
                'name' => $this->modelName,
                'gui' => true,
                'attributes' => $this->columns,
                'relations' => $this->relations
            ]);
        }

        return redirect()->route('cubeta-starter.greetings');
    }

    public function callCreateMigrationCommand(Request $request)
    {
        Artisan::call('create:migration', [
            'name' => $this->modelName,
            'attributes' => $this->columns,
            'relations' => $this->relations
        ]);

        return redirect()->back();
    }

    public function callCreateFactoryCommand()
    {
        Artisan::call('create:factory', [
            'name' => $this->modelName,
            'attributes' => $this->columns,
            'relations' => $this->relations
        ]);

        return redirect()->back();
    }

    public function callCreateSeederCommand()
    {
        Artisan::call('create:seeder', [
            'name' => $this->modelName
        ]);

        return redirect()->back();
    }

    public function callCreateRepositoryCommand()
    {
        Artisan::call('create:repository', [
            'name' => $this->modelName
        ]);
        return redirect()->back();
    }

    public function callCreateServiceCommand()
    {
        Artisan::call('create:service', [
            'name' => $this->modelName
        ]);

        return redirect()->back();
    }

    public function callCreateRequestCommand()
    {
        Artisan::call('create:request', [
            'name' => $this->modelName,
            'attributes' => $this->columns,
        ]);

        return redirect()->back();
    }

    public function callCreateResourceCommand()
    {
        Artisan::call('create:resource', [
            'name' => $this->modelName,
            'attributes' => $this->columns,
            'relations' => $this->relations,
        ]);

        return redirect()->back();
    }

    public function callCreateApiControllerCommand()
    {
        Artisan::call('create:controller', [
            'name' => $this->modelName,
            'actor' => $this->actor
        ]);

        return redirect()->back();
    }

    public function callCreateTestCommand()
    {
        Artisan::call('create:test', [
            'name' => $this->modelName,
            'actor' => $this->actor
        ]);

        return redirect()->back();
    }

    public function callCreatePolicyCommand()
    {
        Artisan::call('create:policy', [
            'name' => $this->modelName
        ]);

        return redirect()->back();
    }

    public function callCreatePostmanCollectionCommand()
    {
        Artisan::call('create:postman-collection', [
            'name' => $this->modelName,
            'attributes' => $this->columns
        ]);

        return redirect()->back();
    }

    private function configureRequestArray($array = null)
    {
        if (!isset($array) || $array == []) {
            return [];
        }

        $result = [];
        foreach ($array as $item) {
            $result[$item['name']] = $item['type'];
        }
        return $result;
    }
}
