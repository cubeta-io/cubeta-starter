<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\LogsMessages\Errors\WrongEnvironment;
use Cubeta\CubetaStarter\LogsMessages\Log;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

class FileUtils
{
    /**
     * check if the directory exist if not create it
     * @param string $directory
     * @return void
     */
    public static function ensureDirectoryExists(string $directory): void
    {
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0775, true, true);
        }
    }

    /**
     * @param array $stubProperties
     * @param string $path
     * @param string $stubPath
     * @param bool $override
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public static function generateFileFromStub(array $stubProperties, string $path, string $stubPath, bool $override = false): void
    {
        CreateFile::make()
            ->setPath($path)
            ->setStubPath($stubPath)
            ->setStubProperties($stubProperties)
            ->callFileGenerateFunctions($override);
    }

    /**
     * format the file on the given path
     *
     * @param $filePath string the project path of the file eg:app/Models/MyModel.php
     * @return void
     */
    public static function formatFile(string $filePath): void
    {
        $command = base_path() . "./vendor/bin/pint {$filePath}";
        $output = self::executeCommandInTheBaseDirectory($command);
        Log::add($output);
    }

    /**
     * @param string $command
     * @return false|string|null
     */
    public static function executeCommandInTheBaseDirectory(string $command): bool|string|null
    {
        if (app()->environment('local')) {
            $rootDirectory = base_path();
            $fullCommand = sprintf('cd %s && %s', escapeshellarg($rootDirectory), $command);

            return shell_exec($fullCommand);
        }

        Log::add(new WrongEnvironment("Running Command : $command"));
        
        return false;
    }
}
