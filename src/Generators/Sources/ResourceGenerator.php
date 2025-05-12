<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Stub\Builders\Resources\ResourceStubBuilder;

class ResourceGenerator extends AbstractGenerator
{
    public static string $key = 'resource';

    public function run(bool $override = false): void
    {
        if (Settings::make()->installedWeb() && Settings::make()->getFrontendType() != FrontendTypeEnum::REACT_TS){
            CubeLog::error("Resource is available when generating for api or react frontend stack");
            return;
        }

        $resourcePath = $this->table->getResourcePath();

        ResourceStubBuilder::make()
            ->namespace($this->table->getResourceNameSpace(false, true))
            ->modelNamespace($this->table->getModelClassString())
            ->modelName($this->table->modelName)
            ->resourceField(
                $this->table->attributes()
                    ->filter(fn(CubeAttribute $attribute) => $attribute instanceof HasResourcePropertyString)
                    ->map(fn(HasResourcePropertyString $attribute) => $attribute->resourcePropertyString())
                    ->toArray()
            )->resourceField(
                $this->table->relations()
                    ->filter(
                        fn(CubeRelation $rel) => $rel instanceof HasResourcePropertyString
                            && $rel->getModelPath()->exist()
                            && $rel->getResourcePath()->exist()
                    )->map(fn(HasResourcePropertyString $attribute) => $attribute->resourcePropertyString())
                    ->toArray()
            )->generate($resourcePath, $this->override);

        CodeSniffer::make()->setModel($this->table)->checkForResourceRelations();
    }
}
