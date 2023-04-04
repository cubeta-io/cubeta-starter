<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeFactory extends Command
{
    use AssistCommand;

    public $signature = 'create:factory
        {name : The name of the model }
        {attributes : columns with data types}?';

    public $description = 'Create a new factory';

    /**
     * Handle the command
     *
     * @return void
     */
    public function handle()
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes');

        $this->createFactory($modelName, $attributes);
    }

    private function createFactory($modelName, array $attributes)
    {
        $factoryName = $this->getFactoryName($modelName);

        $factoryAttributes = $this->generateCols($attributes);

        $stubProperties = [
            '{class}' => $modelName,
            '{useStatements}' => $factoryAttributes['useStatements'],
            '{relations_random}' => $factoryAttributes['relations_random'],
            '{rows}' => $factoryAttributes['rows'],
        ];

        new CreateFile(
            $stubProperties,
            $this->getFactoryPath($factoryName),
            __DIR__.'/stubs/factory.stub'
        );
        $this->line("<info>Created factory:</info> {$factoryName}");
    }

    private function getFactoryName($modelName)
    {
        return $modelName.'Factory';
    }

    private function generateCols(array $attributes)
    {
        $rows = '';
        $relations_random = '';
        $useStatements = '';
        foreach ($attributes as $name => $type) {
            if (Str::endsWith($name, '_at')) {
                $rows .= "\t\t\t'$name' => \$this->faker->date(),\n";

                continue;
            }
            if (Str::startsWith($name, 'is_')) {
                $rows .= "\t\t\t'$name' => \$this->faker->boolean(),\n";

                continue;
            }

            if(in_array($type , RelationsTypeEnum::ALL)){

                if($type == RelationsTypeEnum::BelongsTo || $type == RelationsTypeEnum::HasOne){
                    $relatedModel = ucfirst(Str::singular(str_replace('_id' , '' , $name))) ;
                }
                else {
                    $relatedModel = ucfirst(Str::singular($name)) ;
                }

                $rows .= "\t\t\t'$name' => \App\Models\\$relatedModel::factory() ,\n";
            }

            if (array_key_exists($type, $this->typeFaker)) {
                $faker = $this->typeFaker["$type"];
                $rows .= "\t\t\t'$name' => $faker, \n";
            }
        }

        return ['rows' => $rows, 'relations_random' => $relations_random, 'useStatements' => $useStatements];
    }

    private function getFactoryPath($factoryName)
    {
        return $this->appDatabasePath().'/factories'.
            "/$factoryName".'.php';
    }

    private array $typeFaker = [
        'integer' => '$this->faker->numberBetween(1,2000)',
        'bigInteger' => '$this->faker->numberBetween(1,2000)',
        'unsignedBigInteger' => '$this->faker->numberBetween(1,2000)',
        'unsignedDouble' => '$this->faker->randomFloat(1,2000)',
        'double' => '$this->faker->randomFloat(1,2000)',
        'float' => '$this->faker->randomFloat(1,2000)',
        'string' => '$this->faker->sentence',
        'text' => '$this->faker->text',
        'json' => "{'".'$this->faker->word'."':'".'$this->faker->word'."'}",
    ];
}
