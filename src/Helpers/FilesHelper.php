<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * add the use statement to the top of the desired file
 * @param string $importStatement
 * @param string $filePath
 * @return void
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

/**
 * this function check for a php file syntax error by running php -l command on the file
 * @param string $path
 * @return bool
 */
function checkForSyntaxErrors(string $path): bool
{
    // PHP interpreter with the '-l' flag to check for syntax errors
    $output = shell_exec("php -l {$path}");

    return str_contains($output, 'No syntax errors detected');
}

/**
 * get the package (cubeta-starter)  json file settings as an array
 * @return array
 */
function getJsonSettings(): array
{
    $filePath = base_path('/settings.json');

    if (!file_exists($filePath)) {
        return [];
    }

    $data = json_decode(
        file_get_contents(
            $filePath
        ),
        true
    );

    if (!$data) {
        return [];
    } else return $data;
}

/**
 * store the provided array in the package (cubeta-starter) json file settings as an array
 * @param array $data
 * @return void
 */
function storeJsonSettings(array $data): void
{
    file_put_contents(
        base_path('/settings.json'),
        json_encode($data, JSON_PRETTY_PRINT)
    );
}

/**
 * check if content exist in a file
 * @param $filePath
 * @param $content
 * @return bool
 */
function checkIfContentExistInFile($filePath, $content): bool
{
    $fileContent = file_get_contents($filePath);

    if (!$fileContent) {
        return false;
    }

    $fileContent = preg_replace('/\s+/', '', $fileContent);

    $content = preg_replace('/\s+/', '', $content);

    if (str_contains(strtolower($fileContent), strtolower($content))) {
        return true;
    }

    return false;
}

/**
 * @param string $pattern
 * @param string $replacement
 * @param string $subject
 * @return string
 */
function prependLastMatch(string $pattern, string $replacement, string $subject): string
{
    preg_match_all($pattern, $subject, $matches, PREG_OFFSET_CAPTURE);

    // Get the offset of the last match
    $lastMatchOffset = end($matches[0])[1];

    // Replace the last match with the new content
    return substr_replace($subject, $replacement, $lastMatchOffset, 0);
}
