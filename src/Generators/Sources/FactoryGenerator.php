<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Builders\FactoryStubBuilder;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Illuminate\Support\Str;

class FactoryGenerator extends AbstractGenerator
{
    use StringsGenerator;

    public static string $key = 'factory';
    private FactoryStubBuilder $builder;

    public function __construct(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], ?string $actor = null, string $generatedFor = '', ?string $version = null, bool $override = false)
    {
        parent::__construct(
            $fileName,
            $attributes,
            $relations,
            $nullables,
            $uniques,
            $actor,
            $generatedFor,
            $version,
            $override
        );

        $this->builder = FactoryStubBuilder::make();
    }

    public function run(bool $override = false): void
    {
        $factoryPath = $this->table->getFactoryPath();

        if ($factoryPath->exist()) {
            $factoryPath->logAlreadyExist("Generating Factory For ({$this->table->modelName}) Model");
            return;
        }

        $factoryPath->ensureDirectoryExists();

        $this->generateFields();
        $this->builder
            ->namespace(config('cubeta-starter.factory_namespace'))
            ->modelNamespace($this->table->getModelNameSpace())
            ->modelName($this->table->modelName)
            ->generate($factoryPath, $this->override);


        $factoryPath->format();

        CodeSniffer::make()->setModel($this->table)->checkForFactoryRelations();
    }

    private function generateFields(): void
    {
        $this->table->attributes()->each(function (CubeAttribute $attribute) {
            $isUnique = $attribute->unique ? "->unique()" : "";
            $name = $attribute->name;
            $type = $attribute->type;

            if ($attribute->isKey()) {
                $relatedModel = CubeTable::create(str_replace('_id', '', $name));
                $this->builder->row($name, $relatedModel->getModelClassString() . "::factory()");
            } elseif ($attribute->isTranslatable()) {
                $this->builder->import("\\App\\Serializers\\Translatable");
                $this->builder->row($name, "Translatable::fake()");
            } elseif ($type == ColumnTypeEnum::STRING->value) {
                $this->builder->row(...$this->handleStringType($name, $isUnique));
            } elseif ((in_array($name, ['cost', 'price', 'value', 'expense']) || Str::contains($name, ['price', 'cost'])) && ColumnTypeEnum::isNumericType($type)) {
                $this->builder->row($name, "fake(){$isUnique}->randomFloat(2, 0, 1000)");
            } elseif (Str::contains($name, 'description') && $type == ColumnTypeEnum::TEXT->value) {
                $this->builder->row($name, "fake(){$isUnique}->text()");
            } elseif ($attribute->isFile()) {
                $this->builder->import("\\Illuminate\\Http\\UploadedFile");
                $this->builder->row($name, "UploadedFile::fake()->image(\"image.png\")");
            } elseif (Str::contains($name, 'age') && $type == ColumnTypeEnum::INTEGER->value) {
                $this->builder->row($name, "fake(){$isUnique}->numberBetween(15,60)");
            } elseif (Str::contains($name, 'time') && $type == ColumnTypeEnum::TIME->value) {
                $this->builder->row($name, "fake(){$isUnique}->time('H:i')");
            } elseif ((Str::endsWith($name, '_at') || Str::contains($name, 'date')) && $attribute->isDate()) {
                $this->builder->row($name, "fake(){$isUnique}->date()");
            } elseif (Str::startsWith($name, 'is_') && $type == ColumnTypeEnum::BOOLEAN->value) {
                $this->builder->row($name, "fake(){$isUnique}->boolean()");
            } elseif ($type == ColumnTypeEnum::JSON->value) {
                $this->builder->row($name, "json_encode([fake()->word() => fake()->word()])");
            } elseif ($fakerMethod = $this->getFakeMethod($type)) {
                $this->builder->row($name, "fake(){$isUnique}{$fakerMethod}");
            } else {
                $this->builder->row($name, "fake(){$isUnique}->{$type}()");
            }
        });

        $this->table->relations()->each(function (CubeRelation $rel) {
            if ($rel->isHasMany() || $rel->isManyToMany()) {
                if ($rel->getModelPath()->exist()) {
                    $this->builder->method($this->factoryRelationMethod($rel));
                }
            }
        });
    }

    private function handleStringType(string $name, string $isUnique): array
    {
        $originalName = $name;
        if (Str::contains($name, 'phone')) {
            $name = 'phone';
        } elseif (Str::contains($name, ['latitude ', '_lat', 'lat_']) || $name == 'lat' || $name == 'latitude') {
            $name = 'lat';
        } elseif (Str::contains($name, ['longitude', '_lon', '_lng', 'lon_', 'lng_']) || $name == 'lng' || $name == 'lon' || $name == 'longitude') {
            $name = 'lng';
        } elseif (Str::contains($name, 'address')) {
            $name = 'address';
        } elseif (Str::contains($name, 'street')) {
            $name = 'street';
        } elseif (Str::contains($name, 'city')) {
            $name = 'city';
        } elseif (Str::contains($name, 'country')) {
            $name = 'country';
        } elseif (Str::contains($name, ['zip', 'post_code', 'postcode', 'PostCode', 'postCode', 'ZIP'])) {
            $name = 'postcode';
        } elseif (Str::contains($name, 'gender')) {
            $name = 'gender';
        }
        $fakerMethod = match ($name) {
            'name', 'username', 'first_name', 'last_name', 'user_name' => "firstName()",
            'email' => "email()",
            'phone' => "phoneNumber()",
            'lat' => "latitude()",
            'lng' => "longitude()",
            'address' => "address()",
            'street' => "streetName()",
            'city' => "city()",
            'country' => "country()",
            'postcode' => "postcode()",
            'gender' => "randomElement(['male' , 'female'])",
            default => "word()"
        };

        return [$originalName, "fake(){$isUnique}->{$fakerMethod}"];
    }

    private function getFakeMethod(string $type): ?string
    {
        return match ($type) {
            ColumnTypeEnum::INTEGER->value,
            ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
            ColumnTypeEnum::BIG_INTEGER->value => '->numberBetween(1,2000)',
            ColumnTypeEnum::DOUBLE->value => '->randomFloat(1,2000)',
            ColumnTypeEnum::FLOAT->value => '->randomFloat(1, 1, 100)',
            ColumnTypeEnum::STRING->value => '->sentence()',
            ColumnTypeEnum::TEXT->value => '->text()',
            ColumnTypeEnum::BOOLEAN->value => '->boolean()',
            ColumnTypeEnum::DATE->value => '->date()',
            ColumnTypeEnum::TIME->value => '->time()',
            ColumnTypeEnum::DATETIME->value,
            ColumnTypeEnum::TIMESTAMP->value => '->dateTime()',
            default => null,
        };
    }

    protected function stubsPath(): string
    {
        return CubePath::stubPath('factory.stub');
    }
}
