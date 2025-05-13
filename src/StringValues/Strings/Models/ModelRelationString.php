<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Models;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\StringValues\Strings\MethodString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

class ModelRelationString extends MethodString
{
    private CubeTable $relatedModel;
    private RelationsTypeEnum $type;
    private array $otherParams = [];

    public function __construct(string $relatedModelName, RelationsTypeEnum $relationsType , array $otherParams = [])
    {
        $this->relatedModel = CubeTable::create(Naming::model($relatedModelName));
        $this->type = $relationsType;

        $relationsFunction = $this->relationFunction();
        $relatedModelNamespace = $this->relatedModel->getModelNameSpace(false);
        $modelName = $this->relatedModel->modelName;
        $this->otherParams = $otherParams;

        $methodOtherParams = implode(",", $this->otherParams);

        parent::__construct(
            $this->methodName(),
            [],
            [
                "return \$this->{$relationsFunction}($modelName::class , $methodOtherParams)"
            ],
            returnType: $this->getReturnType(),
            imports: [
                new PhpImportString($relatedModelNamespace),
                $this->getRelationImportString()
            ]
        );
    }

    private function methodName()
    {
        return $this->relatedModel
            ->relationMethodNaming(
                singular: in_array($this->type, [RelationsTypeEnum::HasOne, RelationsTypeEnum::BelongsTo])
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

    private function getRelationImportString(): PhpImportString
    {
        return match ($this->type) {
            RelationsTypeEnum::ManyToMany => new PhpImportString("Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany"),
            RelationsTypeEnum::BelongsTo => new PhpImportString("Illuminate\\Database\\Eloquent\\Relations\\BelongsTo"),
            RelationsTypeEnum::HasMany => new PhpImportString("Illuminate\\Database\\Eloquent\\Relations\\HasMany"),
            RelationsTypeEnum::HasOne => new PhpImportString("Illuminate\\Database\\Eloquent\\Relations\\HasOne")
        };
    }
}