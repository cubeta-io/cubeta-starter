<?php

namespace Cubeta\CubetaStarter\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Artisan;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InitialProjectController extends Controller
{
    use AssistCommand;

    public function callInitialProject(Request $request)
    {
//        $rules = [
//            'useExceptionHandler' => 'boolean|default:false|required',
//            'installSpatie' => 'boolean|default:false|required',
//            'roles' => 'nullable|array',
//            'roles.*.name' => 'string|max:255|' . Rule::requiredIf($request->roles),
//            'roles.*.permissions' => 'string|nullable',
//        ];
//
//        $validator = Validator::make($request->all(), $rules);
//
//        if ($validator->fails()) {
//            return redirect()->back()->with(['errors' => $validator->errors()]);
//        }

        $data = $request->all();

        Artisan::call('cubeta-init', [
            'useExceptionHandler' => $data['useExceptionHandler'],
            'installSpatie' => $data['installSpatie'],
            'rolesPermissionsArray' => $this->convertRolesPermissionArrayToCommandAcceptableFormat($data['roles'])
        ]);

        return redirect()->route('greetings');
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
