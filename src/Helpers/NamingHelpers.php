<?php

use Illuminate\Support\Str;

/**
 * return the variable name of a given string
 * @param string $string
 * @return string
 */
function variableNaming(string $string): string
{
    return Str::singular(Str::camel($string));
}

/**
 * return the lower & plural mode name of a given string
 * @param string $string
 * @return string
 */
function lowerPluralName(string $string): string
{
    return strtolower(Str::plural($string));
}

/**
 * return the name based on name convention for tables
 * @param $name
 * @return string
 */
function tableNaming($name): string
{
    return strtolower(Str::plural(Str::snake($name)));
}

/**
 * return the name based on name convention for models
 * @param $name
 * @return string
 */
function modelNaming($name): string
{
    return ucfirst(Str::singular(Str::studly($name)));
}

/**
 * @param $modelName
 * @return string
 */
function controllerNaming($modelName): string
{
    return $modelName . 'Controller';
}

/**
 * return the name based on name convention for routes
 * @param $name
 * @return string
 */
function routeUrlNaming($name): string
{
    return strtolower(Str::plural(Str::snake($name)));
}

/**
 * return the name based on name convention for relation functions in the models
 * @param $name
 * @param bool $singular
 * @return string
 */
function relationFunctionNaming($name, bool $singular = true): string
{
    if ($singular) {
        return Str::camel(lcfirst(Str::singular(Str::studly($name))));
    } else {
        return Str::camel(lcfirst(Str::plural(Str::studly($name))));
    }
}


