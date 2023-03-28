<?php

namespace Cubeta\CubetaStarter\Traits;

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
     * @param string $path
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function ensureDirectoryExists($path)
    {
        app()->make(Filesystem::class)->ensureDirectoryExists($path);
    }
}
