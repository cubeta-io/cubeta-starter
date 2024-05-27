<?php

namespace Cubeta\CubetaStarter\App\Models\Settings;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Helpers\Naming;

class Settings
{
    private static $instance;
    private static array $json;
    private static array $tables;
    private static ?FrontendTypeEnum $frontendStack;

    private function __construct()
    {
        //
    }

    public static function make(): Settings
    {
        self::$json = self::getJsonSettings();

        if (isset(self::$json['frontend_type'])) {
            self::$frontendStack = FrontendTypeEnum::tryFrom(self::$json['frontend_type']);
        } else {
            self::$frontendStack = FrontendTypeEnum::NONE;
        }

        if (isset(self::$json["tables"])) {
            self::$tables = self::$json["tables"];
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
    public static function getJsonSettings(): array
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
        }
        return $data;
    }

    public function getAllModels(): array
    {
        return array_map(fn($table) => $table['model_name'], self::$tables);
    }

    public function getTable(string $modelName): ?CubeTable
    {
        $modelName = Naming::model($modelName);
        $tableName = Naming::table($modelName);

        foreach (self::$tables as $table) {
            if ($table["model_name"] == $modelName || $table['model_name'] == $tableName) {
                $attributes = [];

                foreach ($table['attributes'] as $attribute) {
                    $attributes[] = new CubeAttribute($attribute['name'], $attribute['type'], $attribute['nullable'], $attribute['unique'], $tableName);
                }

                $relations = [];

                foreach ($table['relations'] as $type => $relationships) {
                    foreach ($relationships as $relationship) {
                        $relations[] = new CubeRelation($type, $relationship['model_name'], $table['model_name']);
                    }
                }

                return new CubeTable(
                    $table['model_name'],
                    $table['table_name'],
                    $attributes,
                    $relations
                );
            }
        }

        return null;
    }

    public function serialize(string $modelName, array $attributes, array $relations = [], array $nullables = [], array $uniques = []): CubeTable
    {
        $columns = [];
        $relationships = [];

        foreach ($attributes as $colName => $type) {

            if ($type == ColumnTypeEnum::KEY->value) {
                $type = ColumnTypeEnum::KEY->value;
                $parent = Naming::model(str_replace('_id', '', $colName));
                $relationships[] = new CubeRelation(RelationsTypeEnum::BelongsTo->value, $parent, $modelName);
            }

            $columns[] = new CubeAttribute($colName, $type, in_array($colName, $nullables), in_array($colName, $uniques), Naming::table($modelName));
        }

        foreach ($relations as $relation => $type) {
            if (str_contains($relation, '_id')) {
                continue;
            }

            $relationships[] = new CubeRelation($type, $relation, $modelName);
        }

        $table = new CubeTable(
            $modelName,
            $modelName,
            $columns,
            $relationships
        );

        $this->addTable($table);

        return $table;
    }

    public function addTable(CubeTable $table): static
    {
        $exist = [];
        foreach (self::$tables as $key => $t) {
            if (isset($t["table_name"]) and $t["table_name"] == $table->tableName) {
                $exist = self::$tables[$key];
                unset(self::$tables[$key]);
            }
        }

        $new = $table->collect()->merge(collect($exist))->unique();

        self::$tables[] = $new->toArray();
        $json = self::getJsonSettings();
        $json['tables'] = collect(self::$tables)->values()->toArray();
        self::storeJsonSettings($json);
        return $this;
    }

    /**
     * store the provided array in the package (cubeta-starter) json file settings as an array
     * @param array $data
     * @return void
     */
    public static function storeJsonSettings(array $data): void
    {
        file_put_contents(
            base_path('/cubeta-starter.config.json'),
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }

    /**
     * @param FrontendTypeEnum $type
     * @return void
     */
    public function setFrontendType(FrontendTypeEnum $type): void
    {
        self::$json["frontend_type"] = $type->value;
        self::storeJsonSettings(self::$json);
    }

    /**
     * @return FrontendTypeEnum|null
     */
    public function getFrontendType(): ?FrontendTypeEnum
    {
        return self::$frontendStack;
    }
}
