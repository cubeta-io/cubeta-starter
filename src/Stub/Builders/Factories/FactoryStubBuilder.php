<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Factories;

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
        return CubePath::stubPath('Factory.stub');
    }

    public function row(string $key, string $value): static
    {
        $this->rows[] = "$key => $value ,";
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