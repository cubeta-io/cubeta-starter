<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Error;
use Throwable;

class ApiControllerGenerator extends AbstractGenerator
{
    public static string $key = 'api-controller';
    public static string $configPath = 'cubeta-starter.api_controller_path';

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $modelName = $this->modelName($this->fileName);
        $controllerName = $this->generatedFileName();
        $variableName = $this->variableName($modelName);
        $idVariable = $variableName . 'Id';

        $controllerPath = $this->getGeneratingPath($controllerName);

        throw_if(file_exists($controllerPath), new Error("{$controllerName} Already Exists"));

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.api_controller_namespace'),
            '{modelName}' => $modelName,
            '{variableNaming}' => $variableName,
            '{serviceNamespace}' => config('cubeta-starter.service_namespace'),
            '{requestNamespace}' => config('cubeta-starter.request_namespace'),
            '{resourceNamespace}' => config('cubeta-starter.resource_namespace'),
            '{idVariable}' => $idVariable,
        ];

        $this->generateFileFromStub($stubProperties, $controllerPath);

        $this->formatFile($controllerPath);
    }

    public function generatedFileName(): string
    {
        return $this->modelName($this->fileName) . 'Controller';
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/controller.api.stub';
    }
}