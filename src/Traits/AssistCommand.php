<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait AssistCommand
{
    /**
     * @return false|string
     */
    public function checkIfMigrationExists($tableName): bool|string
    {
        $tableName = tableNaming($tableName);
        $migrationsPath = base_path(config('cubeta-starter.migration_path'));
        ensureDirectoryExists($migrationsPath);

        $allMigrations = File::allFiles($migrationsPath);
        foreach ($allMigrations as $migration) {
            $migrationName = $migration->getBasename();
            if (Str::contains($migrationName, "create_{$tableName}_table.php")) {
                return $migration->getRealPath();
            }
        }

        return false;
    }

    /**
     * taking the input string from the user and convert it to array
     */
    public function convertInputStringToArray($input = null): ?array
    {
        if (null === $input) {
            return null;
        }

        $input = preg_replace('/\s+/', '', $input);

        return explode(',', $input);
    }

    /**
     * format the file on the given path
     *
     * @param $filePath string the project path of the file eg:app/Models/MyModel.php
     */
    public function formatFile(string $filePath): void
    {
        $command = base_path() . "./vendor/bin/pint {$filePath}";
        $output = $this->executeCommandInTheBaseDirectory($command);
        $this->line((string)$output);
    }

    /**
     * @param string $command
     * @return false|string|null
     */
    public function executeCommandInTheBaseDirectory(string $command): bool|string|null
    {
        if (app()->environment('local')) {
            $rootDirectory = base_path();
            $fullCommand = sprintf('cd %s && %s', escapeshellarg($rootDirectory), $command);

            return shell_exec($fullCommand);
        }
        $this->error('You are in the production environment this is not allowed');
        return false;

    }
}
