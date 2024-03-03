<?php

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
