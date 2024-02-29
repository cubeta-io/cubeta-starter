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
}
