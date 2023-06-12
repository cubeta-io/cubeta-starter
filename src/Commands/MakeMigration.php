<?php

namespace Cubeta\CubetaStarter\Commands;

use Carbon\Carbon;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

class MakeMigration extends Command
{
    use AssistCommand;

    public $signature = 'create:migration
        {name : The name of the model }
        {attributes? : columns with data types}
        {relations?  : related models}';

    public $description = 'Create a new migration';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes') ?? [];
        $relations = $this->argument('relations') ?? [];

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createMigration($modelName, $attributes, $relations);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createMigration($modelName, array $attributes = [], array $relations = []): void
    {

        $tableName = tableNaming($modelName);

        $migrationName = $this->getMigrationName($modelName);

        if ($this->checkIfMigrationExists($tableName)) {
            $this->error("$migrationName Already Exists");

            return;
        }

        $stubProperties = [
            '{table}' => $tableName,
            '{col}' => $this->generateMigrationCols($attributes, $relations),
        ];
        $migrationPath = $this->getMigrationsPath($migrationName);

        generateFileFromStub(
            $stubProperties,
            $migrationPath,
            __DIR__ . '/stubs/migration.stub'
        );

        $this->formatFile($migrationPath);
        $this->info("Created migration: $migrationName");
    }

    private function getMigrationsPath($migrationName): string
    {
        $path = base_path(config('cubeta-starter.migration_path'));
        ensureDirectoryExists($path);

        return "$path/$migrationName" . '.php';
    }

    private function getMigrationName($modelName): string
    {
        $date = Carbon::now()->subSecond()->format('Y_m_d_His');

        return $date . '_create_' . tableNaming($modelName) . '_table';
    }

    /**
     * return the columns of the migration file
     */
    public function generateMigrationCols(array $attributes = [], array $relations = []): string
    {
        $columns = '';
        foreach ($attributes as $name => $type) {
            if ($type == 'key') {
                continue;
            } elseif ($type == 'translatable') {
                $columns .= "\t\t\t\$table->json('$name') ; \n";
            } else {
                $columns .= "\t\t\t\$table->" . ($type == 'file' ? 'string' : $type) . "('$name')" . ($type == 'file' ? '->nullable()' : '') . "; \n";
            }
        }

        foreach ($relations as $rel => $type) {
            if ($type == RelationsTypeEnum::HasOne || $type == RelationsTypeEnum::BelongsTo) {
                $modelName = ucfirst(Str::singular(str_replace('_id', '', $rel)));
                $columns .= "\t\t\t\$table->foreignIdFor(\\" . config('cubeta-starter.model_namespace') . "\\$modelName::class)->constrained()->cascadeOnDelete(); \n";
            }
        }

        return $columns;
    }
}
