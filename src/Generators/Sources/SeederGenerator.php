<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Error;
use Illuminate\Support\Str;
use Throwable;

class SeederGenerator extends AbstractGenerator
{
    public static string $key = 'seeder';
    public static string $configPath = 'cubeta-starter.seeder_path';

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $modelName = $this->modelName($this->fileName);
        $seederName = $this->generatedFileName();

        $seederPath = $this->getGeneratingPath($seederName);

        throw_if(file_exists($seederPath), new Error("{$seederName} Already Exists"));

        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelNamespace}' => config('cubeta-starter.model_namespace')
        ];

        $this->generateFileFromStub($stubProperties, $seederPath);

        $this->formatFile($seederPath);
    }

    public function generatedFileName(): string
    {
        return $this->modelName($this->fileName) . 'Seeder';
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/seeder.stub';
    }
}