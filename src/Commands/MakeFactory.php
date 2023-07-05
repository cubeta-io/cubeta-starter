<?php

namespace Cubeta\CubetaStarter\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\ArrayShape;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

class MakeFactory extends Command
{
    use AssistCommand;

    public $description = 'Create a new factory';

    public $signature = 'create:factory
        {name       : The name of the model }
        {attributes? : columns with data types}
        {relations?  : the model relations}
        {uniques? : unique columns}';

    private array $typeFaker = [
        'integer' => '->numberBetween(1,2000)',
        'bigInteger' => '->numberBetween(1,2000)',
        'unsignedBigInteger' => '->numberBetween(1,2000)',
        'unsignedDouble' => '->randomFloat(1,2000)',
        'double' => '->randomFloat(1, 1, 100)',
        'float' => '->randomFloat(1, 1, 100)',
        'string' => '->sentence()',
        'text' => '->text()',
        'boolean' => '->boolean()',
        'date' => '->date()',
        'time' => '->time()',
        'dateTime' => '->dateTime()',
        'timestamp' => '->dateTime()',
    ];

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes') ?? [];
        $relations = $this->argument('relations') ?? [];
        $uniques = $this->argument('uniques') ?? [];

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createFactory($modelName, $attributes, $relations, $uniques);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createFactory($modelName, array $attributes = [], array $relations = [], array $uniques = []): void
    {
        $modelName = modelNaming($modelName);

        $factoryName = $this->getFactoryName($modelName);

        $factoryAttributes = $this->generateCols($attributes, $relations, $uniques);

        $factoryPath = $this->getFactoryPath($factoryName);
        if (file_exists($factoryPath)) {
            $this->error("{$factoryName} Already Exists");

            return;
        }

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.factory_namespace'),
            '{class}' => $modelName,
            '{usedTraits}' => $this->getUsedTraits($attributes),
            '{rows}' => $factoryAttributes['rows'],
            '//relationFactories' => $factoryAttributes['relatedFactories'],
        ];

        generateFileFromStub(
            $stubProperties,
            $factoryPath,
            __DIR__ . '/stubs/factory.stub'
        );
        $this->formatFile($factoryPath);
        $this->info("Created factory: {$factoryName}");
    }

    /**
     * @return string[]\
     */
    #[ArrayShape(['rows' => 'string', 'relatedFactories' => 'string'])]
    private function generateCols(array $attributes = [], array $relations = [], array $uniques = []): array
    {
        $rows = '';
        $relatedFactories = '';
        foreach ($attributes as $name => $type) {
            $name = columnNaming($name);
            $isUnique = in_array($name, $uniques) ? "->unique()" : "";

            if ($type == 'key') {
                $relatedModel = modelNaming(str_replace('_id', '', $name));
                $rows .= "\t\t\t'{$name}' => \\" . config('cubeta-starter.model_namespace') . "\\{$relatedModel}::factory() ,\n";
            } elseif ($type == 'translatable') {
                $availableLocales = config('cubeta-starter.available_locales');
                $rows .= "'{$name}' => json_encode([";

                foreach ($availableLocales as $locale) {
                    $rows .= "'{$locale}' => fake('{$locale}'){$isUnique}->word() , ";
                }
                $rows .= "]) ,\n";

            } elseif (in_array($name, ['name', 'username', 'first_name', 'last_name', 'user_name']) && $type == 'string') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->firstName(),\n";

            } elseif ($name == 'email' && $type == 'string') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->email(),\n";

            } elseif (
                (in_array($name, ['cost', 'price', 'value', 'expense']) || Str::contains($name, ['price', 'cost'])) && in_array($type, ['integer', 'bigInteger', 'unsignedBigInteger', 'unsignedDouble', 'double', 'float'])
            ) {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->randomFloat(2, 0, 1000),\n";

            } elseif (Str::contains($name, 'description') && $type == 'text') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->text(),\n";

            } elseif (Str::contains($name, 'phone') && $type == 'string') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->phoneNumber(),\n";

            } elseif (Str::contains($name, ['image', 'icon', 'logo', 'photo']) && $type == 'file') {
                $rows .= "\t\t\t'{$name}' => \$this->storeImageFromUrl(fake()->imageUrl)['name'],\n";

            } elseif ((Str::contains($name, ['longitude', '_lon', '_lng', 'lon_', 'lng_']) || $name == 'lng' || $name == 'lon') && $type == 'string') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->longitude(),\n";

            } elseif ((Str::contains($name, ['latitude ', '_lat', 'lat_']) || $name == 'lat') && $type == 'string') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->latitude(),\n";

            } elseif (Str::contains($name, 'address') && $type == 'string') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->address(),\n";

            } elseif (Str::contains($name, 'street') && $type == 'string') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->streetName(),\n";

            } elseif (Str::contains($name, 'city') && $type == 'string') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->city(),\n";

            } elseif (Str::contains($name, ['zip', 'post_code', 'postcode', 'PostCode', 'postCode', 'ZIP']) && $type == 'string') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->postcode(),\n";

            } elseif (Str::contains($name, 'country') && $type == 'string') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->country(),\n";

            } elseif (Str::contains($name, 'age') && $type == 'integer') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->numberBetween(15,60),\n";

            } elseif (Str::contains($name, 'gender') && $type == 'string') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->randomElement(['male' , 'female']),\n";

            } elseif (Str::contains($name, 'time') && $type == 'time') {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->time(),\n";

            } elseif ((Str::endsWith($name, '_at') || Str::contains($name, 'date')) && in_array($type, ['date', 'time', 'dateTime', 'timestamp'])) {
                $rows .= "\t\t\t'{$name}' => fake(){$isUnique}->date(),\n";

            } elseif (Str::startsWith($name, 'is_') && $type == 'boolean') {
                $rows .= "\t\t\t'{
                $name}' => fake(){$isUnique}->boolean(),\n";

            } elseif ($type == 'json') {
                $rows .= "\t\t\t'{$name}' => 'json_encode([fake(){$isUnique}->word() => fake(){$isUnique}->word()])',\n";
            } elseif (array_key_exists($type, $this->typeFaker)) {
                $faker = $this->typeFaker["{$type}"];
                $rows .= "\t\t\t'{
                $name}' => fake(){$isUnique}{$faker}, \n";
            } else {
                $rows .= "\t\t\t '{
                $name}' => fake(){$isUnique}->$type(), \n";
            }
        }

        foreach ($relations as $rel => $type) {
            if ($type == RelationsTypeEnum::HasMany || $type == RelationsTypeEnum::ManyToMany) {
                $functionName = 'with' . ucfirst(Str::plural(Str::studly($rel)));
                $className = modelNaming($rel);

                $relatedFactories .= "
                public function {$functionName}(\$count = 1)
                {
                    return \$this->has(\\" . config('cubeta - starter . model_namespace') . "\\{$className}::factory(\$count));
                } \n";
            }
        }

        return ['rows' => $rows, 'relatedFactories' => $relatedFactories];
    }

    private function getFactoryName($modelName): string
    {
        return $modelName . 'Factory';
    }

    private function getFactoryPath($factoryName): string
    {
        $factoryDirectory = base_path(config('cubeta - starter . factory_path'));
        ensureDirectoryExists($factoryDirectory);

        return "{$factoryDirectory}/{$factoryName}.php";
    }

    /**
     * @param array $attributes
     * @return string
     */
    private function getUsedTraits(array $attributes = []): string
    {
        $usedTraits = '';

        if (in_array('file', $attributes)) {
            $usedTraits .= "use \Cubeta\CubetaStarter\Traits\FileHandler; \n";
        }

        return $usedTraits;
    }
}
