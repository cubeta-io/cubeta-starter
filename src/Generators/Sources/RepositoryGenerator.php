<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Error;
use Illuminate\Support\Str;
use Throwable;

class RepositoryGenerator extends AbstractGenerator
{
    public static string $key = 'repository';
    public static string $configPath = 'cubeta-starter.repository_path';

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $modelName = $this->modelName($this->fileName);
        $repositoryName = $this->generatedFileName();

        $repositoryPath = $this->getGeneratingPath($repositoryName);

        $modelVariable = variableNaming($modelName);

        throw_if(file_exists($repositoryPath), new Error("{$repositoryName} Already Exists"));

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.repository_namespace'),
            '{modelName}' => $modelName,
            '{modelVar}' => $modelVariable,
            '{modelNamespace}' => config('cubeta-starter.model_namespace')
        ];

        $this->generateFileFromStub($stubProperties, $repositoryPath);

        $this->formatFile($repositoryPath);
    }

    public function generatedFileName(): string
    {
        return $this->modelName($this->fileName) . 'Repository';
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/repository.stub';
    }
}