<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Error;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class MigrationGenerator extends AbstractGenerator
{
    public static string $key = 'migration';
    public static string $configPath = 'cubeta-starter.migration_path';

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $migrationName = $this->generatedFileName();

        throw_if($this->checkIfMigrationExists(), new Error("{$migrationName} Already Exists"));

        $stubProperties = [
            '{table}' => $this->tableName($this->fileName),
            '{col}' => $this->generateColumns(),
        ];

        $migrationPath = $this->getGeneratingPath($migrationName);

        $this->generateFileFromStub($stubProperties, $migrationPath);

        $this->addToJsonFile();

        $this->formatFile($migrationPath);
    }

    public function generatedFileName(): string
    {
        $date = now()->subSecond()->format('Y_m_d_His');
        return $date . '_create_' . $this->tableName($this->fileName) . '_table';
    }

    private function checkIfMigrationExists(): ?string
    {
        $tableName = $this->tableName($this->fileName);

        $migrationsPath = base_path(config('cubeta-starter.migration_path'));
        $this->ensureDirectoryExists($migrationsPath);

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
        foreach ($this->attributes as $name => $type) {
            $name = $this->columnName($name);
            $nullable = (in_array($name, $this->nullables) || $type == 'file') ? '->nullable()' : '';
            $unique = in_array($name, $this->uniques) ? '->unique()' : '';

            $columns .= match ($type) {
                'key' => '',
                'translatable' => "\t\t\t\$table->json('{$name}')" . $nullable . $unique . "; \n",
                default => "\t\t\t\$table->" . ($type == 'file' ? 'string' : $type) . "('{$name}')" . $nullable . $unique . "; \n",
            };
        }

        foreach ($this->relations as $rel => $type) {
            if ($type == RelationsTypeEnum::HasOne->value || $type == RelationsTypeEnum::BelongsTo->value) {
                $nullable = in_array($rel . '_id', $this->nullables) ? '->nullable()' : '';
                $modelName = ucfirst(Str::singular(str_replace('_id', '', $rel)));
                $columns .= "\t\t\t\$table->foreignIdFor(\\" . config('cubeta-starter.model_namespace') . "\\{$modelName}::class){$nullable}->constrained()->cascadeOnDelete(); \n";
            }
        }

        return $columns;
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/migration.stub';
    }
}