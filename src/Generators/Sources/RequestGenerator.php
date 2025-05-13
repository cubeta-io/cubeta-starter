<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Stub\Builders\Requests\RequestStubBuilder;

class RequestGenerator extends AbstractGenerator
{
    public static string $key = 'request';

    public function run(bool $override = false): void
    {
        $requestPath = $this->table->getRequestPath();

        RequestStubBuilder::make()
            ->namespace($this->table->getRequestNameSpace(false, true))
            ->modelName($this->table->modelName)
            ->rule(
                $this->table->attributes()
                    ->filter(fn(CubeAttribute $attribute) => $attribute instanceof HasPropertyValidationRule)
                    ->map(fn(HasPropertyValidationRule $attribute) => $attribute->propertyValidationRule())
                    ->toArray()
            )->generate($requestPath, $this->override);
    }
}
