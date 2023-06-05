<?php

namespace Cubeta\CubetaStarter\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Artisan;
use Illuminate\Http\Request;

class CreateModelController extends Controller
{
    public function createModelPage()
    {
        $types = [
            'integer', 'bigInteger', 'unsignedBigInteger',
            'unsignedDouble', 'double', 'float',
            'string', 'json', 'text',
            'boolean', 'date', 'time',
            'dateTime', 'timestamp', 'file',
            'key', 'translatable'
        ];

        $roles = [];

        if (file_exists(base_path('app/Enums/RolesPermissionEnum.php'))) {
            $roles = \App\Enums\RolesPermissionEnum::ALLROLES;
        }

        return view('CubetaStarter::create-model', compact('types', 'roles'));
    }

    public function callCreateModelCommand(Request $request)
    {
        $modelName = $request->model_name;
        $relations = $this->configureRequestArray($request->relations);
        $columns = $this->configureRequestArray($request->columns);
        $actor = $request->actor;

        if (isset($actor)) {
            Artisan::call('create:model', [
                'name' => $modelName,
                'gui' => true,
                'attributes' => $columns,
                'relations' => $relations,
                'actor' => $actor
            ]);
        } else {
            Artisan::call('create:model', [
                'name' => $modelName,
                'gui' => true,
                'attributes' => $columns,
                'relations' => $relations
            ]);
        }

        return redirect()->route('cubeta-starter.greetings');
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
