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
    private static ?FrontendTypeEnum $frontendStack = null;
    private static bool $hasRoles = false;
    private static string $version;
    private static bool $installedApi = false;
    private static bool $installedWeb = false;
    private static bool $installedApiAuth = false;
    private static bool $installedWebAuth = false;
    private static bool $installedWebPackages = false;

    private function __construct()
    {
        //
    }

    public static function make(): Settings
    {
        self::$json = self::getJsonSettings();

        if (isset(self::$json['frontend_type'])) {
            self::$frontendStack = FrontendTypeEnum::tryFrom(self::$json['frontend_type']);
        }

        if (isset(self::$json['has_roles'])) {
            self::$hasRoles = (bool)self::$json['has_roles'];
        } else {
            self::$hasRoles = false;
        }

        if (isset(self::$json['installed_api'])) {
            self::$installedApi = (bool)self::$json['installed_api'];
        } else {
            self::$installedApi = false;
        }

        if (isset(self::$json['installed_web'])) {
            self::$installedWeb = (bool)self::$json['installed_web'];
        } else {
            self::$installedWeb = false;
        }

        if (isset(self::$json['installed_web_packages'])) {
            self::$installedWebPackages = (bool)self::$json['installed_web_packages'];
        } else {
            self::$installedWebPackages = false;
        }

        if (isset(self::$json['installed_api_auth'])) {
            self::$installedApiAuth = (bool)self::$json['installed_api_auth'];
        } else {
            self::$installedApiAuth = false;
        }

        if (isset(self::$json['installed_web_auth'])) {
            self::$installedWebAuth = (bool)self::$json['installed_web_auth'];
        } else {
            self::$installedWebAuth = false;
        }

        if (isset(self::$json["tables"])) {
            self::$tables = self::$json["tables"];
            self::$version = self::$tables["version"] ?? "v1";
        } else {
            self::$tables = [];
            self::$version = 'v1';
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
                    $attributes[] = CubeAttribute::factory($attribute['name'], $attribute['type'], $attribute['nullable'], $attribute['unique'], $tableName);
                }

                $relations = [];

                foreach ($table['relations'] as $type => $relationships) {
                    foreach ($relationships as $relationship) {
                        $relations[] = CubeRelation::factory($type, $relationship['model_name'], $table['model_name'], self::$version);
                    }
                }

                return new CubeTable(
                    $table['model_name'],
                    $table['table_name'],
                    $attributes,
                    $relations,
                    self::$version
                );
            }
        }

        return null;
    }

    public function serialize(string $modelName, array $attributes, array $relations = [], array $nullables = [], array $uniques = [], string $version = 'v1'): CubeTable
    {
        $columns = [];
        $relationships = [];

        foreach ($attributes as $colName => $type) {

            if ($type == ColumnTypeEnum::KEY->value) {
                $type = ColumnTypeEnum::KEY->value;
                $parent = Naming::model(str_replace('_id', '', $colName));
                $relationships[] = CubeRelation::factory(RelationsTypeEnum::BelongsTo->value, $parent, $modelName, self::$version);
            }

            $columns[] = CubeAttribute::factory($colName, $type, in_array($colName, $nullables), in_array($colName, $uniques), Naming::table($modelName));
        }

        foreach ($relations as $relation => $type) {
            if (str_contains($relation, '_id')) {
                continue;
            }

            $relationships[] = CubeRelation::factory($type, $relation, $modelName, self::$version);
        }

        $table = new CubeTable(
            $modelName,
            $modelName,
            $columns,
            $relationships,
            $version
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

    /**
     * @return bool
     */
    public function installedRoles(): bool
    {
        return self::$hasRoles;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setInstalledRoles(bool $value = true): void
    {
        self::$json["has_roles"] = $value;
        self::storeJsonSettings(self::$json);
    }

    /**
     * @return bool
     */
    public function installedApi(): bool
    {
        return self::$installedApi;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setInstalledApi(bool $value = true): void
    {
        self::$json["installed_api"] = $value;
        self::storeJsonSettings(self::$json);
    }

    /**
     * @return bool
     */
    public function installedWeb(): bool
    {
        return self::$installedWeb;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setInstalledWeb(bool $value = true): void
    {
        self::$json["installed_web"] = $value;
        self::storeJsonSettings(self::$json);
    }

    /**
     * @return bool
     */
    public function installedApiAuth(): bool
    {
        return self::$installedApiAuth;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setInstalledApiAuth(bool $value = true): void
    {
        self::$json["installed_api_auth"] = $value;
        self::storeJsonSettings(self::$json);
    }

    public function setInstalledWebAuth(bool $value = true): void
    {
        self::$json["installed_web_auth"] = $value;
        self::storeJsonSettings(self::$json);
    }

    public function installedWebAuth(): bool
    {
        return self::$installedWebAuth;
    }

    public function installedWebPackages(): bool
    {
        return self::$installedWebPackages;
    }

    public function setInstalledWebPackages(bool $value = true): void
    {
        self::$json["installed_web_packages"] = $value;
        self::storeJsonSettings(self::$json);
    }
}
