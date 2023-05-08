<?php

namespace Cubeta\CubetaStarter\Commands;

use Carbon\Carbon;
use Cubeta\CubetaStarter\CreateFile;
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

        $this->createMigration($modelName, $attributes, $relations);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createMigration($modelName, array $attributes, array $relations): void
    {

        if ($this->checkIfMigrationExists(Str::plural(strtolower($modelName)))) {
            return;
        }

        $migrationName = $this->getMigrationName($modelName);

        $tableName = Str::plural(strtolower($modelName));

        $stubProperties = [
            '{table}' => $tableName,
            '{col}' => $this->generateMigrationCols($attributes, $relations),
        ];

        new CreateFile(
            $stubProperties,
            $this->getMigrationsPath($migrationName),
            __DIR__ . '/stubs/migration.stub'
        );
        $this->line("<info>Created migration:</info> $migrationName");
    }

    private function getMigrationsPath($migrationName): string
    {
        return $this->appDatabasePath() . '/migrations' .
            "/$migrationName" . '.php';
    }

    private function getMigrationName($modelName): string
    {
        $date = Carbon::now()->subSecond()->format('Y_m_d_His');

        return $date . '_create_' . Str::plural(strtolower(Str::snake($modelName))) . '_table';
    }
}
