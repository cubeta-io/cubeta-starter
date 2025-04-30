<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Factories;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self modelNamespace(string $modelNamespace)
 * @method self modelName(string $modelName)
 */
class FactoryStubBuilder extends ClassStubBuilder
{
    private array $rows = [];

    protected function stubPath(): string
    {
        return CubePath::stubPath('Factories/Factory.stub');
    }

    public function row(FakeMethodString $faker): static
    {
        if (!is_null($faker->import)){
            $this->import($faker->import);
        }

        $this->rows[] = "{$faker} ,";
        return $this;
    }

    protected function getStubPropertyArray(): array
    {
        return [
            ...parent::getStubPropertyArray(),
            '{{rows}}' => implode("\n", $this->rows),
        ];
    }
}