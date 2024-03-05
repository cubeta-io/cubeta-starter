<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Carbon\Carbon;
use Cubeta\CubetaStarter\app\Models\CubeAttribute;
use Cubeta\CubetaStarter\app\Models\CubeRelation;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Info\SuccessMessage;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mockery\Exception;

class MigrationGenerator extends AbstractGenerator
{
    public static string $key = 'migration';
    public static string $configPath = 'cubeta-starter.migration_path';

    public function run(bool $override = false): void
    {
        $migrationPath = $this->table->getMigrationPath();

        if ($this->checkIfMigrationExists($this->table->tableName)) {
            $migrationPath->logAlreadyExist("Generating A migration For ({$this->table->modelName}) Model");
            return;
        }

        $migrationPath->ensureDirectoryExists();

        $stubProperties = [
            '{table}' => $this->table->tableNaming(),
            '{col}' => $this->generateColumns(),
        ];

        $this->generateFileFromStub($stubProperties, $migrationPath->fullPath);

        foreach ($this->table->relations(RelationsTypeEnum::ManyToMany) as $relation) {
            $this->createPivotTable($this->table->tableName, $relation->tableNaming());
        }

        $migrationPath->format();
    }

    private function checkIfMigrationExists(string $tableName): ?string
    {
        $migrationsPath = base_path(config('cubeta-starter.migration_path'));

        FileUtils::ensureDirectoryExists($migrationsPath);

        $allMigrations = File::allFiles($migrationsPath);

        foreach ($allMigrations as $migration) {
            $migrationName = $migration->getBasename();
            if (Str::contains($migrationName, "create_{$tableName}_table.php")) {
                return $migration->getRealPath();
            }
        }

        return null;
    }

    public function generateColumns(): string
    {
        $columns = '';
        $this->table->attributes()->each(function (CubeAttribute $column) use (&$columns) {
            $name = $column->name;
            $nullable = $column->nullable ? '->nullable()' : '';
            $unique = $column->unique ? '->unique()' : '';

            $columns .= match ($column->type) {
                'key' => '',
                'translatable' => "\t\t\t\$table->json('{$name}')" . $nullable . $unique . "; \n",
                default => "\t\t\t\$table->" . ($column->type == 'file' ? 'string' : $column->type) . "('{$name}')" . $nullable . $unique . "; \n",
            };
        });

        $this->table->relations()->each(function (CubeRelation $relation) use (&$columns) {
            if ($relation->isHasOne() || $relation->isBelongsTo()) {
                $nullable = in_array($relation->key, $this->nullables) ? '->nullable()' : '';
                $columns .= "\t\t\t\$table->foreignIdFor(\\" . config('cubeta-starter.model_namespace') . "\\{$relation->modelName}::class){$nullable}->constrained()->cascadeOnDelete(); \n";
            }
        });

        return $columns;
    }

    private function createPivotTable(string $table1, string $table2): void
    {
        $table1 = Str::singular(Naming::table($table1));
        $table2 = Str::singular(Naming::table($table2));

        $tables = [$table1, $table2];
        sort($tables);

        $pivotTableName = $tables[0] . '_' . Naming::table($tables[1]);

        if (!$this->checkIfMigrationExists(Naming::table($tables[0])) || !$this->checkIfMigrationExists(Naming::table($tables[1]))) {
            CubeLog::add(new CubeError(
                message: "The Related Table Migration Isn't Defined \n Remember When Creating The Related Model To Mention The Many-To-Many Relation In The Generation Form",
                happenedWhen: "Generating Migration For ({$this->table->modelName}) Model"
            ));
            return;
        }

        $migrationName = 'create_' . $pivotTableName . '_table';

        $date = Carbon::now()->addSecond()->format('Y_m_d_His');

        $migrationPath = CubePath::make(config('cubeta-starter.migration_path') . '/' . $date . '_' . $migrationName . '.php');

        $checkMigrationResult = $this->checkIfMigrationExists($pivotTableName);

        if ($checkMigrationResult) {
            // this procedure to make the pivot migration get a date after the two tables
            // so when running the migrations don't get any error
            unlink($checkMigrationResult);
        }

        $className1 = Naming::model($table1);
        $className2 = Naming::model($table2);

        $migrationPath->ensureDirectoryExists();

        $stubProperties = [
            '{pivotTableName}' => $pivotTableName,
            '{className1}' => '\\' . config('cubeta-starter.model_namespace') . "\\{$className1}",
            '{className2}' => '\\' . config('cubeta-starter.model_namespace') . "\\{$className2}",
        ];

        try {
            FileUtils::generateFileFromStub(
                $stubProperties,
                $migrationPath->fullPath,
                __DIR__ . '/../../stubs/pivot-migration.stub'
            );
        } catch (Exception|BindingResolutionException|FileNotFoundException $exception) {
            CubeLog::add($exception);
            return;
        }

        $migrationPath->format();

        CubeLog::add(new SuccessMessage("Pivot Table For [$table1 , $table2] Has Been Created : [{$migrationPath->fullPath}]"));
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../stubs/migration.stub';
    }
}
