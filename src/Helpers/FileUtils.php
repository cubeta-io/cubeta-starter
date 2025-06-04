<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Info\SuccessMessage;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use function Laravel\Prompts\info;


class FileUtils
{
    /**
     * check if the directory exists if not create it
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
     * format the PHP file on the given path
     * @param $filePath string the project path of the file eg:app/Models/MyModel.php
     * @return void
     */
    public static function formatWithPint(string $filePath): void
    {
        $command = base_path("/vendor/bin/pint") . " {$filePath}";
        self::executeCommandInTheBaseDirectory($command, false);
        CubeLog::add(new SuccessMessage("The File : [{$filePath}] Formatted Successfully"));
    }

    /**
     * format the js|ts|jsx|... file on the given path
     * @param $filePath string the project path of the file eg:resources/js/Pages/page.tsx
     * @return void
     */
    public static function formatWithPrettier(string $filePath): void
    {
        $command = "npx prettier {$filePath} --write";
        self::executeCommandInTheBaseDirectory($command, false);
        CubeLog::add(new SuccessMessage("The File : [{$filePath}] Formatted Successfully"));
    }

    /**
     * @param string $command
     * @param bool   $withLog
     * @return false|string|null
     */
    public static function executeCommandInTheBaseDirectory(string $command, bool $withLog = true): bool|string|null
    {
        if (app()->environment('local')) {
            $rootDirectory = base_path();
            $fullCommand = sprintf('cd %s && %s', escapeshellarg($rootDirectory), $command);

            if (php_sapi_name() == "cli") {
                info("Running command : [$command]");
            }elseif ($withLog) {
                CubeLog::info("Running command : [$command]");
            }

            $output = shell_exec($fullCommand);

            if (is_string($output) && $withLog) {
                CubeLog::add($output);
            }

            return $output;
        }

        CubeLog::wrongEnvironment("Running Command : [$command]");

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

        if (self::importExistsInFile($importStatement, $filePath)) {
            CubeLog::contentAlreadyExists($importStatement, $filePath->fullPath, "Adding Import Statement");
            return;
        }

        $namespacePattern = '/namespace\s*(.*?)\s*;/';
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
     * check if content exists in a file
     * @param CubePath $filePath
     * @param string   $needle
     * @return bool
     */
    public static function contentExistInFile(CubePath $filePath, string $needle): bool
    {
        if (!$filePath->exist()) {
            CubeLog::notFound($filePath->fullPath, "Checking If $needle Exists In it");
            return false;
        }

        $fileContent = $filePath->getContent();

        if (!$fileContent) {
            return false;
        }

        return self::contentExistsInString($fileContent, $needle);
    }

    public static function contentExistsInString(string $haystack, string $needle): bool
    {
        $haystack = self::extraTrim($haystack);

        $needle = self::extraTrim($needle);

        if (str_contains(strtolower($haystack), strtolower($needle)) || $haystack == $needle) {
            return true;
        }

        return false;
    }

    public static function extraTrim(string $string): string
    {
        return trim(preg_replace('/\s+/', '', $string));
    }

    public static function isInPhpArrayString(string $arrayString, string $needle): bool
    {
        return Str::contains($arrayString, "\"$needle\"")
            || Str::contains($arrayString, "'$needle'")
            || preg_match('/\'\s*' . $needle . '\s*\'/', $arrayString)
            || preg_match('/\"\s*' . $needle . '\s*\"/', $arrayString);
    }

    public static function replaceFirstMatch($haystack, $needle, $replace)
    {
        $pos = strpos($haystack, $needle);
        if ($pos !== false) {
            return substr_replace($haystack, $replace, $pos, strlen($needle));
        }
        return $haystack;
    }

    public static function registerMiddleware(string $middlewareArrayItem, MiddlewareArrayGroupEnum $type, PhpImportString $importStatement): bool
    {
        $bootstrapPath = CubePath::make("/bootstrap/app.php");
        if (!$bootstrapPath->exist()) {
            return false;
        }
        self::addImportStatement($importStatement, $bootstrapPath);
        return match ($type) {
            MiddlewareArrayGroupEnum::GLOBAL, MiddlewareArrayGroupEnum::ALIAS => self::registerMiddlewareAliasOrGlobal($middlewareArrayItem, $type),
            MiddlewareArrayGroupEnum::API => self::registerWebOrApiMiddleware($middlewareArrayItem),
            MiddlewareArrayGroupEnum::WEB => self::registerWebOrApiMiddleware($middlewareArrayItem, ContainerType::WEB),
        };
    }

    public static function registerWebOrApiMiddleware($middleware, string $container = ContainerType::API): bool
    {
        $methodName = $container == ContainerType::API ? "api" : "web";
        $bootstrapPath = CubePath::make("/bootstrap/app.php");

        if (!$bootstrapPath->exist()) {
            CubeLog::add(new NotFound($bootstrapPath->fullPath, "Registering $middleware middleware in the $container middlewares group"));
            return false;
        }

        $bootstrapContent = $bootstrapPath->getContent();

        $patternWithMethodExists = '/->\s*withMiddleware\s*\(' .
            '\s*function\s*\(\s*Middleware\s*\$middleware\s*\)\s*\{\s*(.*?)' .
            '\$middleware\s*->\s*' . $methodName . '\s*\(\s*(.*?)\s*append\s*:\s*\[\s*(.*?)\s*]\s*(.*?)\)\s*;' .
            '(.*?)\s*}\s*\)/s';
        if (preg_match($patternWithMethodExists, $bootstrapContent, $matches)) {
            if (isset($matches[3])) {
                if (FileUtils::contentExistsInString($matches[3], $middleware)) {
                    CubeLog::add(new ContentAlreadyExist($middleware, $bootstrapPath->fullPath, "Registering $middleware middleware in the $container middlewares group"));
                    return false;
                }
                $bootstrapContent = preg_replace_callback($patternWithMethodExists, function ($matches) use ($methodName, $middleware) {
                    $middlewaresArray = $matches[3];
                    $middlewaresArray .= ",\n$middleware,\n";
                    $middlewaresArray = FileUtils::fixArrayOrObjectCommas($middlewaresArray);
                    return "->withMiddleware(function (Middleware \$middleware)" .
                        " {\n{$matches[1]}\$middleware->$methodName({$matches[2]}append: [\n{$middlewaresArray}\n]{$matches[4]});\n{$matches[5]}\n})";
                }, $bootstrapContent);
                $bootstrapPath->putContent($bootstrapContent);
                CubeLog::add(new ContentAppended($middleware, $bootstrapPath->fullPath));
                $bootstrapPath->format();
                return true;
            } else {
                CubeLog::add(new FailedAppendContent($middleware, $bootstrapPath->fullPath, "Registering $middleware middleware in the $container middlewares group"));
                return false;
            }
        }

        $patternWithoutMethodExists = '/->\s*withMiddleware\s*\(' .
            '\s*function\s*\(\s*Middleware\s*\$middleware\s*\)\s*\{\s*(.*?)\s*}' .
            '\s*\)\s*/s';
        if (preg_match($patternWithoutMethodExists, $bootstrapContent, $matches)) {
            if (isset($matches[1])) {
                $bootstrapContent = preg_replace_callback($patternWithoutMethodExists, function ($matches) use ($methodName, $middleware) {
                    $registered = $matches[1];
                    $registered .= "\n\$middleware->$methodName(append: [\n$middleware,\n]);\n";
                    return "->withMiddleware(function(Middleware \$middleware) {\n$registered\n})";
                }, $bootstrapContent);
                $bootstrapPath->putContent($bootstrapContent);
                CubeLog::add(new ContentAppended($middleware, $bootstrapPath->fullPath));
                $bootstrapPath->format();
                return true;
            } else {
                CubeLog::add(new FailedAppendContent($middleware, $bootstrapPath->fullPath, "Registering $middleware middleware in the $container middlewares group"));
                return false;
            }
        }

        CubeLog::add(new FailedAppendContent($middleware, $bootstrapPath->fullPath, "Registering $middleware middleware in the $container middlewares group"));
        return false;
    }

    public static function registerMiddlewareAliasOrGlobal($middleware, MiddlewareArrayGroupEnum $type): bool
    {
        $methodName = match ($type) {
            MiddlewareArrayGroupEnum::ALIAS => "alias",
            MiddlewareArrayGroupEnum::GLOBAL => "use",
            default => null
        };

        if (!$methodName) {
            return false;
        }

        $context = "Registering $middleware middleware in " . ($type == MiddlewareArrayGroupEnum::GLOBAL ? "global middlewares group" : "middlewares aliases");

        $bootstrapPath = CubePath::make("/bootstrap/app.php");

        if (!$bootstrapPath->exist()) {
            CubeLog::add(new NotFound($bootstrapPath->fullPath, $context));
            return false;
        }

        $bootstrapContent = $bootstrapPath->getContent();

        $patternWithMethodExists = '/->\s*withMiddleware\s*\(' .
            '\s*function\s*\(\s*Middleware\s*\$middleware\s*\)\s*\{\s*(.*?)' .
            '\$middleware\s*->\s*' . $methodName . '\s*\(\s*\[\s*(.*?)\s*]\s*\)\s*;' .
            '(.*?)\s*}\s*\)/s';
        if (preg_match($patternWithMethodExists, $bootstrapContent, $matches)) {
            if (isset($matches[2])) {
                if (FileUtils::contentExistsInString($matches[2], $middleware)) {
                    CubeLog::add(new ContentAlreadyExist($middleware, $bootstrapPath->fullPath, $context));
                    return false;
                }
                $bootstrapContent = preg_replace_callback($patternWithMethodExists, function ($matches) use ($methodName, $middleware) {
                    $middlewaresArray = $matches[2];
                    $middlewaresArray .= "\n,$middleware,\n";
                    $middlewaresArray = FileUtils::fixArrayOrObjectCommas($middlewaresArray);
                    return "->withMiddleware(function (Middleware \$middleware)" .
                        " {\n{$matches[1]}\$middleware->{$methodName}([\n{$middlewaresArray}\n]);\n{$matches[3]}\n})";
                }, $bootstrapContent);
                $bootstrapPath->putContent($bootstrapContent);
                CubeLog::add(new ContentAppended($middleware, $bootstrapPath->fullPath));
                $bootstrapPath->format();
                return true;
            } else {
                CubeLog::add(new FailedAppendContent($middleware, $bootstrapPath->fullPath, $context));
                return false;
            }
        }

        $patternWithoutMethodExists = '/->\s*withMiddleware\s*\(' .
            '\s*function\s*\(\s*Middleware\s*\$middleware\s*\)\s*\{\s*(.*?)\s*}' .
            '\s*\)\s*/s';
        if (preg_match($patternWithoutMethodExists, $bootstrapContent, $matches)) {
            if (isset($matches[1])) {
                $bootstrapContent = preg_replace_callback($patternWithoutMethodExists, function ($matches) use ($methodName, $middleware) {
                    $registered = $matches[1];
                    $registered .= "\n\$middleware->{$methodName}([\n$middleware,\n]);\n";
                    return "->withMiddleware(function(Middleware \$middleware) {\n$registered\n})";
                }, $bootstrapContent);
                $bootstrapPath->putContent($bootstrapContent);
                CubeLog::add(new ContentAppended($middleware, $bootstrapPath->fullPath));
                $bootstrapPath->format();
                return true;
            } else {
                CubeLog::add(new FailedAppendContent($middleware, $bootstrapPath->fullPath, $context));
                return false;
            }
        }

        CubeLog::add(new FailedAppendContent($middleware, $bootstrapPath->fullPath, $context));
        return false;
    }

    public static function removeRepeatedCommas(string $string, bool $newLine = true): array|string|null
    {
        return preg_replace('/(,\s*)+/', $newLine ? ",\n" : ",", $string);
    }

    public static function registerProvider(string $provider): void
    {
        $configPath = CubePath::make('/bootstrap/providers.php');

        if (!$configPath->exist()) {
            CubeLog::add(new NotFound($configPath->fullPath, "Registering [$provider] Provider"));
            return;
        }

        $configContent = $configPath->getContent();

        $pattern = '/\s*return\s*\[\s*(.*?)\s*]\s*/';

        if (preg_match($pattern, $configContent, $matches)) {
            if (!isset($matches[1])) {
                CubeLog::add(new FailedAppendContent($provider, $configPath->fullPath, "Registering [$provider] Provider"));
                return;
            }

            $providers = $matches[1];
            if (self::contentExistsInString($providers, $provider)) {
                CubeLog::add(new ContentAlreadyExist($provider, $configPath->fullPath, "Registering [$provider] Provider"));
                return;
            }

            $providers = $providers . ",\n" . $provider . ",\n";
            $providers = self::fixArrayOrObjectCommas($providers);
            $configContent = str_replace($matches[1], $providers, $configContent);
            $configPath->putContent($configContent);
            CubeLog::add(new ContentAppended($provider, $configPath->fullPath));
            $configPath->format();
            return;
        }

        CubeLog::add(new FailedAppendContent($provider, $configPath->fullPath, "Registering [$provider] Provider"));
    }

    public static function fixArrayOrObjectCommas(string $input): array|string|null
    {
        $input = trim($input, " \t\n\r\0\x0B,");
        return self::removeRepeatedCommas($input);
    }

    public static function generateStringFromStub(string $stubPath, array $properties = []): string
    {
        $search = [];
        $replace = [];

        foreach ($properties as $key => $value) {
            $search[] = "{$key}";
            $replace[] = "$value";
        }

        $stub = file_get_contents($stubPath);
        return str_replace($search, $replace, $stub);
    }

    public static function getReactComponentPropPatterns(string $propName, ?string $value = null): string
    {
        $value = $value != null ? $value : '[^>]*';
        return '((' . $propName . '\s*=\s*{\s*\'\s*' . $value . '\s*\'\s*})|('
            . $propName . '\s*=\s*{\s*"\s*' . $value . '\s*"\s*})|('
            . $propName . '\s*=\s*{\s*`\s*' . $value . '\s*`\s*})|('
            . $propName . '\s*=\s*{\s*' . $value . '\s*})|('
            . $propName . '\s*=\s*\'\s*' . $value . '\s*\'\s*)|('
            . $propName . '\s*=\s*"\s*' . $value . '\s*"\s*))';
    }

    public static function formatCodeString(string $code): string
    {
        $prettier = shell_exec("echo " . escapeshellarg($code) . " | prettier --parser babel");
        if (isset($prettier) && str(self::extraTrim($prettier))->length() >= str(self::extraTrim($code))->length()) {
            $code = $prettier;
        }

        ob_start();
        highlight_string($code);
        $highlight = ob_get_clean();
        return strip_tags(str_replace(['<br />', '&nbsp;'], ["\n", ' '], $highlight));
    }

    public static function importExistsInFile(string|PhpImportString $importString, CubePath $file): bool
    {
        if (!$file->exist()) {
            throw new Exception("File Doesn't Exists : [$file->fullPath] while checking if an import exists in it");
        }

        $content = $file->getContent();

        if ($importString instanceof PhpImportString) {
            $importedClass = trim($importString->classFullName, "\\");
        } else {
            if (!preg_match('/use\s*(.*?);/s', $importString, $matches)) {
                throw new Exception("Invalid import string [$importString] while checking if an import exists in file [$file->fullPath]");
            }
            $importedClass = trim($matches[1], "\\");
        }


        return (bool)preg_match(
            '/use\s*' . preg_quote($importedClass, '/') . '\s*;/s',
            $content,
        );
    }

    public static function migrationExists(string $tableName): ?string
    {
        $migrationsPath = base_path(config('cubeta-starter.migration_path'));

        FileUtils::ensureDirectoryExists($migrationsPath);

        $allMigrations = File::allFiles($migrationsPath);

        foreach ($allMigrations as $migration) {
            $migrationName = $migration->getBasename();
            if (Str::contains($migrationName, "create_{$tableName}_table.php")) {
                return $migration->getRealPath();
            }
        }

        return null;
    }
}
