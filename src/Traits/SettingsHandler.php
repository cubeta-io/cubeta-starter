<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\Enums\ColumnType;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Support\Str;

trait SettingsHandler
{
    /**
     * add new table to settings json and if it exists update it
     * @param string $modelName
     * @param array $attributes
     * @param array $nullables
     * @param array $uniques
     * @param array $relations
     * @return void
     */
    public function handleTableSettings(string $modelName, array $attributes, array $nullables, array $uniques, array $relations = []): void
    {
        $settings = getJsonSettings();
        $this->checkForKey($settings, "tables");
        $tables = $settings["tables"];

        if ($this->checkIfTableExists($tables, $modelName)) {
            $tables = $this->handleAddToExisted($attributes, $nullables, $uniques, $modelName, $relations, $tables);
        } else {
            $tables = $this->handleAddNewOne($attributes, $nullables, $uniques, $modelName, $relations, $tables);
        }

        $settings['tables'] = $tables;
        storeJsonSettings($settings);
    }

    /**
     * make sure that this array key exists
     * @param array $settings
     * @param string $key
     * @return void
     */
    protected function checkForKey(array &$settings, string $key): void
    {
        if (isset($settings[$key])) {
            return;
        } else {
            $settings[$key] = [];
        }
    }

    /**
     * check if table exist in the settings json
     * @param array $tables
     * @param string $name
     * @return bool
     */
    protected function checkIfTableExists(array $tables, string $name): bool
    {
        $result = $this->searchForTable($tables, $name);

        if ($result) {
            return true;
        } else return false;
    }

    /**
     * search for table in settings json
     * @param array $tables
     * @param string $name
     * @return array|null
     */
    protected function searchForTable(array $tables, string $name): ?array
    {
        $tableName = tableNaming($name);
        $modelName = modelNaming($name);

        foreach ($tables as $table) {
            if (isset($table["model_name"])) {
                if ($table["model_name"] == $modelName) {
                    return $table;
                }
            }

            if (isset($table['table_name'])) {
                if ($table['table_name'] == $tableName) {
                    return $table;
                }
            }
        }

        return null;
    }

    /**
     * handle updating an existed table in settings json
     * @param array $attributes
     * @param array $nullables
     * @param array $uniques
     * @param string $modelName
     * @param array $related
     * @param array $tables
     * @return array
     */
    public function handleAddToExisted(array $attributes, array $nullables, array $uniques, string $modelName, array $related, array $tables): array
    {
        $table = $this->searchForTable($tables, $modelName);

        list($columns, $relations) = $this->extractColumnsAndRelations($attributes, $nullables, $uniques, $related);

        $newTable = [
            "model_name" => modelNaming($modelName),
            "table_name" => tableNaming($modelName),
            "attributes" => array_merge($columns, $table["attributes"]),
            "relations" => array_merge($relations, $table["relations"])
        ];

        $this->replaceTable($tables, $modelName, $newTable);
        return $tables;
    }

    /**
     * @param array $attributes
     * @param array $nullables
     * @param array $uniques
     * @param array $related
     * @return array
     */
    protected function extractColumnsAndRelations(array $attributes, array $nullables, array $uniques, array $related = []): array
    {
        $columns = [];
        $relations = [];

        foreach ($attributes as $colName => $type) {

            if ($type == ColumnType::KEY) {
                $type = ColumnType::FOREIGN_KEY;
                $parent = modelNaming(Str::singular(str_replace('_id', '', $colName)));
                $relations[RelationsTypeEnum::BelongsTo][] = [
                    "key" => $colName,
                    "model_name" => $parent
                ];
            }

            $columns[] = [
                "name" => $colName,
                "type" => $type,
                "nullable" => in_array($colName, $nullables),
                "unique" => in_array($colName, $uniques)
            ];
        }

        foreach ($related as $relation => $type) {
            if ($type == RelationsTypeEnum::ManyToMany) {
                $relations[RelationsTypeEnum::ManyToMany][] = [
                    "model_name" => modelNaming($relation)
                ];
            } else if ($type == RelationsTypeEnum::HasMany) {
                $relations[RelationsTypeEnum::HasMany][] = [
                    "model_name" => modelNaming($relation)
                ];
            }
        }

        return array($columns, $relations);
    }

    /**
     * replace a table data with a new one in settings json
     * @param array $tables
     * @param string $name
     * @param array $newTable
     * @return void
     */
    protected function replaceTable(array &$tables, string $name, array $newTable): void
    {
        $tableName = tableNaming($name);
        $modelName = modelNaming($name);

        foreach ($tables as &$table) {
            if (isset($table["model_name"])) {
                if ($table["model_name"] == $modelName) {
                    $table = $newTable;
                }
            }

            if (isset($table['table_name'])) {
                if ($table['table_name'] == $tableName) {
                    $table = $newTable;
                }
            }
        }
    }

    /**
     * handle the addition on a new table in settings json
     * @param array $attributes
     * @param array $nullables
     * @param array $uniques
     * @param string $modelName
     * @param array $related
     * @param mixed $tables
     * @return array
     */
    public function handleAddNewOne(array $attributes, array $nullables, array $uniques, string $modelName, array $related, array $tables): array
    {
        list($columns, $relations) = $this->extractColumnsAndRelations($attributes, $nullables, $uniques, $related);

        $tables[] = [
            "model_name" => modelNaming($modelName),
            "table_name" => tableNaming($modelName),
            "attributes" => $columns,
            "relations" => $relations
        ];
        return $tables;
    }

    public function getAllModelsName(): array
    {
        $tables = getJsonSettings();
        $all = [];
        foreach ($tables as $table) {
            if (isset($table['model_name'])) {
                $all[] = $table['model_name'];
            } elseif (isset($table['table_name'])) {
                $all[] = modelNaming($table['table_name']);
            }
        }
        return $all;
    }
}
