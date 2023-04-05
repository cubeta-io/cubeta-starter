<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;

class MakeFactory extends Command
{
    use AssistCommand;

    public $signature = 'create:factory
        {name       : The name of the model }
        {attributes : columns with data types}?
        {relations  : the model relations}?';


    public $description = 'Create a new factory';

    /**
     * Handle the command
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function handle()
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes');
        $relations = $this->argument('relations');

        $this->createFactory($modelName, $attributes , $relations);
    }

    /**
     * @throws BindingResolutionException
     */
    private function createFactory($modelName, array $attributes , array $relations)
    {
        $factoryName = $this->getFactoryName($modelName);

        $factoryAttributes = $this->generateCols($attributes , $relations);

        $stubProperties = [
            '{class}' => $modelName,
            '{rows}' => $factoryAttributes['rows'],
            '//relationFactories' => $factoryAttributes['relatedFactories'],
        ];

        new CreateFile(
            $stubProperties,
            $this->getFactoryPath($factoryName),
            __DIR__ . '/stubs/factory.stub'
        );
        $this->line("<info>Created factory:</info> {$factoryName}");
    }

    private function getFactoryName($modelName)
    {
        return $modelName . 'Factory';
    }

    private function generateCols(array $attributes , array $relations)
    {
        $rows = '';
        $relatedFactories = '';
        foreach ($attributes as $name => $type) {
            if (Str::endsWith($name, '_at')) {
                $rows .= "\t\t\t'$name' => \$this->faker->date(),\n";

                continue;
            }
            if (Str::startsWith($name, 'is_')) {
                $rows .= "\t\t\t'$name' => \$this->faker->boolean(),\n";

                continue;
            }

            if ($type == 'key') {
                $relatedModel = ucfirst(Str::singular(str_replace('_id', '', $name)));
                $rows .= "\t\t\t'$name' => \App\Models\\$relatedModel::factory() ,\n";
            }

            if (array_key_exists($type, $this->typeFaker)) {
                $faker = $this->typeFaker["$type"];
                $rows .= "\t\t\t'$name' => $faker, \n";
            }
        }

        foreach ($relations as $rel => $type) {
           if($type == RelationsTypeEnum::HasMany || $type == RelationsTypeEnum::ManyToMany){
               $functionName = 'with'.ucfirst(Str::plural($rel)) ;
               $className = ucfirst(Str::singular($rel)) ;

               $relatedFactories.="
                public function $functionName(\$count = 1)
                {
                    return \$this->has(\App\Models\\$className::factory(\$count),);
                } \n" ;
           }
        }

        return ['rows' => $rows , 'relatedFactories' => $relatedFactories];
    }

    private function getFactoryPath($factoryName)
    {
        return $this->appDatabasePath() . '/factories' .
            "/$factoryName" . '.php';
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
        'json' => "{'" . '$this->faker->word' . "':'" . '$this->faker->word' . "'}",
    ];
}
