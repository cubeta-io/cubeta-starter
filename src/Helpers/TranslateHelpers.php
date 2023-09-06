<?php

/**
 * it returns the corresponding value for the provided locale if it is not provided so for the current locale
 * and if there isn't a value corresponding to them, it returns the corresponding value for the default locale
 * defined in the config file of the package and if there isn't a corresponding value for it,
 * it returns a message informing you that there isn't
 *
 * @param  string     $translationColumn must be a json string
 * @param  ?string    $locale
 * @return mixed|null
 */
function getTranslation(string $translationColumn, string $locale = null): mixed
{
    $locale ??= app()->getLocale();
    $translationArray = json_decode($translationColumn, true);

    if ($locale) {
        return $translationArray[$locale] ?? null;
    }

    return $translationArray[config('cubeta-starter.defaultLocale')] ?? 'there is no value correspond to the current locale or the default locale';
}

function addAutoLoadsToComposerJson(): void
{
    $composerPath = base_path('composer.json');

    if (file_exists($composerPath)) {
        // Read the contents of composer.json and parse it as JSON
        $composerJsonContents = file_get_contents($composerPath);
        $composerJsonData = json_decode($composerJsonContents, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Check if the 'autoload' section exists, and if not, create it
            if (!isset($composerJsonData['autoload'])) {
                $composerJsonData['autoload'] = [];
            }

            // Check if the 'files' autoload section exists, and if not, create it
            if (!isset($composerJsonData['autoload']['files'])) {
                $composerJsonData['autoload']['files'] = [];
            }

            // Add your files to the 'files' autoload section
            $composerJsonData['autoload']['files'][] = "app/Helpers/NamingHelpers.php";
            $composerJsonData['autoload']['files'][] = "app/Helpers/TranslateHelpers.php";

            // Encode the modified data as JSON
            $updatedComposerJson = json_encode($composerJsonData, JSON_PRETTY_PRINT);

            // Write the updated JSON back to composer.json
            if (file_put_contents($composerPath, $updatedComposerJson) !== false) {
                echo "Autoload files added to composer.json successfully.";
            } else {
                echo "Failed to write to composer.json.";
            }
        } else {
            echo "Error decoding composer.json as JSON.";
        }
    } else {
        echo "composer.json does not exist.";
    }
}

