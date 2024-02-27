<?php

namespace Cubeta\CubetaStarter\App\Models\Table;

use Cubeta\CubetaStarter\Enums\ColumnType;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Support\Str;

class Settings
{
    private static $instance;
    private static array $tables;

    private function __construct()
    {
        //
    }

    public static function make(): Settings
    {
        self::$tables = self::getJsonSettings();
        if (isset(self::$tables["tables"])) {
            self::$tables = self::$tables["tables"];
        } else {
            self::$tables = [];
        }

        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * get the package (cubeta-starter)  json file settings as an array
     * @return array
     */
    private static function getJsonSettings(): array
    {
        $filePath = base_path('/cubeta-starter.config.json');

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
        }  return $data;
    }

    public function getTable(string $modelName): ?CubetaTable
    {
        $modelName = modelNaming($modelName);
        foreach (self::$tables as $table) {
            if ($table["model_name"] == $modelName || $table['model_name'] == tableNaming($modelName)) {
                $attributes = [];

                foreach ($table['attributes'] as $attribute) {
                    $attributes[] = new CubetaAttribute($attribute['name'], $attribute['type'], $attribute['nullable'], $attribute['unique']);
                }

                $relations = [];

                foreach ($table['relations'] as $type => $relationships) {
                    foreach ($relationships as $relationship) {
                        $relations[] = new CubetaRelation($type, $relationship['model_name'], $relationship['key'] ?? null);
                    }
                }

                return new CubetaTable(
                    $table['model_name'],
                    $table['table_name'],
                    $attributes,
                    $relations
                );
            }
        }

        return null;
    }

    public function serialize(string $modelName, array $attributes, array $relations = [], array $nullables = [], array $uniques = []): CubetaTable
    {
        $columns = [];
        $relationships = [];

        foreach ($attributes as $colName => $type) {

            if ($type == ColumnType::KEY) {
                $type = ColumnType::FOREIGN_KEY;
                $parent = modelNaming(Str::singular(str_replace('_id', '', $colName)));
                $relationships[] = new CubetaRelation(RelationsTypeEnum::BelongsTo, $parent, $colName);
            }

            $columns[] = new CubetaAttribute($colName, $type, in_array($colName, $nullables), in_array($colName, $uniques));
        }

        foreach ($relations as $relation => $type) {
            if (str_contains($relation, '_id')) {
                continue;
            }

            $relationships[] = new CubetaRelation($type, modelNaming($relation));
        }

        $table = new CubetaTable(
            modelNaming($modelName),
            tableNaming($modelName),
            $columns,
            $relationships
        );

        $this->addTable($table);

        return $table;
    }

    public function addTable(CubetaTable $table): static
    {
        foreach (self::$tables as $key => $t) {
            if ($t["table_name"] == $table->tableName) {
                unset(self::$tables[$key]);
            }
        }

        self::$tables[] = $table->toArray();
        self::storeJsonSettings(["tables" => array_values(self::$tables)]);
        return $this;
    }

    public function toArray(): array
    {
        return self::$tables;
    }

    /**
     * store the provided array in the package (cubeta-starter) json file settings as an array
     * @param  array $data
     * @return void
     */
    private static function storeJsonSettings(array $data): void
    {
        file_put_contents(
            base_path('/cubeta-starter.config.json'),
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }
}
