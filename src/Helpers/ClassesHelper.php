<?php


/**
 * @param string $classPath the path of the class you want to add to it
 * @param string $content the content you want to add
 * @return void
 */
function addToClass(string $classPath, string $content): void
{
    if (!file_exists($classPath)) {
        echo "error : path : $classPath doesn't exist \n";
        return;
    }
    $currentContent = file_get_contents($classPath);

    // Find the position of the closing curly brace
    $closingBracePosition = strrpos($currentContent, '}');

    if ($closingBracePosition !== false) {
        // Insert the string above the closing curly brace
        $updatedContent = substr_replace(
            $currentContent,
            $content . PHP_EOL,
            $closingBracePosition,
            0
        );

        file_put_contents($classPath, $updatedContent);

        echo "New content has been added successfully to $classPath.\n";
    } else {
        echo "Closing curly brace not found in $classPath.\n";
    }
}

function addMethodToClass(string $methodName, string $className, string $classPath, string $methodDeclaration): void
{
    if (!file_exists($classPath)) {
        echo("error : $classPath doesn't exists");
        return;
    }

    if (method_exists($className, $methodName)) {
        echo("error : $methodName method already exists in $className");
        return;
    }

    addToClass($classPath, $methodDeclaration);
}

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

