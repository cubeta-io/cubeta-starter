<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Migrations;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\PhpFileStubBuilder;
use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;

/**
 * @method self tableName(string $tableName)
 */
class MigrationStubBuilder extends PhpFileStubBuilder
{
    private array $columns = [];

    protected function getStubPropertyArray(): array
    {
        return [
            '{{col}}' => implode("\n", $this->columns),
            ...$this->stubProperties,
        ];
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Migrations/Migration.stub');
    }

    public function column(MigrationColumnString $column): static
    {
        if (! is_null($column->import)) {
            $this->import($column->import);
        }

        $this->columns[] = "{$column}";

        return $this;
    }
}
