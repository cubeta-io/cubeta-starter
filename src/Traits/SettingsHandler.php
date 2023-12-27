<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\Enums\ColumnType;
use Cubeta\CubetaStarter\Enums\SettingsEnum;
use Illuminate\Support\Str;

trait SettingsHandler
{
    public function addMigration($modelName, $attributes, $nullables, $uniques): void
    {
        $settings = getJsonSettings();
        $this->checkForKey($settings, "migrations");
        $migrations = $settings["migrations"];

        $columns = [];
        $relations = [];

        foreach ($attributes as $colName => $type) {

            if ($type == ColumnType::KEY) {
                $type = ColumnType::FOREIGN_KEY;
                $parent = modelNaming(Str::singular(str_replace('_id', '', $colName)));
                $relations["belongs_to"][] = [
                    "key" => $colName,
                    "parent" => $parent
                ];
            }

            $columns[] = [
                "name" => $colName,
                "type" => $type,
                "nullable" => in_array($colName, $nullables),
                "unique" => in_array($colName, $uniques)
            ];
        }

        $migrations[] = [
            "name" => modelNaming($modelName),
            "attributes" => $columns,
            "relations" => $relations
        ];

        $settings['migrations'] = $migrations;

        storeJsonSettings($settings);
    }

    protected function checkForKey(array &$settings, string $key): void
    {
        if (isset($settings[$key])) {
            return;
        } else {
            $settings[$key] = [];
        }
    }
}
