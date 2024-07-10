<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Errors\WrongEnvironment;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
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
     * check if content exist in a file
     * @param CubePath $filePath
     * @param string   $content
     * @return bool
     */
    public static function contentExistInFile(CubePath $filePath, string $content): bool
    {
        if (!$filePath->exist()) {
            CubeLog::add(new NotFound($filePath->fullPath, "Checking If $content Exists In it"));
        }

        $fileContent = $filePath->getContent();

        if (!$fileContent) {
            return false;
        }

        return self::contentExistsInString($fileContent, $content);
    }

    public static function contentExistsInString(string $string, string $content): bool
    {
        $string = self::extraTrim($string);

        $content = self::extraTrim($content);

        if (str_contains(strtolower($string), strtolower($content))) {
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

    public static function isInPhpArrayString(string $arrayString, string $needle): bool
    {
        return Str::contains($arrayString, "\"$needle\"")
            || Str::contains($arrayString, "'$needle'")
            || preg_match('/\'\s*' . $needle . '\s*\'/', $arrayString)
            || preg_match('/\"\s*' . $needle . '\s*\"/', $arrayString);
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

    public static function replaceFirstMatch($haystack, $needle, $replace)
    {
        $pos = strpos($haystack, $needle);
        if ($pos !== false) {
            return substr_replace($haystack, $replace, $pos, strlen($needle));
        }
        return $haystack;
    }

    public static function addReactTSApiSelectToForm(string $content, $formInterfaceProperty, CubePath $filePath)
    {
        $operationContext = "Trying To Add New ApiSelect Component To The Form";
        if (!$filePath->exist()) {
            CubeLog::add(new NotFound(
                $filePath->fullPath,
                $operationContext
            ));
            return;
        }

        $fileContent = $filePath->getContent();

        if (FileUtils::contentExistInFile($filePath, $content)) {
            CubeLog::add(new ContentAlreadyExist($content, $filePath->fullPath, $operationContext));
            return;
        }

        $firstPattern = '#<Form\s*(.*?)\s*>\s*<div\s*(.*?)\s*>\s*(.*?)\s*</div>#s';
        $secondPattern = '#<Form\s*(.*?)\s*>\s*(.*?)\s*</Form>#s';

        if (preg_match($firstPattern, $fileContent, $matches)) {
            $formContent = $matches[3];
            $substitute = $matches[3];
        } elseif (preg_match($secondPattern, $fileContent, $matches)) {
            $formContent = $matches[2];
            $substitute = $matches[2];
        } else {
            CubeLog::add(new FailedAppendContent(
                $content,
                $filePath->fullPath,
                $operationContext
            ));
            return;
        }

        $formContent .= "\n$content\n";
        $fileContent = str_replace($substitute, $formContent, $fileContent);

        // adding new property
        $formInterfacePattern = '#useForm\s*<\s*\{\s*(.*?)\s*}\s*>#s';
        if (preg_match($formInterfacePattern, $fileContent, $matches)
            && !FileUtils::contentExistInFile($filePath, $formInterfaceProperty)
        ) {
            $interfaceProperties = $matches[1];
            $interfaceProperties .= "\n$formInterfaceProperty\n";
            $fileContent = str_replace($matches[1], $interfaceProperties, $fileContent);
        } else {
            CubeLog::add(new FailedAppendContent(
                $formInterfaceProperty,
                $filePath->fullPath,
                $operationContext
            ));
            return;
        }


        $filePath->putContent($fileContent);
        CubeLog::add(new ContentAppended($content, $filePath->fullPath));
        $filePath->format();
    }

    public static function registerMiddleware(string $middlewareArrayItem, MiddlewareArrayGroupEnum $type , string $importStatement): bool
    {
        $bootstrapPath = CubePath::make("/bootstrap/app.php");
        if (!$bootstrapPath->exist()){
            return false;
        }
        self::addImportStatement($importStatement , $bootstrapPath);
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
                    $middlewaresArray .= "\n$middleware,\n";
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
                    $middlewaresArray .= "\n$middleware,\n";
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

    public static function removeRepeatedCommas(string $string): array|string|null
    {
        return preg_replace('/(,\s*)+/', ",\n", $string);
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
}
