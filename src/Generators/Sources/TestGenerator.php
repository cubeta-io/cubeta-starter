<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasTestAdditionalFactoryData;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Stub\Builders\Tests\TestStubBuilder;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Str;

class TestGenerator extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "test";

    public function run(bool $override = false): void
    {
        if ($this->generatedFor == ContainerType::API) {
            return;
        }
        $baseRouteName = $this->getRouteName($this->table, ContainerType::API, $this->actor) . '.';
        $testPath = $this->table->getTestPath();

        $actor = str($this->actor ?? "none")->lower()->singular()->toString();
        $builder = TestStubBuilder::make()
            ->namespace($this->table->getTestNamespace($this->actor, false, true))
            ->resourceNamespace($this->table->getResourceNameSpace(false))
            ->modelNamespace($this->table->getModelNameSpace(false))
            ->modelName($this->table->modelNaming())
            ->actor($actor)
            ->baseRouteName($baseRouteName)
            ->methodActor($actor == 'none' ? 'user' : $actor)
            ->methodModelName(Str::snake($this->table->modelName));

        $this->table->attributes()
            ->filter(fn(CubeAttribute $att) => $att instanceof HasTestAdditionalFactoryData)
            ->each(fn(HasTestAdditionalFactoryData $att) => $builder->additionalFactoryData($att->testAdditionalFactoryData()));

        $builder->generate($testPath, $this->override);
    }
}
