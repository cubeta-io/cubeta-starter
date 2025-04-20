<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Migrations;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumn;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\PhpFileStubBuilder;

/**
 * @method self tableName(string $tableName)
 */
class MigrationStubBuilder extends PhpFileStubBuilder
{
    private array $columns = [];

    public function column(MigrationColumn $column): static
    {
        if (!is_null($column->import)) {
            $this->import($column->import);
        }

        $this->columns[] = "{$column}";
        return $this;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Migrations/Migration.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return [
            "{{col}}" => implode("\n", $this->columns),
            ...$this->stubProperties,
        ];
    }
}