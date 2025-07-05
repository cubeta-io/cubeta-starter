<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Stub\Builders\Seeders\SeederStubBuilder;

class SeederGenerator extends AbstractGenerator
{
    public static string $key = 'seeder';

    public function run(bool $override = false): void
    {
        $seederPath = $this->table->getSeederPath();
        SeederStubBuilder::make()
            ->modelNamespace($this->table->getModelNamespace(false))
            ->modelName($this->table->modelNaming())
            ->generate($seederPath, $this->override);

        ClassUtils::callInDatabaseSeeder(
            $this->table->getSeederName(),
            $this->table->getSeederClassString() . "::class"
        );
    }
}
