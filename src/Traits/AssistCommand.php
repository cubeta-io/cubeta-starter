<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait AssistCommand
{
    /**
     * Get the app root path
     * @return string
     */
    public function appPath(): string
    {
        return app()->basePath();
    }

    /**
     * Get the database path
     * @return string
     */
    public function appDatabasePath(): string
    {
        return app()->databasePath();
    }

    /**
     * Ensure a directory exists.
     * @param string $path
     * @throws BindingResolutionException
     */
    public function ensureDirectoryExists(string $path): void
    {
        app()->make(Filesystem::class)->ensureDirectoryExists($path);
    }

    public function executeCommandInTheBaseDirectory(string $command): string|null|bool
    {
        $rootDirectory = base_path();
        $fullCommand = sprintf('cd %s && %s', escapeshellarg($rootDirectory), $command);

        return shell_exec($fullCommand);
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
     */
    public function modelNaming($name): string
    {
        return ucfirst(Str::singular(Str::studly($name)));
    }

    /** return the name based on name convention for tables
     */
    public function tableNaming($name): string
    {
        return strtolower(Str::plural(Str::snake($name)));
    }

    /** return the name based on name convention for routes
     */
    public function routeNaming($name): string
    {
        return strtolower(Str::plural(Str::snake($name)));
    }

    /** return the name based on name convention for relation functions in the models
     */
    public function relationFunctionNaming($name, bool $singular = true): string
    {
        if ($singular) {
            return Str::camel(lcfirst(Str::singular(Str::studly($name))));
        } else {
            return Str::camel(lcfirst(Str::plural(Str::studly($name))));
        }

    }

    /**
     * format the file on the given path
     * @param $filePath string the project path of the file eg:app/Models/MyModel.php
     */
    public function formatFile(string $filePath): void
    {
        $command = base_path() . "./vendor/bin/pint $filePath";
        $output = $this->executeCommandInTheBaseDirectory($command);
        $this->line((string)$output);
    }
}
