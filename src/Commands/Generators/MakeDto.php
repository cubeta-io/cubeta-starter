<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Generators\Sources\DtoGenerator;

class MakeDto extends BaseCommand
{
    public $description = 'Create a new validated data transfer object (DTO) for a model';

    public $signature = 'create:dto
        {name? : The name of the model, e.g. Post }
        {attributes? : model columns, format "field:type,field2:type2,..." }
        {nullables? : nullable columns, format "field,field2,..." }
        {uniques? : unique columns, format "field,field2,..." }
        {container? : api, web or both }
        {--force : overwrite the existing dto instead of skipping/prompting }';

    public function getHelp(): string
    {
        return <<<HELP
          Generates a validated DTO class (wendelladriel/laravel-validated-dto) for the given
          model's attributes. Used as an alternative, or in addition, to a FormRequest -
          see "php artisan cubeta:install --validation=".

          {$this->argumentFormatsHelp()}

          Examples:
            php artisan create:dto Post
            php artisan create:dto Post "title:string,body:text" "" "slug" api --force --no-interaction
          HELP;
    }

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName('DTO');
        $attributes = $this->argument('attributes') ?? null;
        $container = $this->argument('container') ?? ($this->askForContainer() ?? ContainerType::API);

        if (! $attributes) {
            [$attributes, $uniques, $nullables] = $this->askForModelAttributes(true, true);
        } else {
            $attributes = $this->resolveAttributes($attributes);
        }

        $unique = $this->resolveList($this->argument('uniques') ?? ($uniques ?? []));

        $nulls = $this->resolveList($this->argument('nullables') ?? ($nullables ?? []));

        $override = $this->askForOverride();

        $generator = new GeneratorFactory(DtoGenerator::$key);
        $generator->make(
            fileName: $modelName,
            attributes: $attributes,
            nullables: $nulls,
            uniques: $unique,
            generatedFor: $container,
            override: $override
        );
    }
}
