<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\StringValues\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\Stub\Builders\Migrations\MigrationStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Migrations\PivotMigrationStubBuilder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrationGenerator extends AbstractGenerator
{
    public static string $key = 'migration';
    private MigrationStubBuilder $builder;

    public function __construct(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], ?string $actor = null, string $generatedFor = '', ?string $version = null, bool $override = false)
    {
        parent::__construct($fileName, $attributes, $relations, $nullables, $uniques, $actor, $generatedFor, $version, $override);
        $this->builder = MigrationStubBuilder::make();
    }

    public function run(bool $override = false): void
    {
        $migrationPath = $this->table->getMigrationPath();

        if (FileUtils::migrationExists($this->table->tableName)) {
            $migrationPath->logAlreadyExist("Generating A migration For ({$this->table->modelName}) Model");
            return;
        }

        $migrationPath->ensureDirectoryExists();

        $this->table->attributes()->each(function (CubeAttribute $column) {
            if ($column instanceof HasMigrationColumn) {
                $this->builder->column($column->migrationColumn());
            }
        });

        $this->builder->tableName($this->table->tableNaming())
            ->generate($migrationPath, $this->override);

        foreach ($this->table->relations(RelationsTypeEnum::ManyToMany) as $relation) {
            $this->createPivotTable($this->table->tableName, $relation->tableNaming());
        }
    }

    private function createPivotTable(string $table1, string $table2): void
    {
        $table1 = Str::singular(Naming::table($table1));
        $table2 = Str::singular(Naming::table($table2));
        $tables = [$table1, $table2];
        $pivotTableName = Naming::pivotTableNaming($table1, $table2);

        if (!FileUtils::migrationExists(Naming::table($tables[0])) || !$this->checkIfMigrationExists(Naming::table($tables[1]))) {
            CubeLog::error(
                message: "The Related Table Migration Isn't Defined \n Remember When Creating The Related Model To Mention The Many-To-Many Relation In The Generation Form",
                context: "Generating Migration For ({$this->table->modelName}) Model"
            );
            return;
        }

        $migrationName = 'create_' . $pivotTableName . '_table';

        $date = now()->addSecond()->format('Y_m_d_His');

        $migrationPath = CubePath::make(config('cubeta-starter.migration_path') . '/' . $date . '_' . $migrationName . '.php');

        $checkMigrationResult = FileUtils::migrationExists($pivotTableName);

        if ($checkMigrationResult) {
            // this procedure to make the pivot migration get a date after the two tables,
            // so when running the migrations don't get any error
            unlink($checkMigrationResult);
        }

        $firstModel = CubeTable::create($table1);
        $secondModel = CubeTable::create($table2);

        $migrationPath->ensureDirectoryExists();

        PivotMigrationStubBuilder::make()
            ->pivotTableName($pivotTableName)
            ->firstModelName($firstModel->modelName)
            ->secondModelName($secondModel->modelName)
            ->import(new PhpImportString($firstModel->getModelNameSpace(false)))
            ->import(new PhpImportString($secondModel->getModelNameSpace(false)))
            ->generate($migrationPath, $this->override);
    }
}
