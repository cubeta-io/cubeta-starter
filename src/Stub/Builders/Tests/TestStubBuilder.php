<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Tests;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Tests\TestAdditionalFactoryDataString;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self resourceNamespace(string $resourceNamespace)
 * @method self modelNamespace(string $modelNamespace)
 * @method self modelName(string $modelName)
 * @method self actor(string $actor)
 * @method self baseRouteName(string $baseRouteName)
 * @method self methodActor(string $methodActor)
 * @method self methodModelName(string $methodModelName)
 */
class TestStubBuilder extends ClassStubBuilder
{
    /** @var TestAdditionalFactoryDataString[] */
    private array $additionalFactoryData = [];

    public function additionalFactoryData(TestAdditionalFactoryDataString $additionalFactoryDataString): static
    {
        $this->additionalFactoryData[] = $additionalFactoryDataString;

        if ($additionalFactoryDataString->imports) {
            $this->import($additionalFactoryDataString->imports);
        }

        return $this;
    }

    protected function getStubPropertyArray(): array
    {
        $factoryData = implode(",\n", $this->additionalFactoryData);

        return [
            ...parent::getStubPropertyArray(),
            "{{additional_factory_data}}" => !empty($factoryData) ? "\n$factoryData\n" : ""
        ];
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Tests/Test.stub');
    }
}