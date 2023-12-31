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
        {nullables? : nullable columns}
        {uniques? : uniques columns}';

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
        $uniques = $this->argument('uniques') ?? [];

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createMigration($modelName, $attributes, $relations, $nullables, $uniques);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createMigration($modelName, array $attributes = [], array $relations = [], array $nullables = [], array $uniques = []): void
    {

        $tableName = tableNaming($modelName);

        $migrationName = $this->getMigrationName($modelName);

        if ($this->checkIfMigrationExists($tableName)) {
            $this->error("{$migrationName} Already Exists");

            return;
        }

        $stubProperties = [
            '{table}' => $tableName,
            '{col}' => $this->generateMigrationCols($attributes, $relations, $nullables, $uniques),
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

    /**
     * return the columns of the migration file
     */
    public function generateMigrationCols(array $attributes = [], array $relations = [], array $nullables = [], array $uniques = []): string
    {
        $columns = '';
        foreach ($attributes as $name => $type) {
            $name = columnNaming($name);
            $nullable = (in_array($name, $nullables) || $type == 'file') ? '->nullable()' : '';
            $unique = in_array($name, $uniques) ? '->unique()' : '';

            if ($type == 'key') {
                continue;
            }
            if ($type == 'translatable') {
                $columns .= "\t\t\t\$table->json('{$name}')" . $nullable . $unique . "; \n";
            } else {
                $columns .= "\t\t\t\$table->" . ($type == 'file' ? 'string' : $type) . "('{$name}')" . $nullable . $unique . "; \n";
            }
        }

        foreach ($relations as $rel => $type) {
            if ($type == RelationsTypeEnum::HasOne || $type == RelationsTypeEnum::BelongsTo) {
                $nullable = in_array($rel . '_id', $nullables) ? '->nullable()' : '';
                $modelName = ucfirst(Str::singular(str_replace('_id', '', $rel)));
                $columns .= "\t\t\t\$table->foreignIdFor(\\" . config('cubeta-starter.model_namespace') . "\\{$modelName}::class){$nullable}->constrained()->cascadeOnDelete(); \n";
            }
        }

        return $columns;
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
