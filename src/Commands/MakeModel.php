<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use JetBrains\PhpStorm\ArrayShape;

class MakeModel extends BaseCommand
{
    public $description = 'Create a new model class';

    public $signature = 'create:model
        {name : The name of the model }
        {attributes?}
        {nullables? : nullable columns}
        {uniques? : unique columns}
        {relations?}
        {actor?}
        {container?}
        {gui?}
        {--migration} {--request} {--resource}
        {--factory} {--seeder} {--repository}
        {--service} {--controller} {--web_controller}
        {--test} {--postman_collection}';

    protected bool $useGui = false;

    public function handle(): void
    {
        $this->info("Creating Model");
        $name = $this->argument('name');
        $options = $this->options();
        $guiAttributes = $this->argument('attributes') ?? [];
        $nullables = $this->argument('nullables') ?? [];
        $uniques = $this->argument('uniques') ?? [];
        $relations = $this->argument('relations') ?? [];
        $actor = $this->argument('actor') ?? 'none';
        $this->useGui = $this->argument('gui') ?? false;

        $generator = new GeneratorFactory("model");
        $generator->make(fileName: $name, attributes: $guiAttributes, relations: $relations, nullables: $nullables, uniques: $uniques, actor: $actor);
        $this->handleCommandLogsAndErrors();
        $this->callAppropriateCommand($name, $options, $actor, $guiAttributes, $relations, $nullables, $uniques);
    }

    /**
     * call to command base on the option flag
     */
    public function callAppropriateCommand(string $name, $options, string $actor, array $attributes = [], array $relations = [], array $nullables = [], array $uniques = []): void
    {
        $container = $this->checkContainer();
        $options = array_filter($options, function ($value) {
            return $value !== false && $value !== null;
        });

        if (!count($options)) {
            $result = 'all';
        } else {
            foreach ($options as $key => $option) {
                $result = match ($key) {
                    'migration' => $this->call('create:migration', ['name' => $name, 'attributes' => $attributes, 'relations' => $relations, 'nullables' => $nullables, 'uniques' => $uniques]),
                    'request' => $this->call('create:request', ['name' => $name, 'attributes' => $attributes, 'nullables' => $nullables, 'uniques' => $uniques]),
                    'resource' => $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $relations]),
                    'factory' => $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $relations, 'uniques' => $uniques]),
                    'seeder' => $this->call('create:seeder', ['name' => $name]),
                    'repository' => $this->call('create:repository', ['name' => $name]),
                    'service' => $this->call('create:service', ['name' => $name]),
                    'controller' => $this->call('create:controller', ['name' => $name, 'actor' => $actor]),
                    'web_controller' => $this->call('create:web-controller', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes, 'relations' => $relations, 'nullables' => $nullables]),
                    'test' => $this->call('create:test', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes]),
                    'postman_collection' => $this->call('create:postman-collection', ['name' => $name, 'attributes' => $attributes]),
                };
            }
        }

        if (!isset($result) || $result === 'all') {
            $this->call('create:migration', ['name' => $name, 'attributes' => $attributes, 'relations' => $relations, 'nullables' => $nullables, 'uniques' => $uniques]);
            $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $relations, 'uniques' => $uniques]);
            $this->call('create:seeder', ['name' => $name]);
            $this->call('create:request', ['name' => $name, 'attributes' => $attributes, 'nullables' => $nullables, 'uniques' => $uniques]);
            $this->call('create:repository', ['name' => $name]);
            $this->call('create:service', ['name' => $name]);

            if ($container['api']) {
                $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $relations]);
                $this->call('create:controller', ['name' => $name, 'actor' => $actor]);
                $this->call('create:test', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes]);
                $this->call('create:postman-collection', ['name' => $name, 'attributes' => $attributes]);
            }

            if ($container['web']) {
                $this->call('create:web-controller', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes, 'relations' => $relations, 'nullables' => $nullables]);
            }
        }
    }

    /**
     * get the container type from the user
     *
     * @return bool[]
     */
    #[ArrayShape(['api' => 'bool', 'web' => 'bool'])]
    public function checkContainer(): array
    {
        if (!$this->useGui) {
            $container = $this->choice('<info>What is the container type of this model controller</info>', ['api', 'web', 'both'], 'api');
        } else {
            $container = $this->argument('container');
            if (!in_array($container, ContainerType::ALL)) {
                $this->error('Invalid container use one of this strings as an input : [api , web , both]');
                return ['api' => false, 'web' => false];
            }
        }
        return [
            'api' => $container == ContainerType::API || $container == ContainerType::BOTH,
            'web' => $container == ContainerType::WEB || $container == ContainerType::BOTH,
        ];
    }
}
