<?php

namespace Cubeta\CubetaStarter\Commands;

use Carbon\Carbon;
use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
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
     * Handle the command
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes') ?? [];
        $relations = $this->argument('relations') ?? [];

        $this->createMigration($modelName, $attributes, $relations);
    }

    private function createMigration($modelName, array $attributes, array $relations)
    {
        $migrationName = $this->getMigrationName($modelName);

        $tableName = Str::plural(strtolower($modelName));

        $stubProperties = [
            '{table}' => $tableName,
            '{col}' => $this->generateCols($attributes, $relations),
        ];

        new CreateFile(
            $stubProperties,
            $this->getMigrationsPath($migrationName),
            __DIR__ . '/stubs/migration.stub'
        );
        $this->line("<info>Created migration:</info> $migrationName");
    }

    private function getMigrationName($modelName): string
    {
        $date = Carbon::now()->subSecond()->format('Y_m_d_His');

        return $date . '_create_' . Str::plural(strtolower($modelName)) . '_table';
    }

    private function generateCols(array $attributes, array $relations): string
    {
        $columns = '';
        foreach ($attributes as $name => $type) {
            if ($type == 'key') {
                continue;
            } else {
                $columns .= "\t\t\t\$table->" . ($type == 'file' ? 'string' : $type) . "('$name')" . ($type == 'file' ? '->nullable()' : '') . "; \n";
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

    private function getMigrationsPath($migrationName): string
    {
        return $this->appDatabasePath() . '/migrations' .
            "/$migrationName" . '.php';
    }
}
