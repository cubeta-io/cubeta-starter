<?php

function getModelClassName(string $modelName): string
{
    return config("cubeta-starter.model_namespace", "App\Models") . "\\" . modelNaming($modelName);
}

function getModelPath(string $modelName): string
{
    return config("cubeta-starter.model_path", "app/Models") . "/" . modelNaming($modelName) . ".php";
}

