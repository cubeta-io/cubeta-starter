<?php

namespace Cubeta\CubetaStarter\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Generators\Installers\ApiInstaller;
use Cubeta\CubetaStarter\Generators\Installers\AuthInstaller;
use Cubeta\CubetaStarter\Generators\Installers\BladePackagesInstaller;
use Cubeta\CubetaStarter\Generators\Installers\ReactTSInertiaInstaller;
use Cubeta\CubetaStarter\Generators\Installers\ReactTsPackagesInstaller;
use Cubeta\CubetaStarter\Generators\Installers\WebInstaller;
use Cubeta\CubetaStarter\Generators\Sources\ActorFilesGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ApiControllerGenerator;
use Cubeta\CubetaStarter\Generators\Sources\FactoryGenerator;
use Cubeta\CubetaStarter\Generators\Sources\MigrationGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ModelGenerator;
use Cubeta\CubetaStarter\Generators\Sources\RepositoryGenerator;
use Cubeta\CubetaStarter\Generators\Sources\RequestGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ResourceGenerator;
use Cubeta\CubetaStarter\Generators\Sources\SeederGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ServiceGenerator;
use Cubeta\CubetaStarter\Generators\Sources\TestGenerator;
use Cubeta\CubetaStarter\Generators\Sources\WebControllers\BladeControllerGenerator;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CallAppropriateCommand extends Controller
{
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
        ini_set('max_execution_time', 0);
    }

    private function configureRequestArray($array = null)
    {
        if (!isset($array) || $array == []) {
            return [];
        }

        return collect($array)->mapWithKeys(fn($item) => [$item['name'] => $item['type']])->toArray();
    }

    public function generateActors(Request $request)
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

        if (count($exceptions)) {
            return redirect()->route($redirectRouteName, ['error' => "Check Logs For Errors"]);
        }

        return redirect()->route($redirectRouteName, ['success' => $successMessage]);
    }

    public function fullGeneration()
    {
        return $this->runFactory([
            "key" => "all",
            "route" => 'cubeta-starter.generate-full.page',
        ], "A Full CRUD Operation Has Been Created");
    }

    public function generateApiController()
    {
        $factoryData = [
            'key' => ApiControllerGenerator::$key,
            'route' => 'cubeta-starter.generate-api-controller.page',
        ];

        return $this->runFactory($factoryData, "Api Controller Created Successfully");
    }

    public function runFactory(array $factoryData, string $successMessage)
    {
        set_time_limit(0);
        try {

            if (empty(trim($this->modelName))) {
                return redirect()->route($factoryData['route'], ['error' => "Invalid Model Name"]);
            }

            $arguments['name'] = $this->modelName;

            $tempColsArray = [];
            if (isset($this->columns) && count($this->columns) > 0) {
                foreach ($this->columns as $col => $type) {
                    if (empty(trim($col))) {
                        return redirect()->route($factoryData['route'], ['error' => "Invalid Column Name"]);
                    }
                    $tempColsArray[Naming::column($col)] = $type;
                }
                $arguments['attributes'] = $tempColsArray;
            }

            if (isset($this->relations) && count($this->relations) > 0) {
                foreach ($this->relations as $relation => $type) {
                    if (empty(trim($relation))) {
                        return redirect()->route($factoryData['route'], ['error' => "Invalid Relation Name"]);
                    }
                }
                $arguments['relations'] = $this->relations;
            }

            if (isset($this->nullables) && count($this->nullables) > 0) {
                $arguments['nullables'] = array_map(fn($nullable) => Naming::column($nullable), $this->nullables);
            }

            if (isset($this->uniques) && count($this->uniques) > 0) {
                $arguments['uniques'] = array_map(fn($unique) => Naming::column($unique), $this->uniques);
            }

            if (isset($this->actor)) {
                $arguments['actor'] = $this->actor;
            }

            if (isset($this->container) && !in_array($this->container, ContainerType::ALL)) {
                return redirect()->route($factoryData['route'], ['error' => "Invalid container name"]);
            }

            $generator = new GeneratorFactory();
            if ($factoryData['key'] == "all") {
                foreach (GeneratorFactory::getAllGeneratorsKeys() as $key) {
                    $generator->setSource($key)->make(
                        fileName: $arguments['name'],
                        attributes: $arguments['attributes'] ?? [],
                        relations: $arguments['relations'] ?? [],
                        nullables: $arguments['nullables'] ?? [],
                        uniques: $arguments['uniques'] ?? [],
                        actor: $arguments['actor'] ?? null,
                        generatedFor: $this->container ?? ContainerType::API,
                    );
                }
            } else {
                $generator->setSource($factoryData['key'])->make(
                    fileName: $arguments['name'],
                    attributes: $arguments['attributes'] ?? [],
                    relations: $arguments['relations'] ?? [],
                    nullables: $arguments['nullables'] ?? [],
                    uniques: $arguments['uniques'] ?? [],
                    actor: $arguments['actor'] ?? null,
                    generatedFor: $this->container ?? ContainerType::API,
                );
            }

            return $this->handleLogs($factoryData['route'], $successMessage);

        } catch (Exception $exception) {
            $error = CubeLog::exceptionToHtml($exception);
            return view('CubetaStarter::command-output', compact('error'));
        }
    }

    public function generateFactory()
    {
        $factoryData = [
            'key' => FactoryGenerator::$key,
            'route' => 'cubeta-starter.generate-factory.page',
        ];

        return $this->runFactory($factoryData, "New Factory Generated Successfully");
    }

    public function generateMigration()
    {
        $factoryData = [
            'key' => MigrationGenerator::$key,
            'route' => 'cubeta-starter.generate-migration.page',
        ];

        return $this->runFactory($factoryData, "New Migration Generated Successfully");
    }

    public function generateModel()
    {
        $factoryData = [
            'key' => ModelGenerator::$key,
            'route' => 'cubeta-starter.generate-full.page',
        ];

        return $this->runFactory($factoryData, "New Model generated Successfully");
    }

    public function generateRepository()
    {
        $factoryData = [
            'key' => RepositoryGenerator::$key,
            'route' => 'cubeta-starter.generate-repository.page',
        ];

        return $this->runFactory($factoryData, "New Repository Class Generated Successfully");
    }

    public function generateRequest()
    {
        $factoryData = [
            'key' => RequestGenerator::$key,
            'route' => 'cubeta-starter.generate-request.page',
        ];

        return $this->runFactory($factoryData, "New Form Request Generated Successfully");
    }

    public function generateResource()
    {
        $factoryData = [
            'key' => ResourceGenerator::$key,
            'route' => 'cubeta-starter.generate-resource.page',
        ];

        return $this->runFactory($factoryData, "New Resource Generated Successfully");
    }

    public function generateSeeder()
    {
        $factoryData = [
            'key' => SeederGenerator::$key,
            'route' => 'cubeta-starter.generate-seeder.page',
        ];

        return $this->runFactory($factoryData, "New Seeder Generated Successfully");
    }

    public function generateService()
    {
        $factoryData = [
            'key' => ServiceGenerator::$key,
            'route' => 'cubeta-starter.generate-service.page',
        ];

        return $this->runFactory($factoryData, "New Service Class And Its Interface Generated Successfully");
    }

    public function generateTest()
    {
        $factoryData = [
            'key' => TestGenerator::$key,
            'route' => 'cubeta-starter.generate-test.page',
        ];

        return $this->runFactory($factoryData, "New Test For Api Controller Generated Successfully");
    }

    public function generateWebController()
    {
        $factoryData = [
            'key' => BladeControllerGenerator::$key,
            'route' => 'cubeta-starter.generate-web-controller.page',
        ];

        return $this->runFactory($factoryData, "New Web Controller With Related Views Generated Successfully");
    }

    public function installWebPackages()
    {
        (new GeneratorFactory(BladePackagesInstaller::$key))->make();
        return $this->handleLogs("cubeta-starter.complete-installation", "Web Packages Installed Successfully");
    }

    public function installAuth(string $container = ContainerType::BOTH)
    {
        (new GeneratorFactory(AuthInstaller::$key))->make(generatedFor: $container , override: true);
        return $this->handleLogs("cubeta-starter.generate-add-actor.page", "Authentication Tools Prepared Successfully");
    }

    public function installApi()
    {
        (new GeneratorFactory(ApiInstaller::$key))->make(override: true);
        return $this->handleLogs("cubeta-starter.complete-installation", "Api Based Usage Tools Generated Successfully");
    }

    public function installWeb()
    {
        (new GeneratorFactory(WebInstaller::$key))->make(override: true);
        return $this->handleLogs('cubeta-starter.complete-installation', "Web Based Usage Tools Generated Successfully");
    }

    public function installReactTs()
    {
        (new GeneratorFactory(ReactTSInertiaInstaller::$key))->make(override: true);
        return $this->handleLogs("cubeta-starter.complete-installation", "React Frontend Stack Tools Has Been Installed");
    }

    public function installReactTsPackages()
    {
        (new GeneratorFactory(ReactTsPackagesInstaller::$key))->make();
        return $this->handleLogs("cubeta-starter.complete-installation", "React , Typescript Packages Has Been Installed");
    }

    public function choseFrontendStack(Request $request)
    {
        $stack = FrontendTypeEnum::tryFrom($request->input('stack'));

        if (!$stack) {
            return redirect()->back();
        }

        Settings::make()->setFrontendType($stack);
        return redirect()->back();
    }
}
