<?php

use Illuminate\Support\Str;

/**
 * return the variable name of a given string
 */
function variableNaming(string $string): string
{
    return Str::singular(Str::camel($string));
}

/**
 * return the lower & plural mode name of a given string
 */
function lowerPluralName(string $string): string
{
    return strtolower(Str::plural($string));
}

/**
 * return the name based on name convention for tables
 */
function tableNaming($name): string
{
    return strtolower(Str::plural(Str::snake($name)));
}

/**
 * return the name based on name convention for models
 */
function modelNaming($name): string
{
    return ucfirst(Str::singular(Str::studly($name)));
}

function controllerNaming($modelName): string
{
    return $modelName.'Controller';
}

/**
 * return the name based on name convention for routes
 */
function routeUrlNaming($name): string
{
    return strtolower(Str::plural(Str::snake($name)));
}

/**
 * return the name based on name convention for relation functions in the models
 */
function relationFunctionNaming($name, bool $singular = true): string
{
    if ($singular) {
        return Str::camel(lcfirst(Str::singular(Str::studly($name))));
    } else {
        return Str::camel(lcfirst(Str::plural(Str::studly($name))));
    }
}
