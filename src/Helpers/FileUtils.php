<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Errors\WrongEnvironment;
use Cubeta\CubetaStarter\Logs\Info\SuccessMessage;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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
     * @param array  $stubProperties
     * @param string $path
     * @param string $stubPath
     * @param bool   $override
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
     * format the php file on the given path
     * @param $filePath string the project path of the file eg:app/Models/MyModel.php
     * @return void
     */
    public static function formatPhpFile(string $filePath): void
    {
        $command = base_path() . "./vendor/bin/pint {$filePath}";
        self::executeCommandInTheBaseDirectory($command);
        CubeLog::add(new SuccessMessage("The File : [{$filePath}] Formatted Successfully"));
    }

    /**
     * format the js|ts|jsx|... file on the given path
     * @param $filePath string the project path of the file eg:resources/js/Pages/page.tsx
     * @return void
     */
    public static function formatJsFile(string $filePath): void
    {
        $command = "npx prettier {$filePath} --write";
        self::executeCommandInTheBaseDirectory($command);
        CubeLog::add(new SuccessMessage("The File : [{$filePath}] Formatted Successfully"));
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

        CubeLog::add(new WrongEnvironment("Running Command : $command"));

        return false;
    }

    /**
     * add the use statement to the top of the desired file
     * @param string   $importStatement
     * @param CubePath $filePath
     * @return void
     */
    public static function addImportStatement(string $importStatement, CubePath $filePath): void
    {
        $contents = $filePath->getContent();

        if (self::contentExistInFile($filePath, $importStatement)) {
            CubeLog::add(new ContentAlreadyExist($importStatement, $filePath->fullPath, "Adding Import Statement"));
            return;
        }

        $namespacePattern = '/namespace\s+([A-Za-z0-9]+(\\\\*[A-Za-z0-9]+)+);/';
        // Check if the namespace declaration exists
        if (preg_match($namespacePattern, $contents, $matches)) {
            $contents = str_replace($matches[0], "{$matches[0]} \n$importStatement\n", $contents);
        } else {
            // No namespace declaration found, add the import statement after the opening PHP tag
            $contents = str_replace("<?php", "<?php\n$importStatement\n", $contents);
        }

        // Write the updated contents back to the file
        $filePath->putContent($contents);
        $filePath->format();
    }

    public static function tsAddImportStatement(string $importStatement, CubePath $filePath): void
    {
        if (self::contentExistInFile($filePath, $importStatement)) {
            return;
        }

        $fileContent = $filePath->getContent();
        $fileContent = "\n{$importStatement}\n{$fileContent}";
        $filePath->putContent($fileContent);
        $filePath->format();
    }


    /**
     * check if content exist in a file
     * @param CubePath $filePath
     * @param string   $content
     * @return bool
     */
    public static function contentExistInFile(CubePath $filePath, string $content): bool
    {
        $fileContent = $filePath->getContent();

        if (!$fileContent) {
            return false;
        }

        $fileContent = self::extraTrim($fileContent);

        $content = self::extraTrim($content);

        if (str_contains(strtolower($fileContent), strtolower($content))) {
            return true;
        }

        return false;
    }

    public static function extraTrim(string $string): string
    {
        return trim(preg_replace('/\s+/', '', $string));
    }

    /**
     * @param string $subject
     * @param string $contentToAdd
     * @param string $pattern
     * @return array|string|null
     */
    public static function appendToFirstMatch(string $subject, string $contentToAdd, string $pattern): array|string|null
    {
        return preg_replace($pattern, '$0' . $contentToAdd, $subject);
    }

    /**
     * this function check for a php file syntax error by running php -l command on the file
     * @param CubePath $path
     * @return bool
     */
    public static function checkForSyntaxErrors(CubePath $path): bool
    {
        // PHP interpreter with the '-l' flag to check for syntax errors
        $output = shell_exec("php -l {$path->fullPath}");

        return str_contains($output, 'No syntax errors detected');
    }

    /**
     * @param string $pattern
     * @param string $replacement
     * @param string $subject
     * @return string
     */
    public static function prependLastMatch(string $pattern, string $replacement, string $subject): string
    {
        preg_match_all($pattern, $subject, $matches, PREG_OFFSET_CAPTURE);

        // Get the offset of the last match
        $lastMatchOffset = end($matches[0])[1];

        // Replace the last match with the new content
        return substr_replace($subject, $replacement, $lastMatchOffset, 0);
    }

    public static function tsAddPropertyToInterface(
        CubePath $filePath,
        string   $interfaceName,
        string   $propertyName,
        string   $propertyType,
        bool     $isOptional = true
    ): void
    {
        if (!$filePath->exist()) {
            CubeLog::add(new NotFound($filePath->fullPath, "Trying to add new property [$propertyName] to [$interfaceName] TS interface"));
            return;
        }

        $fileContent = $filePath->getContent();

        // Regular expression to match the specific interface block
        $pattern = '/(export\s+interface\s+' . preg_quote($interfaceName, '/') . '\s*{)([^}]*)}/s';

        $newProperty = $propertyName . ($isOptional ? '?: ' : ': ') . $propertyType;

        if (preg_match($pattern, $fileContent, $matches)) {
            $interfaceBody = $matches[2];

            // Check if the property already exists
            if (Str::contains(self::extraTrim($interfaceBody), self::extraTrim($newProperty))) {
                CubeLog::add(new ContentAlreadyExist(
                    $newProperty,
                    $filePath->fullPath,
                    "Trying to add new property [$propertyName] to [$interfaceName] TS interface"
                ));
                return;
            }

            // Insert the new property before the closing brace
            $modifiedInterfaceBody = $interfaceBody . "\n    " . $newProperty . ";";
            $modifiedInterfaceCode = $matches[1] . $modifiedInterfaceBody . "\n}";
            $modifiedFileContent = str_replace($matches[0], $modifiedInterfaceCode, $fileContent);
            $filePath->putContent($modifiedFileContent);
        } else {
            CubeLog::add(new FailedAppendContent($newProperty, $filePath->fullPath, "Trying to add new property [$propertyName] to [$interfaceName] TS interface"));
        }
    }
}
