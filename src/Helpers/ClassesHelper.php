<?php

/**
 * @param string $classPath the path of the class you want to add to it
 * @param string $content the content you want to add
 * @return void
 */
function addToClass(string $classPath, string $content): void
{
    if (!file_exists($classPath)) {
        echo "\n error : path : $classPath doesn't exist \n";
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

        echo "\n New content has been added successfully to $classPath.\n";
    } else {
        echo "\n Closing curly brace not found in $classPath.\n";
    }
}

function addMethodToClass(string $methodName, string $className, string $classPath, string $methodDeclaration): void
{
    if (!file_exists($classPath)) {
        echo("\n error : $classPath doesn't exists");
        return;
    }

    if (method_exists($className, $methodName)) {
        echo("\n error : $methodName method already exists in $className");
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

// work version with a problem in the addition to the return array
function addToMethodReturnArray(string $classPath, string $className, string $methodName, string $content): bool
{
    if (!file_exists($classPath) || !class_exists($className)) {
        return false;
    }

    if (!method_exists($className, $methodName)) {
        return false;
    }

    $fileContent = file_get_contents($classPath);

    // this pattern search for the method then match its return statement that returns an array
    $pattern = '/public\s+function\s+' . preg_quote($methodName, '/') . '\s*\([^)]*\)\s*(?::[^{;]+)?\s*{\s*return\s*\[(.*?)];\s*}/s';

    // Search for the pattern in the content
    if (preg_match($pattern, $fileContent, $matches)) {

        /************* checking if the content want to add exists in the return array *************/
        $existingElementsNormalized = preg_replace('/\s+/', ' ', $matches[1]);
        $contentNormalized = preg_replace('/\s+/', ' ', $content);

        // Check for duplicated content, ignoring whitespace differences
        if (str_contains($existingElementsNormalized, $contentNormalized)) {
            return false;
        }
        /*******************************************************************************************/

        // Insert the new content immediately before the closing bracket of the array
        $existingElements = rtrim($matches[1], ",");
        $updatedContent = rtrim($content, ","); // Still remove trailing comma from new content
        $updatedContent = str_replace($matches[1], $existingElements . ($existingElements !== "" && !str_ends_with($existingElements, ",") ? "," : "") . "\n\t\t" . $updatedContent, $fileContent);
        /******************************************************************************************************/

        // check for repeated commas
        $updatedContent = preg_replace('/,\s*,+/', ',', $updatedContent);

        file_put_contents($classPath, $updatedContent);

        return true;
    } else {
        return false;
    }
}




//
//function addToMethodReturnArray(string $classPath, string $className, string $methodName, string $content): bool
//{
//    if (!file_exists($classPath) || !class_exists($className)) {
//        return false;
//    }
//
//    if (!method_exists($className, $methodName)) {
//        return false;
//    }
//
//    $fileContent = file_get_contents($classPath);
//
//    // Pattern to match the toArray method and its return statement
//    $pattern = '/public\s+function\s+' . preg_quote($methodName, '/') . '\s*\([^)]*\)\s*(?::[^{;]+)?\s*{\s*return\s*(\[.*?]\s*]);\s*}/s';
//
//    // Search for the pattern in the content
//    if (preg_match($pattern, $fileContent, $matches)) {
//        // Check if the returned array is empty
//        if (empty(trim($matches[1]))) {
//            // If empty, add the new content without a trailing comma
//            $updatedContent = str_replace($matches[1], "\n\t\t" . $content, $fileContent);
//        } else {
//            // If not empty, add the new content before the closing bracket of the array
//            $existingElements = rtrim($matches[1], ",");
//            $updatedContent = rtrim($content, ","); // Still remove trailing comma from new content
//            $updatedContent = str_replace($matches[1], $existingElements . ($existingElements !== "" && !str_ends_with($existingElements, ",") ? "," : "") . "\n\t\t" . $updatedContent, $fileContent);
//        }
//
//        // Check for repeated commas
//        $updatedContent = preg_replace('/,\s*,+/', ',', $updatedContent);
//
//        // Save the updated content back to the class file
//        file_put_contents($classPath, $updatedContent);
//
//        return true;
//    } else {
//        return false;
//    }
//}
