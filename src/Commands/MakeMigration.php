<?php

namespace Cubeta\CubetaStarter\Commands;

use Carbon\Carbon;
use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

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

        $this->createMigration($modelName, $attributes, $relations);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createMigration($modelName, array $attributes, array $relations): void
    {

        $migrationName = $this->getMigrationName($modelName);

        if ($this->checkIfMigrationExists($migrationName)) {
            return;
        }

        $tableName = $this->tableNaming($modelName);

        $stubProperties = [
            '{table}' => $tableName,
            '{col}' => $this->generateMigrationCols($attributes, $relations),
        ];
        $migrationPath = $this->getMigrationsPath($migrationName);

        new CreateFile(
            $stubProperties,
            $migrationPath,
            __DIR__.'/stubs/migration.stub'
        );

        $this->formatfile($migrationPath);
        $this->line("<info>Created migration:</info> $migrationName");
    }

    private function getMigrationsPath($migrationName): string
    {
        return $this->appDatabasePath().'/migrations'.
            "/$migrationName".'.php';
    }

    private function getMigrationName($modelName): string
    {
        $date = Carbon::now()->subSecond()->format('Y_m_d_His');

        return $date.'_create_'.$this->tableNaming($modelName).'_table';
    }

    /**
     * return the columns of the migration file
     */
    public function generateMigrationCols(array $attributes, array $relations): string
    {
        $columns = '';
        foreach ($attributes as $name => $type) {
            if ($type == 'key') {
                continue;
            } else {
                $columns .= "\t\t\t\$table->".($type == 'file' ? 'string' : $type)."('$name')".($type == 'file' ? '->nullable()' : '')."; \n";
            }
        }

        foreach ($relations as $rel => $type) {
            if ($type == RelationsTypeEnum::HasOne || $type == RelationsTypeEnum::BelongsTo) {
                $modelName = ucfirst(Str::singular(str_replace('_id', '', $rel)));
                $columns .= "\t\t\t\$table->foreignIdFor(App\Models\\$modelName::class)->constrained()->cascadeOnDelete(); \n";
            }
        }

        return $columns;
    }
}
