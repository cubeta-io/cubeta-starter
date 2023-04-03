<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;

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
}
