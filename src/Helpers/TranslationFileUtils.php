<?php

namespace Cubeta\CubetaStarter\Helpers;

use Illuminate\Support\Arr;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Settings\CubeRelation;

class TranslationFileUtils
{
    private static function mergeMissing(array $existing, array $defaults): array
    {
        foreach (Arr::dot($defaults) as $key => $value) {
            if (! Arr::has($existing, $key)) {
                Arr::set($existing, $key, $value);
            }
        }

        return $existing;
    }

    public static function addModelTranslations(CubeTable $table, bool $withReactJson = false): void
    {
        $modelKey = $table->modelNaming();
        $fields = $table->attributes()
            ->mapWithKeys(fn ($attribute) => [$attribute->name => $attribute->labelNaming()])
            ->all();

        $table->relations()
            ->filter(fn (CubeRelation $relation) => $relation->isBelongsTo())
            ->each(function (CubeRelation $relation) use (&$fields): void {
                $fields[$relation->key ?? $relation->method()] = $relation->titleNaming();
            });

        $defaults = [
            'models' => [
                $modelKey => [
                    'singular' => $table->titleNaming(),
                    'plural'   => str($table->tableNaming())->headline()->toString(),
                    'fields'   => $fields,
                ],
            ],
        ];

        foreach (config('cubeta-starter.available_locales', ['en']) as $locale) {
            self::merge(CubePath::make("lang/{$locale}/site.php"), $defaults);

            if ($withReactJson) {
                self::mergeJson(CubePath::make("resources/js/locales/{$locale}.json"), $defaults);
            }
        }
    }

    public static function merge(CubePath $path, array $defaults): void
    {
        $existing = [];

        if ($path->exist()) {
            $loaded = require $path->fullPath;
            $existing = is_array($loaded) ? $loaded : [];
        }

        $translations = self::mergeMissing($existing, $defaults);

        if ($translations === $existing) {
            return;
        }

        $path->ensureDirectoryExists();
        $path->putContent("<?php\n\nreturn ".var_export($translations, true).";\n");
        $path->format();
    }

    public static function mergeJson(CubePath $path, array $defaults): void
    {
        $existing = [];

        if ($path->exist()) {
            $loaded = json_decode($path->getContent(), true, flags: JSON_THROW_ON_ERROR);
            $existing = is_array($loaded) ? $loaded : [];
        }

        $translations = self::mergeMissing($existing, $defaults);

        if ($translations === $existing) {
            return;
        }

        $path->ensureDirectoryExists();
        $path->putContent(json_encode(
            $translations,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        )."\n");
        $path->format();
    }
}
