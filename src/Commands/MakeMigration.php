<?php

namespace Cubeta\CubetaStarter\Commands;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

class MakeMigration extends Command
{
    use AssistCommand;

    public $description = 'Create a new migration';

    public $signature = 'create:migration
        {name : The name of the model }
        {attributes? : columns with data types}
        {relations?  : related models}
        {nullables? : nullable columns}';

    /**
     * return the columns of the migration file
     */
    public function generateMigrationCols(array $attributes = [], array $relations = [], array $nullables = []): string
    {
        $columns = '';
        foreach ($attributes as $name => $type) {
            $name = columnNaming($name);
            if ($type == 'key') {
                continue;
            }
            if ($type == 'translatable') {
                $columns .= "\t\t\t\$table->json('{$name}')" . (in_array($name, $nullables) ? '->nullable()' : '') . "; \n";
            } else {
                $columns .= "\t\t\t\$table->" . ($type == 'file' ? 'string' : $type) . "('{$name}')" . (in_array($name, $nullables) ? '->nullable()' : '') . "; \n";
            }
        }

        foreach ($relations as $rel => $type) {
            if ($type == RelationsTypeEnum::HasOne || $type == RelationsTypeEnum::BelongsTo) {
                $modelName = ucfirst(Str::singular(str_replace('_id', '', $rel)));
                $columns .= "\t\t\t\$table->foreignIdFor(\\" . config('cubeta-starter.model_namespace') . "\\{$modelName}::class)->constrained()->cascadeOnDelete(); \n";
            }
        }

        return $columns;
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes') ?? [];
        $relations = $this->argument('relations') ?? [];
        $nullables = $this->argument("nullables") ?? [];

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createMigration($modelName, $attributes, $relations, $nullables);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createMigration($modelName, array $attributes = [], array $relations = [], array $nullables = []): void
    {

        $tableName = tableNaming($modelName);

        $migrationName = $this->getMigrationName($modelName);

        if ($this->checkIfMigrationExists($tableName)) {
            $this->error("{$migrationName} Already Exists");

            return;
        }

        $stubProperties = [
            '{table}' => $tableName,
            '{col}' => $this->generateMigrationCols($attributes, $relations, $nullables),
        ];
        $migrationPath = $this->getMigrationsPath($migrationName);

        generateFileFromStub(
            $stubProperties,
            $migrationPath,
            __DIR__ . '/stubs/migration.stub'
        );

        $this->formatFile($migrationPath);
        $this->info("Created migration: {$migrationName}");
    }

    private function getMigrationName($modelName): string
    {
        $date = Carbon::now()->subSecond()->format('Y_m_d_His');

        return $date . '_create_' . tableNaming($modelName) . '_table';
    }

    private function getMigrationsPath($migrationName): string
    {
        $path = base_path(config('cubeta-starter.migration_path'));
        ensureDirectoryExists($path);

        return "{$path}/{$migrationName}" . '.php';
    }
}
