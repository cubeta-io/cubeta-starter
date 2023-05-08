<?php

namespace Cubeta\CubetaStarter\Commands;

use Carbon\Carbon;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

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

        $tables = [$table1, $table2];
        sort($tables);
        $pivotTableName = lcfirst(Str::snake(Str::singular($tables[0]))) . '_' . Str::snake(lcfirst(Str::plural($tables[1])));

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

        $migrationPath = database_path('migrations/' . $date . '_' . $migrationName . '.php');

        $stub = file_get_contents(__DIR__ . '/stubs/pivot-migration.stub');

        $className1 = ucfirst(Str::singular($table1));
        $className2 = ucfirst(Str::singular($table2));

        $files = app()->make(Filesystem::class);

        if ($files->exists($migrationPath)) {
            throw new Exception('The Pivot Table Migration For ' . $className1 . 'And ' . $className2 . '  Exists');
        }

        $stub = str_replace(
            ['{pivotTableName}', '{className1}', '{className2}'],
            [$pivotTableName, $className1, $className2],
            $stub
        );

        file_put_contents($migrationPath, $stub);
    }
}
