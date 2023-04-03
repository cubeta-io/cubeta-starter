<?php

namespace Cubeta\CubetaStarter\Commands;

use Carbon\Carbon;
use Cubeta\CubetaStarter\CreateFile;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Cubeta\CubetaStarter\Traits\AssistCommand;

class MakeMigration extends Command
{
    use AssistCommand;

    public $signature = 'create:migration
        {name : The name of the model }
        {attributes : columns with data types}?';

    public $description = 'Create a new migration';

    /**
     * Handle the command
     *
     * @return void
     */
    public function handle(): void
    {
        $modelName = $this->argument("name");
        $attributes = $this->argument("attributes");

        $this->createMigration($modelName,$attributes);
    }

    private function createMigration($modelName , array $attributes){

        $migrationName = $this->getMigrationName($modelName);

        $tableName = Str::plural(strtolower($modelName));

        $stubProperties = [
            "{table}" => $tableName,
            "{col}" => $this->generateCols($attributes),
        ];

        new CreateFile(
            $stubProperties,
            $this->getMigrationsPath($migrationName),
            __DIR__ . "/stubs/migration.stub"
        );
        $this->line("<info>Created migration:</info> $migrationName");
    }

    private function getMigrationName($modelName): string
    {
        $date = Carbon::now()->format('Y_m_d_').time();
        return $date . '_create_'. Str::plural(strtolower($modelName)) .'_table';
    }

    private function generateCols(array $attributes): string
    {
        $columns = '';
        foreach ($attributes as $name => $type) {
            if($type == 'key')
                $columns .= "\t\t\t\$table->foreignId('$name')->constrained()->onDelete('cascade'); \n";
            else
                $columns .= "\t\t\t\$table->".($type=='file'?'string':$type)."('$name')".($type=='file'?"->nullable()":'')."; \n";
        }
        return $columns;
    }

    private function getMigrationsPath($migrationName): string
    {
        return $this->appDatabasePath() . "/migrations" .
            "/$migrationName" . ".php";
    }
}
