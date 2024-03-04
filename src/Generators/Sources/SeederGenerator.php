<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

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

        $seederPath = $this->table->getSeederPath();

        if ($seederPath->exist()) {
            $seederPath->logAlreadyExist("Generating Seeder For ({$this->table->modelName}) Model");
        }

        $stubProperties = [
            '{modelName}' => $this->table->modelName,
            '{modelNamespace}' => config('cubeta-starter.model_namespace')
        ];

        $this->generateFileFromStub($stubProperties, $seederPath->fullPath);

        $seederPath->format();
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../stubs/seeder.stub';
    }
}
