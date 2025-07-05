<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\DisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Javascript\DataTableColumnString;

class BladeFileUtils
{
    public static function addToNewInputToForm(InputComponentString $inputComponentString, CubePath $pagePath): bool
    {
        if (!$pagePath->exist()) {
            return false;
        }

        $content = $pagePath->getContent();

        $pattern = '#<x-form(.*?)>(.*?)<div(.*?)class\s*=\s*([\'"])row([\'"])(.*?)>(.*?)</x-form>#s';

        if (!preg_match($pattern, $content, $matches)) {
            CubeLog::failedAppending($inputComponentString, $pagePath);
            return false;
        }

        if (!isset($matches[0])) {
            CubeLog::failedAppending($inputComponentString, $pagePath);
            return false;
        }

        if (FileUtils::contentExistsInString($matches[7], $inputComponentString)) {
            CubeLog::contentAlreadyExists($inputComponentString, $pagePath, "Adding new input to [$pagePath->fullPath]");
            return false;
        }

        $newContent = "$inputComponentString\n$matches[7]";
        $content = str_replace($matches[7], $newContent, $content);
        $pagePath->putContent($content);
        $pagePath->format();
        CubeLog::contentAppended($inputComponentString, $pagePath);
        return true;
    }

    /**
     * @param CubePath              $filePath
     * @param DataTableColumnString $columnObject
     * @param HtmlTableHeaderString $columnHeader
     * @return bool
     */
    public static function addColumnToDataTable(CubePath $filePath, DataTableColumnString $columnObject, HtmlTableHeaderString $columnHeader): bool
    {
        if (!$filePath->exist()) {
            CubeLog::notFound($filePath->fullPath, "Trying To Add $columnObject->name column object To The Datatable Columns in : [$filePath->fullPath]");
            return false;
        }

        if (FileUtils::contentExistInFile($filePath, $columnObject)) {
            CubeLog::contentAlreadyExists($columnObject, $filePath->fullPath, "Trying To Add $columnObject->name column object To The Datatable Columns in : [$filePath->fullPath]");
            return false;
        }

        $fileContent = $filePath->getContent();

        // adding html column

        if (!FileUtils::contentExistInFile($filePath, $columnHeader)) {
            $pattern = '#<table(.*?)>(.*?)<thead(.*?)>(.*?)<tr(.*?)>(.*?)</tr>(.*?)</thead>(.*?)</table>#s';
            if (!preg_match($pattern, $fileContent, $htmlMatches)) {
                CubeLog::warning(
                    "We Couldn't find the Proper Place To Add New Column In The HTML Of [$filePath->fullPath]",
                    "Trying To Add $columnObject->name column object To The Datatable Columns in : [$filePath->fullPath]"
                );
                return false;
            }
            if (empty($htmlMatches[6])) {
                CubeLog::warning(
                    "We Couldn't find the Proper Place To Add New Column In The HTML Of [$filePath->fullPath]",
                    "Trying To Add $columnObject->name column object To The Datatable Columns in : [$filePath->fullPath]"
                );
                return false;
            }
            if (preg_match('#<th(.*?)>\s*Action\s*</th>#', $htmlMatches[6])) {
                $newHeaders = preg_replace('#<th(.*?)>\s*Action\s*</th>#', "$columnHeader\n" . '${0}', $htmlMatches[6]);
            } else {
                $newHeaders = $htmlMatches[6] . "\n" . $columnHeader;
            }
            $fileContent = str_replace($htmlMatches[6], $newHeaders, $fileContent);
        }

        // Find the column array
        $pattern = '/DataTable\s*\(\s*\{(.*?)columns\s*:\s*\[(.*?)](.*)}\)/s';

        if (!preg_match($pattern, $fileContent, $objectMatches)) {
            CubeLog::warning(
                "We Couldn't find the Proper Place To Add New Column In The HTML Of [$filePath->fullPath]",
                "Trying To Add $columnObject->name column object To The Datatable Columns in : [$filePath->fullPath]"
            );
            return false;
        }

        if (empty($objectMatches[2])) {
            CubeLog::warning(
                "We Couldn't find the Proper Place To Add New Column In The HTML Of [$filePath->fullPath]",
                "Trying To Add $columnObject->name column object To The Datatable Columns in : [$filePath->fullPath]"
            );
            return false;
        }

        if (preg_match('/\{\s*data\s*:\s*([\'"])\s*action\s*([\'"])(.*?)}/s', $objectMatches[2])) {
            $newColumns = preg_replace(
                '/\{\s*data\s*:\s*([\'"])\s*action\s*([\'"])(.*?)}/s',
                $columnObject . "\n" . '${0}',
                $objectMatches[2]
            );
        } else {
            $newColumns = $objectMatches[2] . ",\n$columnObject";
            $newColumns = FileUtils::fixArrayOrObjectCommas($newColumns);
        }
        $fileContent = str_replace($objectMatches[2], $newColumns, $fileContent);

        $filePath->putContent($fileContent);
        $filePath->format();
        CubeLog::contentAppended($columnObject, $filePath);
        return true;
    }

    public static function addNewDisplayComponentToShowView(DisplayComponentString $string, CubePath $showPagePath): bool
    {
        if (!$showPagePath->exist()) {
            return false;
        }

        if (FileUtils::contentExistInFile($showPagePath, $string)) {
            return false;
        }

        $content = $showPagePath->getContent();

        $pattern = '#<x-show-layout(.*?)>(.*?)<div(.*?)class\s*=\s*(\'|\")(.*?)row(.*?)(\'|\")(.*?)>(.*?)</div>\s*</x-show-layout>#s';
        if (!preg_match($pattern, $content, $matches)) {
            return false;
        }

        if (empty($matches[9])) {
            return false;
        }

        $content = str_replace($matches[9], $matches[9] . "\n$string", $content);
        $showPagePath->putContent($content);
        $showPagePath->format();
        return true;
    }
}