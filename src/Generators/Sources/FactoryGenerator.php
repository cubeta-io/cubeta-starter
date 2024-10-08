<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FactoryGenerator extends AbstractGenerator
{
    use StringsGenerator;

    public static string $key = 'factory';

    public function run(bool $override = false): void
    {
        $factoryPath = $this->table->getFactoryPath();

        if ($factoryPath->exist()) {
            $factoryPath->logAlreadyExist("Generating Factory For ({$this->table->modelName}) Model");
            return;
        }

        $factoryPath->ensureDirectoryExists();

        $factoryAttributes = $this->generateFields();

        $stubProperties = [
            '{namespace}'         => config('cubeta-starter.factory_namespace'),
            '{class}'             => $this->table->modelName,
            '{usedTraits}'        => $this->getUsedTraits(),
            '{rows}'              => $factoryAttributes['rows'],
            '//relationFactories' => $factoryAttributes['relatedFactories'],
            '{modelNamespace}'    => $this->table->getModelNameSpace(),
        ];

        $this->generateFileFromStub($stubProperties, $factoryPath->fullPath);

        $factoryPath->format();

        CodeSniffer::make()->setModel($this->table)->checkForFactoryRelations();
    }

    private function generateFields(): array
    {
        $rows = '';
        $relatedFactories = '';

        $this->table->attributes()->each(function (CubeAttribute $attribute) use (&$rows) {
            $isUnique = $attribute->unique ? "->unique()" : "";
            $name = $attribute->name;
            $type = $attribute->type;

            if ($attribute->isKey()) {
                $relatedModel = CubeTable::create(str_replace('_id', '', $name));
                $rows .= "\t\t\t'{$name}' => " . $relatedModel->getModelClassString() . "::factory() ,\n";
            } elseif ($attribute->isTranslatable()) {
                $rows .= "\t\t\t'{$name}' => \\App\\Serializers\\Translatable::fake(),\n";
            } elseif ($type == ColumnTypeEnum::STRING->value) {
                $rows .= $this->handleStringType($name, $isUnique);
            } elseif ((in_array($name, ['cost', 'price', 'value', 'expense']) || Str::contains($name, ['price', 'cost'])) && ColumnTypeEnum::isNumericType($type)) {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->randomFloat(2, 0, 1000),\n";
            } elseif (Str::contains($name, 'description') && $type == ColumnTypeEnum::TEXT->value) {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->text(),\n";
            } elseif ($attribute->isFile()) {
                $rows .= "\t\t\t'{$name}' => \$this->storeFile(\Illuminate\Http\UploadedFile::fake()->image(\"image.png\")),\n";
            } elseif (Str::contains($name, 'age') && $type == ColumnTypeEnum::INTEGER->value) {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->numberBetween(15,60),\n";
            } elseif (Str::contains($name, 'time') && $type == ColumnTypeEnum::TIME->value) {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->time('H:i'),\n";
            } elseif ((Str::endsWith($name, '_at') || Str::contains($name, 'date')) && ColumnTypeEnum::isDateTimeType($type)) {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->date(),\n";
            } elseif (Str::startsWith($name, 'is_') && $type == ColumnTypeEnum::BOOLEAN->value) {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->boolean(),\n";
            } elseif ($type == ColumnTypeEnum::JSON->value) {
                $rows .= "\t\t\t'{$name}' => json_encode([fake()->word() => fake()->word()]), \n";
            } elseif ($fakerMethod = $this->getFakeMethod($type)) {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}{$fakerMethod}, \n";
            } else {
                $rows .= "\t\t\t '{$name}' => fake(){$isUnique}->{$type}(), \n";
            }
        });

        $this->table->relations()->each(function (CubeRelation $rel) use (&$relatedFactories) {
            if ($rel->isHasMany() || $rel->isManyToMany()) {
                if ($rel->getModelPath()->exist()) {
                    $relatedFactories .= $this->factoryRelationMethod($rel);
                }
            }
        });

        return ['rows' => $rows, 'relatedFactories' => $relatedFactories];
    }

    private function handleStringType(string $name, string $isUnique): string
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

        return "\t\t\t'{$originalName}' => fake(){$isUnique}->{$fakerMethod},\n";
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

    private function getUsedTraits(): string
    {
        $usedTraits = '';
        //TODO::when merging with dev there will be a config option for traits so make the use traits use the config option for traits namespace
        if (in_array('file', $this->attributes)) {
            $usedTraits .= "use \App\Traits\FileHandler; \n";
        }

        return $usedTraits;
    }

    protected function stubsPath(): string
    {
        return CubePath::stubPath('factory.stub');
    }
}
