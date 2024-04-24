<?php

namespace Cubeta\CubetaStarter\Generators;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Error;
use Mockery\Exception;
use Throwable;

class GeneratorFactory
{
    private ?string $source = null;

    public function __construct(?string $source = null)
    {
        $this->source = $source;
    }

    public static function getAllGeneratorsKeys(): array
    {
        return [
            Sources\MigrationGenerator::$key,
            Sources\ModelGenerator::$key,
            Sources\RequestGenerator::$key,
            Sources\ResourceGenerator::$key,
            Sources\FactoryGenerator::$key,
            Sources\SeederGenerator::$key,
            Sources\RepositoryGenerator::$key,
            Sources\ServiceGenerator::$key,
            Sources\ControllerGenerator::$key,
            Sources\TestGenerator::$key,
        ];
    }

    public function setSource(string $source): static
    {
        $this->source = $source;
        return $this;
    }

    public function make(
        string  $fileName = "",
        array   $attributes = [],
        array   $relations = [],
        array   $nullables = [],
        array   $uniques = [],
        ?string $actor = null,
        string  $generatedFor = ContainerType::API,
        bool    $override = true
    ): void
    {
        if (!$this->source) {
            throw new Exception("Undefined Generator Factory Key Please Provide One");
        }
        $generator = match ($this->source) {
            Sources\MigrationGenerator::$key => new Sources\MigrationGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor
            ),
            Sources\ModelGenerator::$key => new Sources\ModelGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor
            ),
            Sources\RequestGenerator::$key => new Sources\RequestGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor
            ),
            Sources\ResourceGenerator::$key => new Sources\ResourceGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor
            ),
            Sources\FactoryGenerator::$key => new Sources\FactoryGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor
            ),
            Sources\SeederGenerator::$key => new Sources\SeederGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor
            ),
            Sources\RepositoryGenerator::$key => new Sources\RepositoryGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor
            ),
            Sources\ServiceGenerator::$key => new Sources\ServiceGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor
            ),
            Sources\ControllerGenerator::$key => new Sources\ControllerGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor
            ),
            Sources\TestGenerator::$key => new Sources\TestGenerator(
                fileName: $fileName,
                attributes: $attributes,
                actor: $actor
            ),
            Installers\AuthInstaller::$key => new Installers\AuthInstaller(
                generatedFor: $generatedFor
            ),
            Installers\ApiInstaller::$key => new Installers\ApiInstaller(),
            Installers\WebInstaller::$key => new Installers\WebInstaller(),
            Installers\WebPackagesInstallers::$key => new Installers\WebPackagesInstallers(),
            Installers\PermissionsInstaller::$key => new Installers\PermissionsInstaller(),
            Installers\ReactInertiaInstaller::$key => new Installers\ReactInertiaInstaller(),
            default => throw new Error("Not supported generator {$this->source} "),
        };
        try {
            $generator->run($override);
        } catch (Exception $exception) {
            CubeLog::add($exception);
            return;
        } catch (Throwable $e) {
            CubeLog::add($e);
            return;
        }
    }
}
