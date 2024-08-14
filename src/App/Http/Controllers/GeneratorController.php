<?php

namespace Cubeta\CubetaStarter\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
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
use Cubeta\CubetaStarter\Logs\CubeLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GeneratorController extends Controller
{
    public Request $request;
    private bool $installedWeb;
    private bool $installedApi;
    private ?string $validContainer = null;

    public function __construct()
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
        ini_set('max_execution_time', 0);
    }

    private function handleLogs()
    {
        CubeLog::handleExceptionsAsErrors();
        $logs = CubeLog::logs();
        $oldLogs = Cache::get('logs') ?? [];
        Cache::forever('logs', [...$oldLogs, ...$logs]);
    }

    /**
     * @param array{
     *     model_name:string,columns:array,relations:array,uniques:array,nullables:array,
     *     actor:string|null,container:string,generate_key:string
     * } $factoryData
     */
    public function runFactory(array $factoryData)
    {
        set_time_limit(0);
        try {
            $generator = new GeneratorFactory();
            if ($factoryData['generate_key'] == "full_crud") {
                foreach (GeneratorFactory::getAllGeneratorsKeys() as $key) {
                    $generator->setSource($key)->make(
                        fileName: $factoryData['model_name'],
                        attributes: $factoryData['columns'] ?? [],
                        relations: $factoryData['relations'] ?? [],
                        nullables: $factoryData['nullables'] ?? [],
                        uniques: $factoryData['uniques'] ?? [],
                        actor: $factoryData['actor'] ?? null,
                        generatedFor: $factoryData['container'] ?? ContainerType::API,
                    );
                }
            } else {
                $generator->setSource($factoryData['generate_key'])->make(
                    fileName: $factoryData['model_name'],
                    attributes: $factoryData['columns'] ?? [],
                    relations: $factoryData['relations'] ?? [],
                    nullables: $factoryData['nullables'] ?? [],
                    uniques: $factoryData['uniques'] ?? [],
                    actor: $factoryData['actor'] ?? null,
                    generatedFor: $factoryData['container'] ?? ContainerType::API,
                );
            }
            $this->handleLogs();
        } catch (Exception $exception) {
            CubeLog::add($exception);
            $this->handleLogs();
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
            if ($value == "true") {
                $data[$key] = true;
            }
        }
        return $data;
    }

    public function clearLogs()
    {
        Cache::delete('logs');
        return response()->json(['success' => true]);
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
        $data = $request->all();
        $modelName = $data['model_name'];
        $generateKey = $data['generate_key'] ?? "full_crud";
        $container = $data['container'] ?? ($this->installedApi ? ContainerType::API : ContainerType::WEB);
        $actor = $data['actor'] ?? null;
        $columns = [];
        $nullables = [];
        $uniques = [];
        $relations = [];

        if (isset($data['columns'])) {
            foreach ($data['columns'] as $column) {
                $columns[$column['name']] = $column[$column['type']] ?? ColumnTypeEnum::STRING->value;
                if (isset($column['unique']) && $column['unique'] == "true") {
                    $uniques[] = $column['name'];
                }
                if (isset($column['nullable']) && $column['nullable'] == "true") {
                    $nullables[] = $column['name'];
                }
            }
        }

        if (isset($data['relations'])) {
            foreach ($data['relations'] as $relation) {
                $relations[$relation['name']] = $relation["type"];
            }
        }

        $this->runFactory([
            'generate_key' => $generateKey,
            'model_name'   => $modelName,
            'container'    => $container,
            'actor'        => $actor,
            'columns'      => $columns,
            'relations'    => $relations,
            'nullables'    => $nullables,
            'uniques'      => $uniques,
        ]);

        return redirect()->route('cubeta.starter.generate.page');
    }
}
