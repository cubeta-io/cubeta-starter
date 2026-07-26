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
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\FileReference;

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
     * @param array $stubProperties
     * @param string $path
     * @param string $stubPath
     * @param bool $override
     * @return void
     * @throws FileNotFoundException
     */
    public static function generateFileFromStub(array $stubProperties, string $path, #[FileReference(basePath: "src/Stub/stubs/")] string $stubPath, bool $override = false): void
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
     * @param $filePath string the project path of the file eg:resources/js/pages/page.tsx
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
     * @param bool $withLog
     * @return false|string|null
     */
    public static function executeCommandInTheBaseDirectory(string $command, bool $withLog = true): bool|string|null
    {
        if (app()->environment('local')) {
            $rootDirectory = base_path();
            $fullCommand = sprintf('cd %s && %s', escapeshellarg($rootDirectory), $command);

            if ($withLog) {
                CubeLog::info("Running command : [$command]");
            }

            $output = self::runCommandWithPipes(
                command: $command,
                fullCommand: $fullCommand,
                echoOutput: php_sapi_name() == "cli"
            );

            if ($withLog && is_string($output) && !empty($output)) {
                CubeLog::add($output);
            }

            return $output;
        }

        CubeLog::wrongEnvironment("Running Command : [$command]");

        return false;
    }

    /**
     * Run a command through proc_open, capture its output, and optionally echo
     * it in real-time. This replaces shell_exec so stdout and stderr are both
     * captured reliably instead of letting stderr leak past silently.
     */
    private static function runCommandWithPipes(string $command, string $fullCommand, bool $echoOutput): string|false
    {
        $process = proc_open(
            $fullCommand . ' 2>&1',
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            base_path()
        );

        if (!is_resource($process)) {
            CubeLog::error("Failed to execute command: [$command]");
            return false;
        }

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $output = '';

        while (!feof($pipes[1]) || !feof($pipes[2])) {
            $stdout = fread($pipes[1], 4096);
            $stderr = fread($pipes[2], 4096);

            if ($stdout !== '' && $stdout !== false) {
                if ($echoOutput) {
                    echo $stdout;
                    flush();
                }
                $output .= $stdout;
            }

            if ($stderr !== '' && $stderr !== false) {
                if ($echoOutput) {
                    fwrite(STDERR, $stderr);
                    fflush(STDERR);
                }
                $output .= $stderr;
            }

            usleep(10000);
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return $output;
    }

    /**
     * add the use statement to the top of the desired file
     * @param string $importStatement
     * @param CubePath $filePath
     * @return void
     * @throws Exception
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

    public static function tsAddImportStatement(TsImportString $import, CubePath $filePath): void
    {
        if (self::contentExistInFile($filePath, $import)) {
            return;
        }


        $fileContent = $filePath->getContent();

        if (self::tsImportExists($fileContent, $import)) {
            CubeLog::contentAlreadyExists($import , $filePath);
            return;
        }

        $fileContent = "\n{$import}\n{$fileContent}";
        $filePath->putContent($fileContent);
        $filePath->format();
    }

    public static function tsImportExists(string $fileContent, TsImportString $tsImport): bool
    {
        // Normalize line endings so multiline imports are handled consistently
        $content = str_replace(["\r\n", "\r"], "\n", $fileContent);

        $from = preg_quote($tsImport->from, '/');

        // Case 1: Side-effect import → import "path";
        if ($tsImport->import === null) {
            $pattern = '/^\s*import\s+["\']' . $from . '["\']\s*;?/m';
            return preg_match($pattern, $content) === 1;
        }

        $import = preg_quote($tsImport->import, '/');

        // Case 2: Default import → import Name from "path";
        if ($tsImport->default) {
            $pattern = '/^\s*import\s+' . $import . '\s+from\s+["\']' . $from . '["\']\s*;?/m';
            return preg_match($pattern, $content) === 1;
        }

        // Case 3: Named import → import { Name } from "path";
        // Also handles multiline and mixed imports like: import { A, Name, B } from "path";
        $pattern = '/^\s*import\s*(?:type\s+)?\{\s*([^}]*)\s*\}\s*from\s+["\']' . $from . '["\']\s*;?/m';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER) === false) {
            return false;
        }

        foreach ($matches as $match) {
            // Split by comma and check each imported name
            $importsInBraces = array_map('trim', explode(',', $match[1]));

            foreach ($importsInBraces as $imp) {
                // Strip potential aliases: "Name as Alias" → "Name"
                $parts = preg_split('/\s+as\s+/i', $imp);
                $actualName = trim($parts[0]);

                if ($actualName === $tsImport->import) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * check if content exists in a file
     * @param CubePath $filePath
     * @param string $needle
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
            '\s*function\s*\(\s*Middleware\s*\$middleware\s*\)(.*?)\{\s*(.*?)' .
            '\$middleware\s*->\s*' . $methodName . '\s*\(\s*(.*?)\s*append\s*:\s*\[\s*(.*?)\s*](.*?)\)\s*;' .
            '(.*?)\s*}\s*\)/s';
        if (preg_match($patternWithMethodExists, $bootstrapContent, $matches)) {
            if (isset($matches[4])) {
                if (FileUtils::contentExistsInString($matches[4], $middleware)) {
                    CubeLog::add(new ContentAlreadyExist($middleware, $bootstrapPath->fullPath, "Registering $middleware middleware in the $container middlewares group"));
                    return false;
                }
                $bootstrapContent = preg_replace_callback($patternWithMethodExists, function ($matches) use ($methodName, $middleware) {
                    $middlewaresArray = $matches[4];
                    $middlewaresArray .= ",\n$middleware,\n";
                    $middlewaresArray = FileUtils::fixArrayOrObjectCommas($middlewaresArray);
                    return "->withMiddleware(function (Middleware \$middleware){$matches[1]}" .
                        " {\n{$matches[2]}\$middleware->$methodName({$matches[3]}append: [\n{$middlewaresArray}\n]{$matches[5]});\n{$matches[6]}\n})";
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
            '\s*function\s*\(\s*Middleware\s*\$middleware\s*\)(.*?)\{\s*(.*?)\s*}' .
            '\s*\)\s*/s';
        if (preg_match($patternWithoutMethodExists, $bootstrapContent, $matches)) {
            if (isset($matches[2])) {
                $bootstrapContent = preg_replace_callback($patternWithoutMethodExists, function ($matches) use ($methodName, $middleware) {
                    $registered = $matches[2];
                    $registered .= "\n\$middleware->$methodName(append: [\n$middleware,\n]);\n";
                    return "->withMiddleware(function(Middleware \$middleware){$matches[1]} {\n$registered\n})";
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
            '\s*function\s*\(\s*Middleware\s*\$middleware\s*\)(.*?)\{\s*(.*?)' .
            '\$middleware\s*->\s*' . $methodName . '\s*\(\s*\[\s*(.*?)\s*]\s*\)\s*;' .
            '(.*?)\s*}\s*\)/s';
        if (preg_match($patternWithMethodExists, $bootstrapContent, $matches)) {
            if (isset($matches[3])) {
                if (FileUtils::contentExistsInString($matches[3], $middleware)) {
                    CubeLog::add(new ContentAlreadyExist($middleware, $bootstrapPath->fullPath, $context));
                    return false;
                }
                $bootstrapContent = preg_replace_callback($patternWithMethodExists, function ($matches) use ($methodName, $middleware) {
                    $middlewaresArray = $matches[3];
                    $middlewaresArray .= "\n,$middleware,\n";
                    $middlewaresArray = FileUtils::fixArrayOrObjectCommas($middlewaresArray);
                    return "->withMiddleware(function (Middleware \$middleware){$matches[1]}" .
                        " {\n{$matches[2]}\$middleware->{$methodName}([\n{$middlewaresArray}\n]);\n{$matches[4]}\n})";
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
            '\s*function\s*\(\s*Middleware\s*\$middleware\s*\)(.*?)\{\s*(.*?)\s*}' .
            '\s*\)\s*/s';
        if (preg_match($patternWithoutMethodExists, $bootstrapContent, $matches)) {
            if (isset($matches[2])) {
                $bootstrapContent = preg_replace_callback($patternWithoutMethodExists, function ($matches) use ($methodName, $middleware) {
                    $registered = $matches[2];
                    $registered .= "\n\$middleware->{$methodName}([\n$middleware,\n]);\n";
                    return "->withMiddleware(function(Middleware \$middleware){$matches[1]}{\n$registered\n})";
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
