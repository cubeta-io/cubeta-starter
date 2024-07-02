<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Cubeta\CubetaStarter\Logs\Warnings\ContentNotFound;

class ClassUtils
{
    public static function addMethodToClass(CubePath $classPath, string $methodName, string $methodDeclaration): void
    {
        if (!$classPath->exist()) {
            CubeLog::add(new NotFound($classPath->fullPath, "Trying To Add The Method : ($methodName) To $classPath->fileName"));
            return;
        }

        if (ClassUtils::isMethodDefined($classPath, $methodName)) {
            CubeLog::add(new ContentAlreadyExist("$methodName Method", $classPath->fullPath, "Trying To Add The Method : ($methodName) To $classPath->fileName"));
            return;
        }

        self::addToClass($classPath, $methodDeclaration);
    }

    /**
     * @param CubePath $filePath
     * @param string   $functionName
     * @return bool
     */
    public static function isMethodDefined(CubePath $filePath, string $functionName): bool
    {
        if (!$filePath->exist()) {
            return false; // File doesn't exist
        }

        $tokens = token_get_all(file_get_contents($filePath->fullPath));

        $isFunction = false;
        foreach ($tokens as $token) {
            if (is_array($token) && $token[0] == T_FUNCTION) {
                $isFunction = true;
            } elseif ($isFunction && is_array($token) && $token[0] == T_STRING) {
                // Found a function name
                $currentFunctionName = $token[1];
                if ($currentFunctionName == $functionName) {
                    return true; // Function is defined in the file
                }
                $isFunction = false; // Reset flag after checking this function
            }
        }

        return false; // Function not found in the file
    }

    /**
     * @param CubePath $classPath the path of the class you want to add to it
     * @param string   $content   the content you want to add
     * @return void
     */
    public static function addToClass(CubePath $classPath, string $content): void
    {
        if (!$classPath->exist()) {
            CubeLog::add(new NotFound(
                $classPath->fullPath,
                "Trying To Add The Following Content:\n $content \n To $classPath->fileName"
            ));
            return;
        }
        $currentContent = $classPath->getContent();

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

            $classPath->putContent($updatedContent);

            CubeLog::add(new ContentAppended($content, $classPath->fullPath));

            $classPath->format();
        } else {
            CubeLog::add(new ContentNotFound(
                "Closing curly brace",
                $classPath->fullPath,
                "Trying To Add The Following Content:\n $content \n To $classPath->fileName"
            ));
        }
    }

    public static function addToMethodReturnArray(CubePath $classPath, string $className, string $methodName, string $content): bool
    {
        if (!$classPath->exist()) {
            CubeLog::add(new NotFound(
                $classPath->fullPath,
                "Adding The Following Content \n $content \n To The Returned Array Of The Method : [$methodName]"
            ));
            return false;
        }

        if (!self::isMethodDefined($classPath, $methodName)) {
            CubeLog::add(new ContentNotFound(
                "$methodName Method",
                $classPath->fullPath,
                "Adding The Following Content \n $content \n To The Returned Array Of The Method : [$methodName]"
            ));
            return false;
        }

        $fileContent = $classPath->getContent();

        // this pattern search for the method then match its return statement that returns an array
        $pattern = '/public\s*(static)?\s*function\s+' . preg_quote($methodName, '/') . '\s*\((.*?)\)\s*:?(.*?)\{\s*(.*?)\s*return\s*\[(.*?)]\s*;\s*}/s';//

        if (preg_match($pattern, $fileContent, $matches)) {

            if (!isset($matches[5])) {
                CubeLog::add(new FailedAppendContent($content,
                    $classPath->fullPath,
                    "Adding The Following Content\n$content\nTo The Returned Array Of The Method : [$methodName]"
                ));
                return false;
            }
            $returnArray = $matches[5];

            if (FileUtils::contentExistsInString($returnArray, $content)) {
                CubeLog::add(new ContentAlreadyExist(
                    $content,
                    $classPath->fullPath,
                    "Adding The Following Content\n$content\nTo The Returned Array Of The Method : [$methodName]"
                ));
                return false;
            }

            $fileContent = preg_replace_callback($pattern, function ($matches) use ($content, $methodName) {
                $returnArray = $matches[5] . ",\n" . $content;
                $returnArray = FileUtils::fixArrayOrObjectCommas($returnArray);
                return 'public ' . ($matches[1] ? 'static ' : '') . 'function ' . $methodName . '(' . $matches[2] . ')' . ($matches[3] ? ':' . $matches[3] : '') . " \n{\n" . $matches[4] . " return [\n" . $returnArray . "\n];\n}";
            }, $fileContent);
            $fileContent = str_replace($matches[5], $returnArray, $fileContent);
            $classPath->putContent($fileContent);

            CubeLog::add(new ContentAppended($content, $classPath->fullPath));
            $classPath->format();

            return true;
        } else {
            CubeLog::add(new CubeError(
                message: "Failed To Get A Match For A Method Called ($methodName) And Return An Array In [{$classPath->fullPath}]",
                happenedWhen: "Adding The Following Content \n $content \n To The Returned Array Of The Method : [$methodName]"
            ));
            return false;
        }
    }

    public static function addNewRelationsToWithMethod(CubePath $filePath, CubeTable $table, array $additionalRelations): bool
    {
        if (!$filePath->exist()) {
            CubeLog::add(new NotFound($filePath->fullPath, "Trying To Add [ " . implode(" , ", $additionalRelations) . " ] To The With Method"));
            return false;
        }

        $content = $filePath->getContent();

        $pattern = "/$table->modelName::.*?->with\((.*?)\)/s";

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
            CubeLog::add(new CubeError(
                message: "No Match Found To Add New Relations To with() Method In [$filePath->fullPath] \n",
                happenedWhen: "Trying To Add [ " . implode(" , ", $additionalRelations) . " ] To The With Method"
            ));
            return false;
        }

        $filePath->putContent($updated);
        CubeLog::add(new ContentAppended(implode(" , ", $additionalRelations), $filePath->fullPath));

        $filePath->format();

        return true;
    }
}
