<?php

namespace Cubeta\CubetaStarter\Settings\Relations;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFactoryRelationMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models\HasModelRelationMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Settings\CubeRelation;
use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Factories\FactoryRelationMethodStringString;
use Cubeta\CubetaStarter\StringValues\Strings\Models\ModelRelationString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;

class CubeHasMany extends CubeRelation implements HasModelRelationMethod, HasDocBlockProperty, HasResourcePropertyString, HasFactoryRelationMethod,HasInterfacePropertyString
{
    public function factoryRelationMethod(): FactoryRelationMethodStringString
    {
        return new FactoryRelationMethodStringString(
            $this->relationModel,
        );
    }

    public function modelRelationMethod(): ModelRelationString
    {
        return new ModelRelationString(
            $this->relationModel,
            RelationsTypeEnum::HasMany,
        );
    }

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            str($this->relationModel)->plural()->lower()->toString(),
            "\Illuminate\Support\Collection<$this->relationModel>|null",
            imports: new PhpImportString($this->getModelNameSpace())
        );
    }

    public function resourcePropertyString(): ResourcePropertyString
    {
        return new ResourcePropertyString(
            str($this->relationModel)->plural()->snake()->lower()->toString(),
            "{$this->getResourceName()}::collection(\$this->whenLoaded('{$this->relationMethodNaming(singular: false)}'))",
            [
                new PhpImportString($this->getResourceNameSpace(false))
            ]
        );
    }

    public function interfacePropertyString(): InterfacePropertyString
    {
        $modelName = $this->modelNaming();
        return new InterfacePropertyString(
            $this->relationMethodNaming(singular: false),
            "{$modelName}[]",
            true,
            new TsImportString(
                $modelName,
                "@/Models/{$modelName}"
            )
        );
    }
}