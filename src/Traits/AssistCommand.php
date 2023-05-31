<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait AssistCommand
{
    public function executeCommandInTheBaseDirectory(string $command): string|null|bool
    {
        $rootDirectory = base_path();
        $fullCommand = sprintf('cd %s && %s', escapeshellarg($rootDirectory), $command);

        return shell_exec($fullCommand);
    }

    /**
     * @param $tableName
     * @return false|string
     */
    public function checkIfMigrationExists($tableName): bool|string
    {
        $migrationsPath = base_path(config('cubeta-starter.migration_path'));
        ensureDirectoryExists($migrationsPath);

        $allMigrations = File::allFiles($migrationsPath);
        foreach ($allMigrations as $migration) {
            $migrationName = $migration->getBasename();
            if (Str::contains($migrationName, "_create_$tableName".'_table')) {
                return $migration->getRealPath();
            }
        }

        return false;
    }

    /**
     * format the file on the given path
     *
     * @param $filePath string the project path of the file eg:app/Models/MyModel.php
     */
    public function formatFile(string $filePath): void
    {
        $command = base_path()."./vendor/bin/pint $filePath";
        $output = $this->executeCommandInTheBaseDirectory($command);
        $this->line((string) $output);
    }

    /**
     * taking the input string from the user and convert it to array
     */
    public function convertInputStringToArray($input = null): ?array
    {
        if (is_null($input)) {
            return null;
        }

        $input = preg_replace('/\s+/', '', $input);

        return explode(',', $input);
    }
}
