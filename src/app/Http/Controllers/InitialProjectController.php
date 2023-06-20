<?php

namespace Cubeta\CubetaStarter\app\Http\Controllers;

use Artisan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Cubeta\CubetaStarter\Traits\AssistCommand;

class InitialProjectController extends Controller
{
    use AssistCommand;

    public function callInitialProject(Request $request)
    {
        set_time_limit(0);
        $data = $request->only(['useExceptionHandler', 'installSpatie', 'roles']);

        Artisan::call('cubeta-init', [
            'useExceptionHandler' => $data['useExceptionHandler'],
            'installSpatie' => $data['installSpatie'],
            'rolesPermissionsArray' => $request->roles ? $this->convertRolesPermissionArrayToCommandAcceptableFormat($data['roles']) : null
        ]);

        return redirect()->route('cubeta-starter.greetings');
    }

    public function convertRolesPermissionArrayToCommandAcceptableFormat(array $rolesPermissionArray)
    {
        $result = [];

        foreach ($rolesPermissionArray as $array) {
            $result[$array['name']] = $this->convertInputStringToArray($array['permissions']);
        }

        return $result;
    }
}
