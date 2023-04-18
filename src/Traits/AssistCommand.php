<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

trait AssistCommand
{
    /**
     * Get the app root path
     *
     * @return string|mixed
     */
    public function appPath()
    {
        return app()->basePath();
    }

    /**
     * Get the database path
     *
     * @return string|mixed
     */
    public function appDatabasePath()
    {
        return app()->databasePath();
    }

    /**
     * Ensure a directory exists.
     *
     * @param  string  $path
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
                $columns .= "\t\t\t\$table->".($type == 'file' ? 'string' : $type)."('$name')".($type == 'file' ? '->nullable()' : '')."; \n";
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
}
