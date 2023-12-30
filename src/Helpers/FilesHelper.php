<?php

use Cubeta\CubetaStarter\CreateFile;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * @param array $stubProperties
 * @param string $path
 * @param string $stubPath
 * @param bool $override override file if exist
 * @return void
 * @throws BindingResolutionException
 * @throws FileNotFoundException
 */
function generateFileFromStub(array $stubProperties, string $path, string $stubPath, bool $override = false): void
{
    CreateFile::make()->setPath($path)->setStubPath($stubPath)->setStubProperties($stubProperties)->callFileGenerateFunctions($override);
}

/**
 * check if the directory exist if not create it
 * @param string $directory
 * @return void
 */
function ensureDirectoryExists(string $directory): void
{
    if (!File::isDirectory($directory)) {
        File::makeDirectory($directory, 0775, true, true);
    }
}

/**
 * add the use statement to the top of the desired file
 * @param string $importStatement
 * @param string $filePath
 * @return void
 */
function addImportStatement(string $importStatement, string $filePath): void
{
    $contents = file_get_contents($filePath);

    if (Str::contains($contents, $importStatement)) {
        return;
    }

    // Check if import statement already exists
    $fileLines = File::lines($filePath);
    foreach ($fileLines as $line) {
        $cleanLine = trim($line);
        if (Str::contains($cleanLine, $importStatement)) {
            return;
        }
    }

    // Find the last "use" statement and insert the new import statement after it
    $lastUseIndex = strrpos($contents, 'use ');
    $insertIndex = $lastUseIndex !== false ? $lastUseIndex - 1 : 0;
    $contents = substr_replace($contents, "\n" . $importStatement . "\n", $insertIndex, 0);

    // Write the updated contents back to the file
    file_put_contents($filePath, $contents);
}

/**
 * this function check for a php file syntax error by running php -l command on the file
 * @param string $path
 * @return bool
 */
function checkForSyntaxErrors(string $path): bool
{
    // PHP interpreter with the '-l' flag to check for syntax errors
    $output = shell_exec("php -l {$path}");

    return str_contains($output, 'No syntax errors detected');
}

/**
 * get the package (cubeta-starter)  json file settings as an array
 * @return array
 */
function getJsonSettings(): array
{
    $filePath = base_path('/settings.json');

    if (!file_exists($filePath)) {
        return [];
    }

    $data = json_decode(
        file_get_contents(
            $filePath
        ),
        true
    );

    if (!$data) {
        return [];
    } else return $data;
}

/**
 * store the provided array in the package (cubeta-starter) json file settings as an array
 * @param array $data
 * @return void
 */
function storeJsonSettings(array $data): void
{
    file_put_contents(
        base_path('/settings.json'),
        json_encode($data, JSON_PRETTY_PRINT)
    );
}

/**
 * @param string $classPath the path of the class you want to add to it
 * @param string $content the content you want to add
 * @return void
 */
function addToClass(string $classPath, string $content): void
{
    if (!file_exists($classPath)) {
        echo "path : $classPath doesn't exist \n";
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
        \Illuminate\Console\Command::error("$classPath doesn't exists");
        return;
    }

    if (method_exists($className, $methodName)) {
        \Illuminate\Console\Command::error("$methodName method already exists in $className");
        return;
    }

    addToClass($classPath, $methodDeclaration);
}
