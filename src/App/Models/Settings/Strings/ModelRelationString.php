<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings;

use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Helpers\Naming;

class ModelRelationString extends MethodString
{
    private CubeTable $relatedModel;
    private RelationsTypeEnum $type;

    public function __construct(string $relatedModelName, RelationsTypeEnum $relationsType)
    {
        $this->relatedModel = CubeTable::create(Naming::model($relatedModelName));
        $this->type = $relationsType;

        $relationsFunction = $this->relationFunction();
        $relatedModelNamespace = $this->relatedModel->getModelNameSpace(false);
        $modelName = $this->relatedModel->modelName;

        parent::__construct(
            $this->methodName(),
            [],
            [
                "return \$this->{$relationsFunction}($modelName::class)"
            ],
            returnType: $this->getReturnType(),
            imports: [
                new ImportString($relatedModelNamespace),
                $this->getRelationImportString()
            ]
        );
    }

    private function methodName()
    {
        return $this->relatedModel
            ->relationMethodNaming(
                singular: in_array($this->type, [RelationsTypeEnum::HasMany, RelationsTypeEnum::ManyToMany])
            );
    }

    private function relationFunction(): string
    {
        return match ($this->type) {
            RelationsTypeEnum::ManyToMany => "belongsToMany",
            RelationsTypeEnum::BelongsTo => "belongsTo",
            RelationsTypeEnum::HasMany => "hasMany",
            RelationsTypeEnum::HasOne => "hasOne"
        };
    }

    private function getReturnType(): string
    {
        return match ($this->type) {
            RelationsTypeEnum::ManyToMany => "BelongsToMany",
            RelationsTypeEnum::BelongsTo => "BelongsTo",
            RelationsTypeEnum::HasMany => "HasMany",
            RelationsTypeEnum::HasOne => "HasOne"
        };
    }

    private function getRelationImportString(): ImportString
    {
        return match ($this->type) {
            RelationsTypeEnum::ManyToMany => new ImportString("Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany"),
            RelationsTypeEnum::BelongsTo => new ImportString("Illuminate\\Database\\Eloquent\\Relations\\BelongsTo"),
            RelationsTypeEnum::HasMany => new ImportString("Illuminate\\Database\\Eloquent\\Relations\\HasMany"),
            RelationsTypeEnum::HasOne => new ImportString("Illuminate\\Database\\Eloquent\\Relations\\HasOne")
        };
    }
}