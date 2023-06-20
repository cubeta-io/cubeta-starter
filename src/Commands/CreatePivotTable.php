<?php

namespace Cubeta\CubetaStarter\Commands;

use Exception;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Contracts\Container\BindingResolutionException;

class CreatePivotTable extends Command
{
    use AssistCommand;

    protected $description = 'Create a migration file for a pivot table between two tables';

    protected $signature = 'create:pivot {table1} {table2}';

    public function getPivotPath(string $date, string $migrationName): string
    {
        return base_path(config('cubeta-starter.migration_path') . '/' . $date . '_' . $migrationName . '.php');
    }

    /**
     * @throws BindingResolutionException
     */
    public function handle()
    {
        $table1 = $this->argument('table1');
        $table2 = $this->argument('table2');

        if (( ! $table1 || empty(trim($table1))) || ( ! $table2 || empty(trim($table2)))) {
            $this->error('Invalid input');
            return;
        }

        $table1 = tableNaming($table1);
        $table2 = tableNaming($table2);

        $tables = [$table1, $table2];
        sort($tables);

        $pivotTableName = $tables[0] . '_' . $tables[1];

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

        $migrationName = 'create_' . $pivotTableName . '_table';

        $date = Carbon::now()->addSecond()->format('Y_m_d_His');

        $migrationPath = $this->getPivotPath($date, $migrationName);

        $checkMigrationResult = $this->checkIfMigrationExists($pivotTableName);

        if ($checkMigrationResult) {
            // this procedure to make the pivot migration get a date after the two tables
            // so when running the migrations don't get any error
            unlink($checkMigrationResult);
        }

        $className1 = modelNaming($table1);
        $className2 = modelNaming($table2);

        ensureDirectoryExists(base_path(config('cubeta-starter.migration_path')));

        $stubProperties = [
            '{pivotTableName}' => $pivotTableName,
            '{className1}' => '\\' . config('cubeta-starter.model_namespace') . "\\{$className1}",
            '{className2}' => '\\' . config('cubeta-starter.model_namespace') . "\\{$className2}",
        ];

        generateFileFromStub(
            $stubProperties,
            $migrationPath,
            __DIR__ . '/stubs/pivot-migration.stub'
        );

        $this->info("Pivot Table For {$className1} and {$className2} Created");

        $this->formatFile($migrationPath);
    }
}
