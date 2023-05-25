<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait AssistCommand
{
    /**
     * @param string $directory
     * @return void
     */
    public function ensureDirectoryExists(string $directory): void
    {
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0775, true, true);
        }
    }

    public function executeCommandInTheBaseDirectory(string $command): string|null|bool
    {
        $rootDirectory = base_path();
        $fullCommand = sprintf('cd %s && %s', escapeshellarg($rootDirectory), $command);

        return shell_exec($fullCommand);
    }

    /**
     * @param $tableName
     * @return bool
     */
    public function checkIfMigrationExists($tableName): bool
    {
        $migrationsPath = base_path(config('repository.migration_path'));
        $this->ensureDirectoryExists($migrationsPath);

        $allMigrations = File::allFiles($migrationsPath);
        foreach ($allMigrations as $migration) {
            $migrationName = $migration->getBasename();
            if (Str::contains($migrationName, "_create_$tableName" . "_table")) {
                return true;
            }
        }

        return false;
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
