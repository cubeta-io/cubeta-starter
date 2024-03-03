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

    if (isMethodDefined($classPath, $methodName)) {
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

function addToMethodReturnArray(string $classPath, string $className, string $methodName, string $content): bool
{
    if (!file_exists($classPath)) {
        echo "Targeted Class Or File Doesn't Exists \n";
        return false;
    }

    if (!isMethodDefined($classPath, $methodName)) {
        echo "Method : $methodName Doesn't Exists In The Targeted Class $className \n";
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
        if (str_contains(trim(str_replace(',', '', $existingElementsNormalized)), trim(str_replace(',', '', $contentNormalized)))) {
            echo "The Content You're Trying To Add Is Already Exists \n";
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

        echo "New Content Has Been Added Successfully To : $classPath \n";
        return true;
    } else {
        echo "Failed To Get A Match For A Method Called ($methodName) And Return An Array \n";
        return false;
    }
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


function getViewPath(string $modelName, string $type): string
{
    $viewsPath = base_path('resources/views/dashboard') . '/' . viewNaming($modelName);

    return match ($type) {
        'show' => "$viewsPath/show.blade.php",
        "create" => "$viewsPath/create.blade.php",
        "update", "edit" => "$viewsPath/edit.blade.php",
        "index" => "$viewsPath/index.blade.php",
    };
}


function addNewRelationsToWithMethod(string $modelName, string $filePath, array $additionalRelations): bool
{
    if (!file_exists($filePath)) {
        echo "$filePath Doesn't Exists \n ";
        return false;
    }

    $content = file_get_contents($filePath);

    $pattern = "/$modelName::.*?->with\((.*?)\)/s";

    // Callback function to add relations
    $callback = function ($matches) use ($additionalRelations) {
        $withMethod = $matches[0];

        foreach ($additionalRelations as $key => $relation) {
            if (str_contains($withMethod, $relation)) {
                unset($additionalRelations[$key]);
            }
        }
        $newRelations = "";
        foreach ($additionalRelations as $additionalRelation) {
            if ($additionalRelation != null and strlen(trim($additionalRelation)) > 0) {
                $newRelations .= "'$additionalRelation' , ";
            }
        }
        $result = str_replace('with([', "with([$newRelations, ", $withMethod);
        $filtered = preg_replace('/\\s*,\\s*,/', "", $result);
        return preg_replace('/\\[\\s*,\\s*/', "[", $filtered);
    };

    // Perform the replacement
    $updated = preg_replace_callback($pattern, $callback, $content);

    if (!$updated) {
        echo "No Match Found To Add New Relations To with() Method In [$filePath] \n";
        return false;
    }

    file_put_contents($filePath, $updated);
    echo "$filePath Has Been Updated Successfully \n";
    return true;
}
