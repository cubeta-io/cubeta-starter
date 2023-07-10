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

/**
 * return the lower case and the plural in kebab case of the input string
 * @param string $name
 * @return string
 */
function lowerPluralKebabNaming(string $name): string
{
    return strtolower(Str::plural(Str::kebab($name)));
}


/**
 * return the name based on name convention for routes
 */
function routeUrlNaming($name): string
{
    return lowerPluralKebabNaming($name);
}

/**
 * return the used name of the model for the route name
 */
function routeNameNaming(string $name): string
{
    return str_replace('-', '.', lowerPluralKebabNaming($name));
}

/**
 * return the name based on name convention for relation functions in the models
 */
function relationFunctionNaming($name, bool $singular = true): string
{
    if ($singular) {
        return Str::camel(lcfirst(Str::singular(Str::studly($name))));
    }
    return Str::camel(lcfirst(Str::plural(Str::studly($name))));
}

/**
 * @param string $name
 * @return string
 */
function viewNaming(string $name): string
{
    return lowerPluralKebabNaming($name);
}

/**
 * @param string $name
 * @return string
 */
function columnNaming(string $name): string
{
    return strtolower(Str::snake($name));
}

/**
 * @param string $name
 * @return string
 */
function titleNaming(string $name): string
{
    return Str::headline($name);
}

/**
 * return the role enum for a given string
 * @param string $name
 * @return string
 */
function roleNaming(string $name): string
{
    return Str::singular(Str::upper(Str::snake($name)));
}
