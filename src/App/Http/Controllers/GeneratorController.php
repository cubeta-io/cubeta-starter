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
use Cubeta\CubetaStarter\Generators\Installers\PermissionsInstaller;
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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class GeneratorController extends Controller
{
    public Request $request;
    private mixed $actor;
    private mixed $columns;
    private mixed $container;
    private mixed $modelName;
    private mixed $nullables;
    private mixed $relations;
    private mixed $uniques;
    private bool $installedWeb;
    private bool $installedApi;
    private ?string $validContainer = null;

    public function __construct(Request $request)
    {
        $this->installedApi = Settings::make()->installedApi();
        $this->installedWeb = Settings::make()->installedWeb();

        if ($this->installedApi && $this->installedWeb) {
            $this->validContainer = ContainerType::BOTH;
        }

        if ($this->installedApi && !$this->installedWeb) {
            $this->validContainer = ContainerType::API;
        }

        if ($this->installedWeb && !$this->installedApi) {
            $this->validContainer = ContainerType::WEB;
        }

        $this->request = $request;
        $this->modelName = $request->model_name;
//        $this->relations = $this->configureRequestArray($request->relations);
//        $this->columns = $this->configureRequestArray($request->columns);
        $this->actor = $request->actor;
        $this->container = $request->containerType;
        $this->nullables = $request->nullables;
        $this->uniques = $request->uniques;
        $this->version = $request->version ?? 'v1';
        ini_set('max_execution_time', 0);
    }

    private function configureRequestArray($array = null)
    {
        if (!isset($array) || $array == []) {
            return [];
        }

        return collect($array)->mapWithKeys(fn ($item) => [$item['name'] => $item['type']])->toArray();
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

    private function handleLogs()
    {
        CubeLog::handleExceptionsAsErrors();
        $logs = CubeLog::logs();
        $oldLogs = Cache::get('logs') ?? [];
        Cache::forever('logs', [...$oldLogs, ...$logs]);
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
                $arguments['nullables'] = array_map(fn ($nullable) => Naming::column($nullable), $this->nullables);
            }

            if (isset($this->uniques) && count($this->uniques) > 0) {
                $arguments['uniques'] = array_map(fn ($unique) => Naming::column($unique), $this->uniques);
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

    public function settingsHandler(Request $request)
    {
        set_time_limit(0);
        $data = $this->prepareValues($request->all());

        if (isset($data['api']) && $data['api']) {
            (new GeneratorFactory(ApiInstaller::$key))->make();
        }

        if (isset($data['web']) && $data['web']) {
            if (isset($data['frontend_stack']) && FrontendTypeEnum::tryFrom($data['frontend_stack']) == FrontendTypeEnum::BLADE) {
                (new GeneratorFactory(WebInstaller::$key))->make();
                (new GeneratorFactory(BladePackagesInstaller::$key))->make();
            } elseif (isset($data['frontend_stack']) && FrontendTypeEnum::tryFrom($data['frontend_stack']) == FrontendTypeEnum::REACT_TS) {
                (new GeneratorFactory(ReactTsPackagesInstaller::$key))->make();
                (new GeneratorFactory(ReactTSInertiaInstaller::$key))->make();
            }
        }

        if (isset($data['auth']) && $data['auth'] && $this->validContainer) {
            (new GeneratorFactory(AuthInstaller::$key))->make(generatedFor: $this->validContainer);
        }

        if (isset($data['permissions']) && $data['permissions']) {
            (new GeneratorFactory(PermissionsInstaller::$key))->make();
        }

        $this->handleLogs();

        return redirect()->back();
    }

    public function addActor(Request $request)
    {
        $data = $this->prepareValues($request->all());
        if (!isset($data['actor']) || !isset($data['container'])) {
            return redirect()->back();
        }

        (new ActorFilesGenerator($data['actor'], [], $data['authenticated'] ?? false, $data['container']))->run();
        $this->handleLogs();
        return redirect()->back();
    }

    private function prepareValues(array $data = [])
    {
        foreach ($data as $key => $value) {
            if ($data[$key] == "true") {
                $data[$key] = true;
            }
        }

        return $data;
    }

    public function clearLogs()
    {
        Cache::delete('logs');
        return response()->json(['success' => true], 200);
    }

    public function generatePage()
    {
        if (!$this->installedApi && !$this->installedWeb) {
            return redirect()->route('cubeta.starter.settings');
        }

        $generatingType = GeneratorFactory::getAllGeneratorsKeys();
        $installedApi = $this->installedApi;
        $installedWeb = $this->installedWeb;
        $hasRoles = Settings::make()->hasRoles();

        return view('CubetaStarter::generator', compact([
            'generatingType',
            'installedApi',
            'installedWeb',
            'hasRoles',
        ]));
    }

    public function generate(Request $request)
    {
        dd($request->all());
    }
}
