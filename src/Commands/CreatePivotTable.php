<?php

namespace Cubeta\CubetaStarter\Commands;

use Carbon\Carbon;
use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;

class CreatePivotTable extends Command
{
    use AssistCommand;

    protected $signature = 'create:pivot {table1} {table2}';

    protected $description = 'Create a migration file for a pivot table between two tables';

    /**
     * @throws BindingResolutionException
     */
    public function handle()
    {
        $table1 = $this->argument('table1');
        $table2 = $this->argument('table2');

        $tables = array($table1, $table2);
        natcasesort($tables);

        $pivotTableName = tableNaming($tables[0]) . '_' . tableNaming($tables[1]);

        $this->info('Creating migration for pivot table ' . $pivotTableName);

        $this->createMigration($table1, $table2, $pivotTableName);

        $this->info('Migration created successfully.');
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    protected function createMigration($table1, $table2, $pivotTableName)
    {

        if ($this->checkIfMigrationExists($pivotTableName)) {
            return;
        }

        $migrationName = 'create_' . $pivotTableName . '_table';

        $date = Carbon::now()->addSecond()->format('Y_m_d_His');

        $migrationPath = $this->getPivotPath($date, $migrationName);

        $className1 = modelNaming($table1);
        $className2 = modelNaming($table2);

        ensureDirectoryExists(base_path(config('repository.migration_path')));

        if (file_exists($migrationPath)) {
            $this->info("The Pivot Table Migration For <fg=red>$className1</fg=red> And <fg=red>$className2</fg=red> Exists");
        }

        $stubProperties = [
            '{pivotTableName}' => $pivotTableName,
            '{className1}' => $className1,
            '{className2}' => $className2
        ];

        generateFileFromStub(
            $stubProperties,
            $migrationPath,
            __DIR__ . '/stubs/pivot-migration.stub'
        );

        $this->info("Pivot Table For $className1 and $className2 Created");

        $this->formatFile($migrationPath);
    }

    /**
     * @param string $date
     * @param string $migrationName
     * @return string
     */
    public function getPivotPath(string $date, string $migrationName): string
    {
        return base_path(config('repository.migration_path') . '/' . $date . '_' . $migrationName . '.php');
    }
}
