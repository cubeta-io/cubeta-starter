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

        $migrationName = $this->getMigrationName($modelName);

        if ($this->checkIfMigrationExists($migrationName)) {
            return;
        }

        $tableName = $this->tableNaming($modelName);

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

        return $date . '_create_' . $this->tableNaming($modelName) . '_table';
    }
}
