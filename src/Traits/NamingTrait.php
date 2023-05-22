<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Support\Str;

trait NamingTrait
{
    /**
     * return the variable name of a given string
     * @param string $string
     * @return string
     */
    function variableNaming(string $string): string
    {
        return Str::singular(Str::camel($string)) ;
    }

    /**
     * return the lower & plural mode name of a given string
     * @param string $string
     * @return string
     */
    public function lowerPluralName(string $string): string
    {
        return strtolower(Str::plural($string)) ;
    }
}
