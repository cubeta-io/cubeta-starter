<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;

class ControllerGenerator extends AbstractGenerator
{
    public static $key = "controller";

    public function run(bool $override = false): void
    {
        if (ContainerType::isApi($this->generatedFor)) {
            $gen = new ApiControllerGenerator(
                fileName: $this->fileName,
                attributes: $this->attributes,
                relations: $this->relations,
                nullables: $this->nullables,
                uniques: $this->uniques,
                actor: $this->actor,
                generatedFor: $this->generatedFor
            );

            $gen->run($override);
        }

        if (ContainerType::isWeb($this->generatedFor)) {
            $gen = new WebControllerGenerator(
                fileName: $this->fileName,
                attributes: $this->attributes,
                relations: $this->relations,
                nullables: $this->nullables,
                uniques: $this->uniques,
                actor: $this->actor,
                generatedFor: $this->generatedFor
            );

            $gen->run();
        }
    }
}
