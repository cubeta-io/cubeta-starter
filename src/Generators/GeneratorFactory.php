<?php

namespace Cubeta\CubetaStarter\Generators;

use Cubeta\CubetaStarter\LogsMessages\CubeLog;
use Error;
use Mockery\Exception;
use Throwable;

class GeneratorFactory
{
    private string $source;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    public function make(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], ?string $actor = null): void
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
            CubeLog::add($exception);
            return;
        } catch (Throwable $e) {
            CubeLog::add($e);
            return;
        }
    }
}
