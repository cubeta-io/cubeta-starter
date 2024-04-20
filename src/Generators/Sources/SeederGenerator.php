<?php

namespace Cubeta\CubetaStarter\Generators\Sources;


use Cubeta\CubetaStarter\Generators\AbstractGenerator;

class SeederGenerator extends AbstractGenerator
{
    public static string $key = 'seeder';

    public function run(bool $override = false): void
    {

        $seederPath = $this->table->getSeederPath();

        if ($seederPath->exist()) {
            $seederPath->logAlreadyExist("Generating Seeder For ({$this->table->modelName}) Model");
            return;
        }

        $seederPath->ensureDirectoryExists();

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
