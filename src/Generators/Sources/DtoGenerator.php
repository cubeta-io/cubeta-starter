<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\PackageManager;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\StringValues\Contracts\Dtos\HasDtoCast;
use Cubeta\CubetaStarter\StringValues\Contracts\Dtos\HasDtoProperty;
use Cubeta\CubetaStarter\StringValues\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\Stub\Builders\Dtos\DtoStubBuilder;

class DtoGenerator extends AbstractGenerator
{
    public static string $key = 'dto';

    public function run(bool $override = false): void
    {
        $this->warnIfDtoPackageIsMissing();

        $dtoPath = $this->table->getDtoPath();

        DtoStubBuilder::make()
            ->namespace($this->table->getDtoNameSpace(false, true))
            ->modelName($this->table->modelName)
            ->dtoProperty(
                $this->table->attributes()
                    ->filter(fn (CubeAttribute $attribute) => $attribute instanceof HasDtoProperty)
                    ->map(fn (HasDtoProperty $attribute) => $attribute->dtoProperty())
                    ->toArray()
            )->rule(
                $this->table->attributes()
                    ->filter(fn (CubeAttribute $attribute) => $attribute instanceof HasPropertyValidationRule)
                    ->map(fn (HasPropertyValidationRule $attribute) => $attribute->propertyValidationRule())
                    ->toArray()
            )->cast(
                $this->table->attributes()
                    ->filter(fn (CubeAttribute $attribute) => $attribute instanceof HasDtoCast)
                    ->map(fn (HasDtoCast $attribute) => $attribute->dtoCast())
                    ->toArray()
            )->generate($dtoPath, $this->override);
    }

    /**
     * the generated DTOs extend a class from a third party package
     * so warn the user when it is not required by his project
     */
    private function warnIfDtoPackageIsMissing(): void
    {
        $composerJson = PackageManager::composerJson();

        if (! $composerJson
            || isset($composerJson['require'][self::DTO_PACKAGE])
            || isset($composerJson['require-dev'][self::DTO_PACKAGE])) {
            return;
        }

        CubeLog::warning(
            'The ['.self::DTO_PACKAGE.'] package is not installed, run [composer require '.self::DTO_PACKAGE.'] so the generated DTOs work',
            "Generating {$this->table->getDtoName()}"
        );
    }
}
