<?php

namespace Cubeta\CubetaStarter\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class RenderAppropriateViewController extends Controller
{
    public array $arguments = [
        'title' => '',
        'textUnderTitle' => '',
        'action' => '',
        'modelNameField' => true,
        'attributesField' => false,
        'nullables' => false,
        'uniques' => false,
        'relationsField' => false,
        'actorsField' => false,
        'addActor' => false,
        'containerField' => false,
        'notes' => ''
    ];

    public array $roles = [];

    public array $types = [
        'integer', 'bigInteger', 'unsignedBigInteger',
        'unsignedDouble', 'double', 'float',
        'string', 'json', 'text',
        'boolean', 'date', 'time',
        'dateTime', 'timestamp', 'file',
        'key', 'translatable'
    ];

    public function __construct()
    {
        if (file_exists(base_path('app/Enums/RolesPermissionEnum.php')) && class_exists('\App\Enums\RolesPermissionEnum')) {
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            $this->roles = \App\Enums\RolesPermissionEnum::ALLROLES;
        } else {
            $this->roles = [];
        }
    }

    public function addActor()
    {
        $roles = $this->roles;
        $types = $this->types;
        $this->arguments['title'] = 'Add New Actor';
        $this->arguments['textUnderTitle'] = 'Here You Can Add Actors (Roles) To Your Project';
        $this->arguments['action'] = route('cubeta-starter.call-add-actor-command');
        $this->arguments['modelNameField'] = false;
        $this->arguments['addActor'] = true;
        $this->arguments['modalBody'] = "Adding Actors";
        $this->arguments['notes'] = "<li class='notes'>Don't forgot to run your migrations after installing the package because we are using spatie/laravel-permission <a href='https://spatie.be/docs/laravel-permission/v5/introduction'>Check spatie/permissions Documentation</a></li>
                                        <li class='notes'>Init api auth depends on <a href='https://github.com/PHP-Open-Source-Saver/jwt-auth'>jwt-auth</a> package so you need to configure it before using auth tools</li>
                                        <li class='notes'>You don't need to install jwt-auth ,it is included in the package just configure it</li>
                                        <li class='notes'>init auth commands will override any file with the same name and same path</li>
                                        <li class='notes'>authenticated checkbox will create an api auth endpoint for the created actor</li>
                                        <li class='notes'>RolesPermissionEnum class will be created in your app/Enums directory after adding new roles</li>
                                        <li class='notes'>If you want to remove a role just remove it from RolesPermissionsEnum</li>
                                        <li class='notes'>any change in the roles need you to run the role and the permissions seeders in the seeders directory</li>";
        return view('CubetaStarter::main-generate-page', compact('types', 'roles'))->with($this->arguments);
    }


    public function fullGenerate()
    {
        $roles = $this->roles;
        $types = $this->types;
        $this->arguments['title'] = 'Generate The CRUDs';
        $this->arguments['textUnderTitle'] = 'Here We Will Create Your Model And All The Others Needs To Have A Complete CRUD API';
        $this->arguments['action'] = route('cubeta-starter.call-create-model-command');
        $this->arguments['attributesField'] = true;
        $this->arguments['nullables'] = true;
        $this->arguments['uniques'] = true;
        $this->arguments['relationsField'] = true;
        $this->arguments['actorsField'] = true;
        $this->arguments['containerField'] = true;
        $this->arguments['modalBody'] = "Generating The CRUDs";
        $this->arguments['notes'] = '   <li class="notes">This GUI will work just when the app environment is local</li>
                                        <li class="notes">If a model with the same name is exist nothing will be generated</li>
                                        <li class="notes">the generated files directories will be based on the package config file</li>
                                        <li class="notes">read about the key and translatable columns type in the <a href="https://gitlab.com/cubetaio/backend/cubeta-starter/-/blob/api-version-with-ui/readme.md" target="_blank">documentation</a></li>
                                        <li class="notes">each created file will be formatted with Laravel Pint based on the published pint configuration file</li>
                                        <li class="notes">entered model and columns names will be refactored based on Laravel naming convention, but it is better you follow it</li>
                                        ';

        return view('CubetaStarter::main-generate-page', compact('roles', 'types'))->with($this->arguments);
    }

    public function generateController()
    {
        $roles = $this->roles;
        $this->arguments['title'] = 'API Controller';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The API Controller For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-api-controller-command');
        $this->arguments['actorsField'] = true;
        $this->arguments['modalBody'] = "Generating API Controller";
        $this->arguments['notes'] = "<li class='notes'>The created controller has the five CRUD methods, and it uses the service interface to implement them<br>so it is necessary to has a service class and its interface with a corresponding repository to use the created<br>controller methods</li>";
        return view('CubetaStarter::main-generate-page', compact('roles'))->with($this->arguments);
    }

    public function generateFactory()
    {
        $types = $this->types;
        $this->arguments['title'] = 'Factory';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Factory Class For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-factory-command');
        $this->arguments['attributesField'] = true;
        $this->arguments['relationsField'] = true;
        $this->arguments['uniques'] = true;
        $this->arguments['modalBody'] = "Generating Factory";
        $this->arguments['notes'] = ' <li class="notes">check on how the factory will be generated <a
                                                href="https://gitlab.com/cubetaio/backend/cubeta-starter/-/blob/api-version-with-ui/readme.md#factories" target="_blank">here</a>
                                        </li>';
        return view('CubetaStarter::main-generate-page', compact('types'))->with($this->arguments);
    }

    public function generateMigration()
    {
        $types = $this->types;
        $this->arguments['title'] = 'Migration';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Migration File For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-migration-command');
        $this->arguments['attributesField'] = true;
        $this->arguments['nullables'] = true;
        $this->arguments['uniques'] = true;
        $this->arguments['relationsField'] = true;
        $this->arguments['modalBody'] = "Generating Migration";

        $this->arguments['notes'] = '<li class="notes">columns of type files will be placed on the migration file as a string columns with a nullable attribute</li>
                                        <li class="notes">columns of type key will be placed on the migration file as a foreignIdFor columns</li>
                                        <li class="notes">columns of type translatable will be placed on the migration file as a json columns</li>
                                        <li class="notes">it is always better to check on the created migration</li>';

        return view('CubetaStarter::main-generate-page', compact('types'))->with($this->arguments);
    }

    public function generatePolicy()
    {
        $this->arguments['title'] = 'Policy';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Policy Class For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-policy-command');
        $this->arguments['modalBody'] = "Generating Policy";
        return view('CubetaStarter::main-generate-page')->with($this->arguments);
    }

    public function generatePostmanCollection()
    {
        $types = $this->types;
        $this->arguments['title'] = 'Postman Collection';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Postman Collection Your Model Endpoints (CRUDs Only)';
        $this->arguments['action'] = route('cubeta-starter.call-create-postman-collection-command');
        $this->arguments['attributesField'] = true;
        $this->arguments['modalBody'] = "generating postman collection";
        return view('CubetaStarter::main-generate-page', compact('types'))->with($this->arguments);
    }

    public function generateRepository()
    {
        $this->arguments['title'] = 'Repository';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Repository Class For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-repository-command');
        $this->arguments['modalBody'] = "Generating Repository Class";
        return view('CubetaStarter::main-generate-page')->with($this->arguments);
    }

    public function generateRequest()
    {
        $types = $this->types;
        $this->arguments['title'] = 'Request';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Request Class For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-request-command');
        $this->arguments['attributesField'] = true;
        $this->arguments['uniques'] = true;
        $this->arguments['nullables'] = true;
        $this->arguments['modalBody'] = "Generating Form Request";
        $this->arguments['notes'] = ' <li class="notes">check on how the form request will be generated <a
                                                href="https://gitlab.com/cubetaio/backend/cubeta-starter/-/blob/api-version-with-ui/readme.md#requests" target="_blank">here</a>
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
        $this->arguments['modalBody'] = "Generating API Resource";
        return view('CubetaStarter::main-generate-page', compact('types'))->with($this->arguments);
    }

    public function generateSeeder()
    {
        $this->arguments['title'] = 'Seeder';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Seeder Class For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-seeder-command');
        $this->arguments['modalBody'] = "Generating Seeder";
        $this->arguments['notes'] = ' <li class="notes">The created seeder will call the model factory 10 times</li>';
        return view('CubetaStarter::main-generate-page')->with($this->arguments);
    }

    public function generateService()
    {
        $this->arguments['title'] = 'Service';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Service Class And Its Interface For Your Model';
        $this->arguments['action'] = route('cubeta-starter.call-create-service-command');
        $this->arguments['modalBody'] = "Generating Service Class And Interface";
        $this->arguments['notes'] = "<li class='notes'>The created service will construct an instance from the corresponding repository, so it is better to create one</li>";
        return view('CubetaStarter::main-generate-page')->with($this->arguments);
    }

    public function generateTest()
    {
        $roles = $this->roles;
        $types = $this->types;
        $this->arguments['title'] = 'Feature Test';
        $this->arguments['textUnderTitle'] = 'Here We Will Generate The Feature Test For Your Model Endpoints (CRUDs ONLY)';
        $this->arguments['action'] = route('cubeta-starter.call-create-test-command');
        $this->arguments['actorsField'] = true;
        $this->arguments['attributesField'] = true;
        $this->arguments['modalBody'] = "Generating Tests";
        return view('CubetaStarter::main-generate-page', compact('roles', 'types'))->with($this->arguments);
    }

    public function generateWebController()
    {
        $roles = $this->roles;
        $types = $this->types;
        $this->arguments['title'] = 'Add Web Controller';
        $this->arguments['textUnderTitle'] = 'Here you can generate the web controller with its views';
        $this->arguments['action'] = route('cubeta-starter.call-create-web-controller-command');
        $this->arguments['modelNameField'] = true;
        $this->arguments['actorsField'] = true;
        $this->arguments['attributesField'] = true;
        $this->arguments['relationsField'] = true;
        $this->arguments['nullables'] = true;
        $this->arguments['notes'] = "<li class='notes'>This will generate a controller and 4 view files in the resources/views/dashboard directory</li>";

        return view('CubetaStarter::main-generate-page', compact('roles', 'types'))->with($this->arguments);
    }

    public function getDocumentation()
    {
        $docs = file_get_contents(__DIR__ . '/../../../../readme.md');
        $docs = Str::inlineMarkdown($docs);
        return view('CubetaStarter::documentation', compact('docs'));
    }
}
