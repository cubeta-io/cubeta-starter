<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class MakeFactory extends Command
{
    use AssistCommand;

    public $signature = 'create:factory
        {name       : The name of the model }
        {attributes : columns with data types}?
        {relations?  : the model relations}?';

    public $description = 'Create a new factory';

    private array $typeFaker = [
        'integer' => 'fake()->numberBetween(1,2000)',
        'bigInteger' => 'fake()->numberBetween(1,2000)',
        'unsignedBigInteger' => 'fake()->numberBetween(1,2000)',
        'unsignedDouble' => 'fake()->randomFloat(1,2000)',
        'double' => 'fake()->randomFloat(1,2000)',
        'float' => 'fake()->randomFloat(1,2000)',
        'string' => 'fake()->sentence()',
        'text' => 'fake()->text()',
        'json' => 'json_encode([fake()->word() => fake()->word()])',
        'boolean' => 'fake()->boolean()',
        'date' => 'fake()->date()',
        'time' => 'fake()->time()',
        'dateTime' => 'fake()->dateTime()',
        'timestamp' => 'fake()->dateTime()',
    ];

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes');
        $relations = $this->argument('relations');

        $this->createFactory($modelName, $attributes, $relations);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createFactory($modelName, array $attributes, array $relations): void
    {
        $modelName = modelNaming($modelName);

        $factoryName = $this->getFactoryName($modelName);

        $factoryAttributes = $this->generateCols($attributes, $relations);

        $factoryPath = $this->getFactoryPath($factoryName);
        if (file_exists($factoryPath)) {
            $this->error("$factoryName Already Exist");

            return;
        }

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.factory_namespace'),
            '{class}' => $modelName,
            '{rows}' => $factoryAttributes['rows'],
            '//relationFactories' => $factoryAttributes['relatedFactories'],
        ];

        generateFileFromStub(
            $stubProperties,
            $factoryPath,
            __DIR__ . '/stubs/factory.stub'
        );
        $this->formatFile($factoryPath);
        $this->info("Created factory: $factoryName");
    }

    private function getFactoryName($modelName): string
    {
        return $modelName . 'Factory';
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['rows' => 'string', 'relatedFactories' => 'string'])]
    private function generateCols(array $attributes, array $relations): array
    {
        $rows = '';
        $relatedFactories = '';
        foreach ($attributes as $name => $type) {
            if ($type == 'key') {
                $relatedModel = modelNaming(str_replace('_id', '', $name));
                $rows .= "\t\t\t'$name' => \App\Models\\$relatedModel::factory() ,\n";

                continue;
            }

            if ($type == 'translatable') {

                $availableLocales = config('cubeta-starter.available_locales');
                $rows .= "'$name' => json_encode([";

                foreach ($availableLocales as $locale) {
                    $rows .= "'$locale' => fake('$locale')->word() , \n";
                }

                $rows .= "]) ,\n";

                continue;
            }

            if (in_array($name, ['name', 'username', 'first_name', 'last_name', 'user_name'])) {
                $rows .= "\t\t\t'$name' => fake()->firstName(),\n";

                continue;
            }

            if ($name == 'email') {
                $rows .= "\t\t\t'$name' => fake()->email(),\n";

                continue;
            }

            if (in_array($name, ['cost', 'price', 'value', 'expense']) || Str::contains($name, ['price', 'cost'])) {
                $rows .= "\t\t\t'$name' => fake()->randomFloat(2, 0, 1000),\n";

                continue;
            }

            if (Str::contains($name, 'description')) {
                $rows .= "\t\t\t'$name' => fake()->text(),\n";

                continue;
            }

            if (Str::contains($name, 'phone')) {
                $rows .= "\t\t\t'$name' => fake()->phoneNumber(),\n";

                continue;
            }

            if (Str::contains($name, ['image', 'icon', 'logo', 'photo'])) {
                $rows .= "\t\t\t'$name' => fake()->imageUrl(),\n";

                continue;
            }

            if (Str::contains($name, ['longitude', '_lon', '_lng', 'lon_', 'lng_']) || $name == 'lng' || $name == 'lon') {
                $rows .= "\t\t\t'$name' => fake()->longitude(),\n";

                continue;
            }

            if (Str::contains($name, ['latitude ', '_lat', 'lat_']) || $name == 'lat') {
                $rows .= "\t\t\t'$name' => fake()->latitude(),\n";

                continue;
            }

            if (Str::contains($name, 'address')) {
                $rows .= "\t\t\t'$name' => fake()->address(),\n";

                continue;
            }

            if (Str::contains($name, 'street')) {
                $rows .= "\t\t\t'$name' => fake()->streetName(),\n";

                continue;
            }

            if (Str::contains($name, 'city')) {
                $rows .= "\t\t\t'$name' => fake()->city(),\n";

                continue;
            }

            if (Str::contains($name, ['zip', 'post_code', 'postcode', 'PostCode', 'postCode', 'ZIP'])) {
                $rows .= "\t\t\t'$name' => fake()->postcode(),\n";

                continue;
            }

            if (Str::contains($name, 'country')) {
                $rows .= "\t\t\t'$name' => fake()->country(),\n";

                continue;
            }

            if (Str::contains($name, 'age')) {
                $rows .= "\t\t\t'$name' => fake()->numberBetween(15,60),\n";

                continue;
            }

            if (Str::contains($name, 'gender')) {
                $rows .= "\t\t\t'$name' => fake()->randomElement(['male' , 'female']),\n";

                continue;
            }

            if (Str::contains($name, 'time')) {
                $rows .= "\t\t\t'$name' => fake()->time(),\n";

                continue;
            }

            if (Str::endsWith($name, '_at') || Str::contains($name, 'date')) {
                $rows .= "\t\t\t'$name' => fake()->date(),\n";

                continue;
            }
            if (Str::startsWith($name, 'is_')) {
                $rows .= "\t\t\t'$name' => fake()->boolean(),\n";

                continue;
            }

            if (array_key_exists($type, $this->typeFaker)) {
                $faker = $this->typeFaker["$type"];
                $rows .= "\t\t\t'$name' => $faker, \n";
            }
        }

        foreach ($relations as $rel => $type) {
            if ($type == RelationsTypeEnum::HasMany || $type == RelationsTypeEnum::ManyToMany) {
                $functionName = 'with' . ucfirst(Str::plural(Str::studly($rel)));
                $className = modelNaming($rel);

                $relatedFactories .= "
                public function $functionName(\$count = 1)
                {
                    return \$this->has(\App\Models\\$className::factory(\$count));
                } \n";
            }
        }

        return ['rows' => $rows, 'relatedFactories' => $relatedFactories];
    }

    private function getFactoryPath($factoryName): string
    {
        $factoryDirectory = base_path(config('cubeta-starter.factory_path'));
        ensureDirectoryExists($factoryDirectory);

        return "$factoryDirectory/$factoryName.php";
    }
}
