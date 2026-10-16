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
        return str($name)
            ->singular()
            ->pascal()
            ->toString();
    }

    /**
     * @param string $name
     * @return string
     */
    public static function column(string $name): string
    {
        return str($name)
            ->snake()
            ->lower()
            ->toString();
    }

    public static function pivotTableNaming(string $table1, string $table2): string
    {
        $table1 = Str::singular(Naming::table($table1));
        $table2 = Str::singular(Naming::table($table2));
        $tables = [$table1, $table2];
        sort($tables);
        return $tables[0] . '_' . Naming::table($tables[1]);
    }

    public static function role(string $name): string
    {
        return Str::snake(strtolower(Str::singular($name)));
    }
}
