<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsInputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;
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

    public static function addColumnToDataTable(CubePath $filePath, DataTableColumnObjectString $newColumn): void
    {
        if (!$filePath->exist()) {
            CubeLog::notFound($filePath->fullPath, "Adding new column to the data table inside the file");
        }

        $fileContent = $filePath->getContent();

        $pattern = '/schema\s*=\s*\{\s*\[(.*?)\s*]\s*}/s';

        if (preg_match($pattern, $fileContent, $matches)) {
            $schemaContent = $matches[1];

            if (FileUtils::contentExistInFile($filePath, $newColumn)) {
                CubeLog::contentAlreadyExists(
                    $newColumn,
                    $filePath->fullPath,
                    "Adding new column to the data table inside the file"
                );
            }

            $pattern = '/\s*}\s*,\s*\{\s*/';
            $modifiedSchemaContent = preg_replace($pattern, "},$newColumn,{", $schemaContent, 1);
            $modifiedSchemaArray = "schema = {[" . $modifiedSchemaContent . "]}";
            $modifiedFileContent = str_replace($matches[0], $modifiedSchemaArray, $fileContent);

            $filePath->putContent($modifiedFileContent);
            $filePath->format();
            CubeLog::contentAppended($newColumn, $filePath->fullPath);
        } else {
            $pattern = '/schema\s*=\s*\{\s*\[\s*/';
            if (preg_match($pattern, $fileContent)) {
                $fileContent = preg_replace($pattern, "schema={[$newColumn,", $fileContent, 1);
                $filePath->putContent($fileContent);
                $filePath->format();
                CubeLog::contentAppended($newColumn, $filePath->fullPath);
            } else {
                CubeLog::failedAppending($newColumn,
                    $filePath->fullPath,
                    "Adding new column to the data table inside the file");
            }
        }
    }


    /**
     * @param ReactTsInputComponentString         $inputElement
     * @param InterfacePropertyString             $formInterfaceProperty
     * @param CubePath                            $filePath
     * @param array{key:string,value:string}|null $defaultValue
     * @return bool
     */
    public static function addNewInputToReactTSForm(ReactTsInputComponentString $inputElement, InterfacePropertyString $formInterfaceProperty, CubePath $filePath, ?array $defaultValue = null): bool
    {
        $operationContext = "Trying To Add New ApiSelect Component To The Form";
        if (!$filePath->exist()) {
            CubeLog::notFound($filePath->fullPath, $operationContext);
            return false;
        }

        $fileContent = $filePath->getContent();

        if (FileUtils::contentExistInFile($filePath, $inputElement)) {
            CubeLog::contentAlreadyExists($inputElement, $filePath->fullPath, $operationContext);
            return false;
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
            CubeLog::failedAppending(
                $inputElement,
                $filePath->fullPath,
                $operationContext
            );
            return false;
        }

        $formContent .= "\n$inputElement\n";
        $fileContent = str_replace($substitute, $formContent, $fileContent);

        // adding new interface property
        $formInterfacePattern = '#useForm\s*<\s*\{\s*(.*?)\s*}\s*>\s*\(\s*\{(.*?)}\s*\)#s';
        if (preg_match($formInterfacePattern, $fileContent, $matches)) {
            $interfaceProperties = $matches[1];
            if (!FileUtils::contentExistsInString($interfaceProperties, $formInterfaceProperty)) {
                $interfaceProperties .= "\n$formInterfaceProperty\n";
                $fileContent = str_replace($matches[1], $interfaceProperties, $fileContent);
            } else {
                CubeLog::contentAlreadyExists($interfaceProperties, $filePath->fullPath, $operationContext);
            }

            if ($defaultValue
                && !empty($matches[2])
                && !FileUtils::contentExistsInString($matches[2], "{$defaultValue['key']}:{$defaultValue['value']}")
            ) {
                $newDefaultValues = "{$matches[2]},\n{$defaultValue['key']}:{$defaultValue['value']},";
                $newDefaultValues = FileUtils::fixArrayOrObjectCommas($newDefaultValues);
                $fileContent = str_replace($matches[2], $newDefaultValues, $fileContent);
            }
        } else {
            CubeLog::failedAppending(
                $formInterfaceProperty,
                $filePath->fullPath,
                $operationContext
            );
        }

        $filePath->putContent($fileContent);
        $filePath->format();
        CubeLog::contentAppended($inputElement, $filePath->fullPath);
        return true;
    }

    public static function addComponentToShowPage(ReactTsDisplayComponentString $component, CubePath $pagePath): bool
    {
        if (!$pagePath->exist()) {
            return false;
        }

        if (FileUtils::contentExistInFile($pagePath, $component)) {
            return false;
        }

        $content = $pagePath->getContent();

        $pattern = '#<PageCard(.*?)>\s*<div(.*?)>(.*?)</div>(.*?)</PageCard>#s';

        if (!preg_match($pattern, $content, $matches)) {
            return false;
        }

        if (!isset($matches[3])) {
            return false;
        }

        $content = str_replace($matches[3], "$component\n{$matches[3]}", $content);
        $pagePath->putContent($content);
        $pagePath->format();
        CubeLog::contentAppended($component, $pagePath->fullPath);
        return true;
    }
}