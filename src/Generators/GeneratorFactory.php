<?php

namespace Cubeta\CubetaStarter\Generators;

use Error;
use Mockery\Exception;

class GeneratorFactory
{
    private static $instance;
    public array $logs = [];
    private string $source;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    public static function make(string $source): GeneratorFactory
    {
        if (self::$instance == null) {
            self::$instance = new self($source);
        }

        self::$instance->source = $source;
        return self::$instance;
    }


    public function run(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], ?string $actor = null): void
    {
        $generator = match ($this->source) {
            Sources\MigrationGenerator::$key => new Sources\MigrationGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques
            ),
            Sources\ModelGenerator::$key => new Sources\ModelGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
            ),
            Sources\RequestGenerator::$key => new Sources\RequestGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
            ),
            Sources\ResourceGenerator::$key => new Sources\ResourceGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
            ),
            Sources\FactoryGenerator::$key => new Sources\FactoryGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor
            ),
            Sources\SeederGenerator::$key => new Sources\SeederGenerator(
                fileName: $fileName,
            ),
            Sources\RepositoryGenerator::$key => new Sources\RepositoryGenerator(
                fileName: $fileName,
            ),
            Sources\ServiceGenerator::$key => new Sources\ServiceGenerator(
                fileName: $fileName,
            ),
            Sources\ApiControllerGenerator::$key => new Sources\ApiControllerGenerator(
                fileName: $fileName,
                actor: $actor
            ),
            default => throw new Error("Not supported generator"),
        };
        try {
            $generator->run();
        } catch (Exception $exception) {
            $this->logs[] = $exception->getMessage();
        }
        $this->logs = array_merge($this->logs, $generator->logs);
    }
}
