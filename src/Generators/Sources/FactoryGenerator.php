<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Error;
use Illuminate\Support\Str;
use Throwable;

class FactoryGenerator extends AbstractGenerator
{
    public static string $key = 'factory';
    public static string $configPath = 'cubeta-starter.factory_path';

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $modelName = $this->modelName($this->fileName);
        $factoryName = $this->generatedFileName();
        $this->addToJsonFile();

        $factoryPath = $this->getGeneratingPath($factoryName);

        throw_if(file_exists($factoryPath), new Error("{$factoryName} Already Exists"));

        $factoryAttributes = $this->generateFields();

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.factory_namespace'),
            '{class}' => $modelName,
            '{usedTraits}' => $this->getUsedTraits(),
            '{rows}' => $factoryAttributes['rows'],
            '//relationFactories' => $factoryAttributes['relatedFactories'],
        ];

        $this->generateFileFromStub($stubProperties, $factoryPath);

        CodeSniffer::make()->setModel($modelName)->checkForFactoryRelations();

        $this->formatFile($factoryPath);
    }

    public function generatedFileName(): string
    {
        return $this->modelName($this->fileName) . 'Factory';
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/factory.stub';
    }

    private function generateFields(): array
    {
        $rows = '';
        $relatedFactories = '';
        foreach ($this->attributes as $name => $type) {
            $name = $this->columnName($name);
            $isUnique = in_array($name, $this->uniques) ? "->unique()" : "";

            if ($type == ColumnTypeEnum::KEY->value) {
                $relatedModel = $this->modelName(str_replace('_id', '', $name));
                $rows .= "\t\t\t'{$name}' => \\" . config('cubeta-starter.model_namespace') . "\\{$relatedModel}::factory() ,\n";
            } elseif ($type == ColumnTypeEnum::TRANSLATABLE->value) {
                $rows .= "\t\t\t'{$name}' => \$this->fakeTranslation('word'),\n";
            } elseif ($type == ColumnTypeEnum::STRING->value) {
                $rows .= $this->handleStringType($name, $isUnique);
            } elseif ((in_array($name, ['cost', 'price', 'value', 'expense']) || Str::contains($name, ['price', 'cost'])) && ColumnTypeEnum::isNumericType($type)) {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->randomFloat(2, 0, 1000),\n";
            } elseif (Str::contains($name, 'description') && $type == ColumnTypeEnum::TEXT->value) {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->text(),\n";
            } elseif ($type == ColumnTypeEnum::FILE->value) {
                $rows .= "\t\t\t'{$name}' => \$this->storeImageFromUrl(fake()->imageUrl)['name'],\n";
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
        }

        foreach ($this->relations as $rel => $type) {
            if ($type == RelationsTypeEnum::HasMany->value || $type == RelationsTypeEnum::ManyToMany->value) {
                if (!file_exists(getModelPath($rel))) {
                    continue;
                }
                $relatedFactories .= $this->factoryRelationMethod($rel);
            }
        }

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
        };

        return "\t\t\t'{$originalName}' => fake(){$isUnique}->{$fakerMethod},\n";
    }

    private function getFakeMethod(string $type): ?string
    {
        return match ($type) {
            ColumnTypeEnum::INTEGER->value,
            ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
            ColumnTypeEnum::BIG_INTEGER->value => '->numberBetween(1,2000)',
            ColumnTypeEnum::UNSIGNED_DOUBLE->value => '->randomFloat(1,2000)',
            ColumnTypeEnum::DOUBLE->value,
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

    private function factoryRelationMethod($relation): string
    {
        $modelName = $this->modelName($relation);
        $functionName = 'with' . ucfirst(Str::plural(Str::studly($modelName)));
        return "public function {$functionName}(\$count = 1)\n{\n\t return \$this->has(\\" . config('cubeta-starter.model_namespace') . "\\{$modelName}::factory(\$count));\n} \n";
    }

    private function getUsedTraits(): string
    {
        $usedTraits = '';
        //TODO::when merging with dev there will be a config option for traits so make the use traits use the config option for traits namespace
        if (in_array('file', $this->attributes)) {
            $usedTraits .= "use \App\Traits\FileHandler; \n";
        }

        if (in_array('translatable', $this->attributes)) {
            $usedTraits .= "use \App\Traits\Translations; \n";
        }

        return $usedTraits;
    }
}
