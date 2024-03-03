<?php


function getModelClassName(string $modelName): string
{
    return config("cubeta-starter.model_namespace", "App\Models") . "\\" . modelNaming($modelName);
}

function getModelPath(string $modelName): string
{
    return base_path(config("cubeta-starter.model_path", "app/Models") . "/" . modelNaming($modelName) . ".php");
}

function getFactoryClassName(string $modelName): string
{
    return config("cubeta-starter.factory_namespace", "Database\Factories") . "\\" . modelNaming($modelName) . "Factory";
}

function getFactoryPath(string $modelName): string
{
    return base_path(config("cubeta-starter.factory_path", "database/factories") . "/" . modelNaming($modelName) . "Factory.php");
}

function getResourceClassName(string $modelName): string
{
    $resourceName = resourceNaming($modelName);
    return config("cubeta-starter.resource_namespace", "App\Http\Resources") . "\\$resourceName";
}

function getResourcePath(string $modelName): string
{
    $resourceName = resourceNaming($modelName);
    return base_path(config("cubeta-starter.resource_path", 'app/Http/Resources')) . "/$resourceName.php";
}

function getApiControllerPath(string $modelName): string
{
    $controllerName = modelNaming($modelName) . "Controller.php";
    return base_path(config('cubeta-starter.api_controller_path', 'app/Http/Controllers/API/v1')) . $controllerName;
}

function getWebControllerPath(string $modelName): string
{
    $controllerName = modelNaming($modelName) . "Controller.php";
    return base_path(config('cubeta-starter.web_controller_path', 'app/Http/Controllers/WEB/v1')) . "/" . $controllerName;
}

