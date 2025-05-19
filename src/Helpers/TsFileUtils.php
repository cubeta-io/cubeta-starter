<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;

class TsFileUtils
{
    public static function addPropertyToInterface(
        CubePath                $interfacePath,
        InterfacePropertyString $interfacePropertyString
    ): void
    {
        $interfaceName = $interfacePath->getFileNameWithoutExtension();
        $propertyName = $interfacePropertyString->name;
        if (!$interfacePath->exist()) {
            CubeLog::add(new NotFound($interfacePath->fullPath, "Trying to add new property [$propertyName] to [$interfaceName] TS interface"));
            return;
        }

        $fileContent = $interfacePath->getContent();

        $pattern = '/interface\s*' . preg_quote($interfaceName, '/') . '\s*\{(.*?)}/s';

        if (preg_match($pattern, $fileContent, $matches)) {
            $interfaceBody = $matches[1];

            if (FileUtils::contentExistInFile($interfacePath, $interfacePropertyString)) {
                CubeLog::contentAlreadyExists(
                    $interfacePropertyString,
                    $interfacePath->fullPath,
                    "Trying to add new property [$propertyName] to [$interfaceName] TS interface"
                );
                return;
            }

            $modifiedInterfaceBody = "$interfaceBody\n$interfacePropertyString";
            $fileContent = str_replace($matches[1], $modifiedInterfaceBody, $fileContent);
            $interfacePath->putContent($fileContent);
            $interfacePath->format();
        } else {
            CubeLog::failedAppending($interfacePropertyString, $interfacePath->fullPath, "Trying to add new property [$propertyName] to [$interfaceName] TS interface");
        }
    }

    /**
     * @param string|string[]|TsImportString[]|TsImportString $importString
     * @param CubePath                                        $filePath
     * @return void
     */
    public static function addImportStatement(string|array|TsImportString $importString, CubePath $filePath): void
    {
        if (is_array($importString)) {
            foreach ($importString as $item) {
                self::addImportStatement($item, $filePath);
            }
        } else {
            if (FileUtils::contentExistInFile($filePath, "$importString")) {
                return;
            }

            $fileContent = $filePath->getContent();
            $fileContent = "\n{$importString}\n{$fileContent}";
            $filePath->putContent($fileContent);
            $filePath->format();
        }
    }
}