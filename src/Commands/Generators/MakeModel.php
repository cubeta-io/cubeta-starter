<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Settings\Settings;

class MakeModel extends BaseCommand
{
    public $description = 'Create a new model class';

    public $signature = 'create:model
        {name? : The name of the model }
        {attributes?}
        {nullables? : nullable columns}
        {uniques? : unique columns}
        {relations?}
        {actor?}
        {container?}
        {--migration} {--request} {--resource}
        {--factory} {--seeder} {--repository}
        {--service} {--controller} {--web_controller}
        {--test}';

    protected bool $useGui = false;

    public function handle(): void
    {
        $options = $this->options();
        $modelName = $this->argument('name') ?? $this->askForModelName("Model");
        $attributes = $this->argument('attributes') ?? null;

        if (!$attributes) {
            [$attributes, $uniques, $nullables] = $this->askForModelAttributes(true, true);
        }

        $relations = $this->argument('relations') ?? ($this->askForRelations($modelName) ?? []);

        $unique = $this->argument('uniques') ?? ($uniques ?? []);

        $nulls = $this->argument("nullables") ?? ($nullables ?? []);

        $actor = $this->argument('actor') ?? ($this->askForGeneratedFileActors("Model") ?? 'none');

        $container = $this->argument('container') ?? ($this->askForContainer() ?? ContainerType::API);

        $generator = new GeneratorFactory("model");

        $generator->make(fileName: $modelName, attributes: $attributes, relations: $relations, nullables: $nulls, uniques: $unique, actor: $actor);

        $this->handleCommandLogsAndErrors();

        $this->callAppropriateCommand($modelName, $options, $actor, $attributes, $relations, $nulls, $unique, $container);
    }

    /**
     * call to command base on the option flag
     */
    public function callAppropriateCommand(string $name, $options, string $actor, array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], string $container = ContainerType::API): void
    {
        $options = array_filter($options, function ($value) {
            return $value !== false && $value !== null;
        });

        if (!count($options)) {
            $result = 'all';
        } else {
            foreach ($options as $key => $option) {
                $result = match ($key) {
                    'migration' => $this->call('create:migration', ['name' => $name, 'attributes' => $attributes, 'relations' => $relations, 'nullables' => $nullables, 'uniques' => $uniques]),
                    'request' => $this->call('create:request', ['name' => $name, 'attributes' => $attributes, 'nullables' => $nullables, 'uniques' => $uniques, 'container' => $container]),
                    'resource' => $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $relations, 'container' => $container]),
                    'factory' => $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $relations, 'uniques' => $uniques]),
                    'seeder' => $this->call('create:seeder', ['name' => $name]),
                    'repository' => $this->call('create:repository', ['name' => $name]),
                    'service' => $this->call('create:service', ['name' => $name]),
                    'controller' => $this->call('create:controller', ['name' => $name, 'actor' => $actor]),
                    'web_controller' => $this->call('create:web-controller', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes, 'relations' => $relations, 'nullables' => $nullables]),
                    'test' => $this->call('create:test', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes]),
                };
            }
        }

        if (!isset($result) || $result === 'all') {
            $this->call('create:migration', ['name' => $name, 'attributes' => $attributes, 'relations' => $relations, 'nullables' => $nullables, 'uniques' => $uniques]);
            $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $relations, 'uniques' => $uniques]);
            $this->call('create:seeder', ['name' => $name]);
            $this->call('create:request', ['name' => $name, 'attributes' => $attributes, 'nullables' => $nullables, 'uniques' => $uniques, 'container' => $container]);
            $this->call('create:repository', ['name' => $name]);
            $this->call('create:service', ['name' => $name]);

            if ((ContainerType::isWeb($container) && Settings::make()->getFrontendType() == FrontendTypeEnum::REACT_TS)
                || ContainerType::isApi($container)
            ) {
                $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $relations, 'container' => $container]);
            }

            if (ContainerType::isApi($container)) {
                $this->call('create:controller', ['name' => $name, 'actor' => $actor]);
                $this->call('create:test', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes]);
            }

            if (ContainerType::isWeb($container)) {
                $this->call('create:web-controller', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes, 'relations' => $relations, 'nullables' => $nullables]);
            }
        }
    }
}
