<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait AssistCommand
{
    /**
     * Get the app root path
     *
     * @return string
     */
    public function appPath()
    {
        return app()->basePath();
    }

    /**
     * Get the database path
     *
     * @return string
     */
    public function appDatabasePath()
    {
        return app()->databasePath();
    }

    /**
     * Ensure a directory exists.
     *
     * @param string $path
     *
     * @throws BindingResolutionException
     */
    public function ensureDirectoryExists($path): void
    {
        app()->make(Filesystem::class)->ensureDirectoryExists($path);
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

    public function excuteCommandInTheBaseDirectory(string $command): void
    {
        $rootPath = base_path();
        $output = shell_exec("cd {$rootPath} && {$command}");
        $this->output->write($output);
    }

    /** check if the migration is exists
     */
    public function checkIfMigrationExists($tableName): bool
    {
        $allMigrations = File::allFiles(base_path() . '/database/migrations');
        foreach ($allMigrations as $migration) {
            $migrationName = $migration->getBasename();
            if (Str::contains($migrationName, '_create_' . $tableName . '_table')) {
                return true;
            }
        }

        return false;
    }

    /** return the name based on name convention for models
     * @param $name
     * @return string
     */
    public function modelNaming($name): string
    {
        return ucfirst(Str::singular(Str::studly($name)));
    }

    /** return the name based on name convention for tables
     * @param $name
     * @return string
     */
    public function tableNaming($name): string
    {
        return strtolower(Str::plural(Str::snake($name)));
    }

    /** return the name based on name convention for routes
     * @param $name
     * @return string
     */
    public function routeNaming($name): string
    {
        return strtolower(Str::plural(Str::snake($name)));
    }

    /** return the name based on name convention for relation functions in the models
     * @param $name
     * @return string
     */
    public function relationFunctionNaming($name, $singular = true): string
    {
        if ($singular)
            return Str::camel(lcfirst(Str::singular(Str::studly($name))));

        else
            return Str::camel(lcfirst(Str::plural(Str::studly($name))));

    }
}
