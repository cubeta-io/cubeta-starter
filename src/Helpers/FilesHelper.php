<?php

use Cubeta\CubetaStarter\CreateFile;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

/**
 * @param array $stubProperties stub elements to replace
 * @param string $path the path to the created file
 * @param string $stubPath stub path
 * @return void
 * @throws BindingResolutionException
 * @throws FileNotFoundException
 */
function generateFileFromStub(array $stubProperties, string $path, string $stubPath): void
{
    CreateFile::make()->setPath($path)->setStubPath($stubPath)->setStubProperties($stubProperties)->callFileGenerateFunctions();
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
