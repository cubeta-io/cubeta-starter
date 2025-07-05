<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Generators\Sources\WebControllers\BladeControllerGenerator;
use Cubeta\CubetaStarter\Generators\Sources\WebControllers\InertiaReactTSController;
use Cubeta\CubetaStarter\Settings\Settings;

class ControllerGenerator extends AbstractGenerator
{
    public static string $key = "controller";

    public function run(): void
    {
        if (ContainerType::isApi($this->generatedFor)) {
            $gen = new ApiControllerGenerator(
                fileName: $this->fileName,
                attributes: $this->attributes,
                relations: $this->relations,
                nullables: $this->nullables,
                uniques: $this->uniques,
                actor: $this->actor,
                generatedFor: $this->generatedFor,
                version: $this->version,
                override: $this->override,
            );

            $gen->run();
        }

        if (ContainerType::isWeb($this->generatedFor)) {
            $gen = match (Settings::make()->getFrontendType() ?? FrontendTypeEnum::NONE) {
                FrontendTypeEnum::REACT_TS => new InertiaReactTSController(
                    fileName: $this->fileName,
                    attributes: $this->attributes,
                    relations: $this->relations,
                    nullables: $this->nullables,
                    uniques: $this->uniques,
                    actor: $this->actor,
                    generatedFor: $this->generatedFor,
                    version: $this->version,
                    override: $this->override,
                ),
                default => new BladeControllerGenerator(
                    fileName: $this->fileName,
                    attributes: $this->attributes,
                    relations: $this->relations,
                    nullables: $this->nullables,
                    uniques: $this->uniques,
                    actor: $this->actor,
                    generatedFor: $this->generatedFor,
                    version: $this->version,
                    override: $this->override,
                )
            };

            $gen->run();
        }
    }
}
