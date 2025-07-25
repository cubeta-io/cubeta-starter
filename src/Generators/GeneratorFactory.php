<?php

namespace Cubeta\CubetaStarter\Generators;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Settings\Settings;
use Error;
use Mockery\Exception;
use Throwable;

class GeneratorFactory
{
    private ?string $source;

    public function independentFromContainer(): array
    {
        return [
            Sources\MigrationGenerator::$key,
            Sources\ModelGenerator::$key,
            Sources\RequestGenerator::$key,
            Sources\FactoryGenerator::$key,
            Sources\SeederGenerator::$key,
            Sources\RepositoryGenerator::$key,
            Sources\ServiceGenerator::$key,
            Sources\ResourceGenerator::$key,
            Installers\BladePackagesInstaller::$key,
            Installers\PermissionsInstaller::$key,
            Installers\ReactTSInertiaInstaller::$key,
            Installers\ReactTsPackagesInstaller::$key,
            Installers\ApiInstaller::$key,
            Installers\WebInstaller::$key,
        ];
    }

    public function __construct(?string $source = null)
    {
        $this->source = $source;
    }

    public static function notNeedForRelations(): array
    {
        return [
            Sources\RequestGenerator::$key,
            Sources\SeederGenerator::$key,
            Sources\RepositoryGenerator::$key,
            Sources\ServiceGenerator::$key,
        ];
    }

    public static function noNeedForColumns(): array
    {
        return [
            Sources\SeederGenerator::$key,
            Sources\RepositoryGenerator::$key,
            Sources\ServiceGenerator::$key,
        ];
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
        bool    $override = false,
        string  $version = 'v1'
    ): void
    {
        if (!$this->source) {
            throw new Exception("Undefined Generator Factory Key Please Provide One");
        }

        $settings = Settings::make();

        if (
            ContainerType::isWeb($generatedFor)
            && !$settings->installedWeb()
            && !in_array($this->source, $this->independentFromContainer())
        ) {
            CubeLog::add(new CubeError("Install Web tools by running [php artisan cubeta:install web && php artisan cubeta:install web-packages] or [php artisan cubeta:install react-ts && php artisan cubeta:install react-ts-packages] and try again"));
            return;
        }

        if (
            ContainerType::isApi($generatedFor)
            && !$settings->installedApi()
            && !in_array($this->source, $this->independentFromContainer())
        ) {
            CubeLog::add(new CubeError("Install Api tools by running [php artisan cubeta:install api] and try again"));
            return;
        }

        $generator = match ($this->source) {
            Sources\MigrationGenerator::$key => new Sources\MigrationGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor,
                override: $override,
            ),
            Sources\ModelGenerator::$key => new Sources\ModelGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor,
                override: $override,
            ),
            Sources\RequestGenerator::$key => new Sources\RequestGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor,
                version: $version,
                override: $override,
            ),
            Sources\ResourceGenerator::$key => new Sources\ResourceGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor,
                version: $version,
                override: $override,
            ),
            Sources\FactoryGenerator::$key => new Sources\FactoryGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor,
                override: $override,
            ),
            Sources\SeederGenerator::$key => new Sources\SeederGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor,
                override: $override,
            ),
            Sources\RepositoryGenerator::$key => new Sources\RepositoryGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor,
                override: $override,
            ),
            Sources\ServiceGenerator::$key => new Sources\ServiceGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor,
                version: $version,
                override: $override,
            ),
            Sources\ControllerGenerator::$key => new Sources\ControllerGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
                actor: $actor,
                generatedFor: $generatedFor,
                version: $version,
                override: $override,
            ),
            Sources\TestGenerator::$key => new Sources\TestGenerator(
                fileName: $fileName,
                attributes: $attributes,
                actor: $actor,
                version: $version,
                override: $override,
            ),
            Installers\AuthInstaller::$key => new Installers\AuthInstaller(
                generatedFor: $generatedFor,
                version: $version,
                override: $override,
            ),
            Installers\ApiInstaller::$key => new Installers\ApiInstaller(version: $version , override: $override),
            Installers\WebInstaller::$key => new Installers\WebInstaller(version: $version, override: $override),
            Installers\BladePackagesInstaller::$key => new Installers\BladePackagesInstaller(override: $override),
            Installers\PermissionsInstaller::$key => new Installers\PermissionsInstaller(override: $override),
            Installers\ReactTSInertiaInstaller::$key => new Installers\ReactTSInertiaInstaller(override: $override),
            Installers\ReactTsPackagesInstaller::$key => new Installers\ReactTsPackagesInstaller(override: $override),
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
