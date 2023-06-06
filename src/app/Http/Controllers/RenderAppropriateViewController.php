<?php

namespace Cubeta\CubetaStarter\app\Http\Controllers;

use App\Http\Controllers\Controller;

class RenderAppropriateViewController extends Controller
{
    public array $arguments = [
        'title' => '',
        'textUnderTitle' => '',
        'action' => '',
        'modelNameField' => true,
        'attributesField' => false,
        'relationsField' => false,
        'actorsField' => false,
        'notes' => ''
    ];

    public array $types = [
        'integer', 'bigInteger', 'unsignedBigInteger',
        'unsignedDouble', 'double', 'float',
        'string', 'json', 'text',
        'boolean', 'date', 'time',
        'dateTime', 'timestamp', 'file',
        'key', 'translatable'
    ];

    public array $roles = [];

    public function __construct()
    {
        if (file_exists(base_path('app/Enums/RolesPermissionEnum.php'))) {
            $this->roles = \App\Enums\RolesPermissionEnum::ALLROLES;
        } else {
            $this->roles = [];
        }
    }


    public function fullGenerate()
    {
        $roles = $this->roles;
        $types = $this->types;
        $this->arguments['title'] = 'Generate The CRUDs';
        $this->arguments['textUnderTitle'] = 'Here We Will Create Your Model And All The Others Needs To Have A Complete CRUD API';
        $this->arguments['action'] = route('cubeta-starter.call-create-model-command');
        $this->arguments['attributesField'] = true;
        $this->arguments['relationsField'] = true;
        $this->arguments['actorsField'] = true;
        $this->arguments['notes'] = '<li class="notes">If a model with the same name is exist nothing will be generated</li>
                                        <li class="notes">This GUI will just when the app environment is local</li>
                                        <li class="notes">read about the key and translatable columns type in the <a href="https://gitlab.com/cubetaio/backend/cubeta-starter/-/blob/api-version-with-ui/readme.md">documentation</a></li>';

        return view('CubetaStarter::main-generate-page', compact('roles', 'types'))->with($this->arguments);
    }

    public function generateMigration()
    {
        $types = $this->types;
        $this->arguments['title'] = 'Migration';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Migration File For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-migration-command');
        $this->arguments['attributesField'] = true;
        $this->arguments['relationsField'] = true;

        $this->arguments['notes'] = '<li class="notes">columns of type files will be placed on the migration file as a string columns with a nullable attribute</li>
                                        <li class="notes">columns of type key will be placed on the migration file as a foreignIdFor columns</li>
                                        <li class="notes">columns of type translatable will be placed on the migration file as a json columns</li>
                                        <li class="notes">it is always better to check on the created files</li>';

        return view('CubetaStarter::main-generate-page', compact('types'))->with($this->arguments);
    }

    public function generateFactory()
    {
        $types = $this->types;
        $this->arguments['title'] = 'Factory';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Factory Class For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-factory-command');
        $this->arguments['attributesField'] = true;
        $this->arguments['relationsField'] = true;
        $this->arguments['notes'] = ' <li class="notes">check on how the factory will be generated <a
                                                href="https://gitlab.com/cubetaio/backend/cubeta-starter/-/blob/api-version-with-ui/readme.md#factories">here</a>
                                        </li>';
        return view('CubetaStarter::main-generate-page', compact('types'))->with($this->arguments);
    }

    public function generateSeeder()
    {
        $this->arguments['title'] = 'Seeder';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Seeder Class For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-seeder-command');
        return view('CubetaStarter::main-generate-page')->with($this->arguments);
    }

    public function generateRepository()
    {
        $this->arguments['title'] = 'Repository';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Repository Class For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-repository-command');
        return view('CubetaStarter::main-generate-page')->with($this->arguments);
    }

    public function generateService()
    {
        $this->arguments['title'] = 'Service';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Service Class And Its Interface For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-service-command');
        return view('CubetaStarter::main-generate-page')->with($this->arguments);
    }

    public function generateRequest()
    {
        $types = $this->types;
        $this->arguments['title'] = 'Request';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Request Class For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-request-command');
        $this->arguments['attributesField'] = true;
        $this->arguments['notes'] = ' <li class="notes">check on how the form request will be generated <a
                                                href="https://gitlab.com/cubetaio/backend/cubeta-starter/-/blob/api-version-with-ui/readme.md#requests">here</a>
                                        </li>';
        return view('CubetaStarter::main-generate-page', compact('types'))->with($this->arguments);
    }

    public function generateResource()
    {
        $types = $this->types;
        $this->arguments['title'] = 'API Resource';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The API Resource Class For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-resource-command');
        $this->arguments['attributesField'] = true;
        $this->arguments['relationsField'] = true;
        return view('CubetaStarter::main-generate-page', compact('types'))->with($this->arguments);
    }

    public function generateController()
    {
        $roles = $this->roles;
        $this->arguments['title'] = 'API Controller';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The API Controller For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-api-controller-command');
        $this->arguments['actorsField'] = true;
        return view('CubetaStarter::main-generate-page')->with($this->arguments);
    }

    public function generateTest()
    {
        $roles = $this->roles;
        $this->arguments['title'] = 'Feature Test';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Feature Test For Your Model Endpoints (CRUDs ONLY)';
        $this->arguments['action'] = route('cubeta-starter.call-create-test-command');
        $this->arguments['actorsField'] = true;
        return view('CubetaStarter::main-generate-page', compact('roles'))->with($this->arguments);
    }

    public function generatePolicy()
    {
        $this->arguments['title'] = 'Policy';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Policy Class For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-policy-command');
        return view('CubetaStarter::main-generate-page')->with($this->arguments);
    }

    public function generatePostmanCollection()
    {
        $types = $this->types;
        $this->arguments['title'] = 'Postman Collection';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Postman Collection Your Model Endpoints (CRUDs Only)';
        $this->arguments['action'] = route('cubeta-starter.call-create-postman-collection-command');
        $this->arguments['attributesField'] = true;
        return view('CubetaStarter::main-generate-page', compact('types'))->with($this->arguments);
    }
}
