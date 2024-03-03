<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\app\Models\CubetaAttribute;
use Cubeta\CubetaStarter\app\Models\CubetaRelation;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrationGenerator extends AbstractGenerator
{
    public static string $key = 'migration';
    public static string $configPath = 'cubeta-starter.migration_path';

    public function run(): void
    {
        $migrationPath = $this->table->getMigrationPath();

        if ($this->checkIfMigrationExists()) {
            $migrationPath->logAlreadyExist("Generating A migration For ({$this->table->modelName}) Model");
            return;
        }

        $migrationPath->ensureDirectoryExists();

        $stubProperties = [
            '{table}' => $this->table->tableNaming(),
            '{col}' => $this->generateColumns(),
        ];

        $this->generateFileFromStub($stubProperties, $migrationPath->fullDirectory);

        $migrationPath->format();
    }

    private function checkIfMigrationExists(): ?string
    {
        $tableName = $this->table->tableNaming();

        $migrationsPath = base_path(config('cubeta-starter.migration_path'));

        FileUtils::ensureDirectoryExists($migrationsPath);

        $allMigrations = File::allFiles($migrationsPath);

        foreach ($allMigrations as $migration) {
            $migrationName = $migration->getBasename();
            if (Str::contains($migrationName, "create_{$tableName}_table.php")) {
                return $migration->getRealPath();
            }
        }

        return null;
    }

    public function generateColumns(): string
    {
        $columns = '';
        $this->table->attributes()->each(function (CubetaAttribute $column) use (&$columns) {
            $name = $column->name;
            $nullable = $column->nullable ? '->nullable()' : '';
            $unique = $column->unique ? '->unique()' : '';

            $columns .= match ($column->type) {
                'key' => '',
                'translatable' => "\t\t\t\$table->json('{$name}')" . $nullable . $unique . "; \n",
                default => "\t\t\t\$table->" . ($column->type == 'file' ? 'string' : $column->type) . "('{$name}')" . $nullable . $unique . "; \n",
            };
        });

        $this->table->relations()->each(function (CubetaRelation $relation) use (&$columns) {
            if ($relation->isHasOne() || $relation->isBelongsTo()) {
                $nullable = in_array($relation->key, $this->nullables) ? '->nullable()' : '';
                $columns .= "\t\t\t\$table->foreignIdFor(\\" . config('cubeta-starter.model_namespace') . "\\{$relation->modelName}::class){$nullable}->constrained()->cascadeOnDelete(); \n";
            }
        });

        return $columns;
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/migration.stub';
    }
}
