<?php

namespace Cubeta\CubetaStarter\Helpers;

use Illuminate\Support\Str;

class Naming
{
    /**
     * @param string $name
     * @return string
     */
    public static function table(string $name): string
    {
        return strtolower(Str::plural(Str::snake($name)));
    }

    /**
     * @param string $name
     * @return string
     */
    public static function model(string $name): string
    {
        return ucfirst(Str::singular(Str::studly($name)));
    }

    /**
     * @param string $name
     * @return string
     */
    public static function column(string $name): string
    {
        return strtolower(Str::snake($name));
    }
}
