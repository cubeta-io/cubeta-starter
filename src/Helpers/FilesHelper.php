<?php

use Illuminate\Support\Str;
use Cubeta\CubetaStarter\CreateFile;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @param  array  $stubProperties stub elements to replace
 * @param  string  $path the path to the created file
 * @param  string  $stubPath stub path
 *
 * @throws BindingResolutionException
 * @throws FileNotFoundException
 */
function generateFileFromStub(array $stubProperties, string $path, string $stubPath): void
{
    CreateFile::make()->setPath($path)->setStubPath($stubPath)->setStubProperties($stubProperties)->callFileGenerateFunctions();
}

/**
 * check if the directory exist if not create it
 */
function ensureDirectoryExists(string $directory): void
{
    if ( ! File::isDirectory($directory)) {
        File::makeDirectory($directory, 0775, true, true);
    }
}

/**
 * add the use statement to the top of the desired file
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
