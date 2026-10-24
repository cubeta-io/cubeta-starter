<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Models;

use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Cubeta\CubetaStarter\StringValues\Strings\MethodString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;

class ModelRelationString extends MethodString
{
    private array $otherParams = [];

    private CubeTable $relatedModel;

    private RelationsTypeEnum $type;

    public function __construct(string $relatedModelName, RelationsTypeEnum $relationsType, array $otherParams = [])
    {
        $this->relatedModel = CubeTable::create(Naming::model($relatedModelName));
        $this->type = $relationsType;

        $relationsFunction = $this->relationFunction();
        $relatedModelNamespace = $this->relatedModel->getModelNameSpace(false);
        $modelName = $this->relatedModel->modelName;
        $this->otherParams = $otherParams;

        $methodOtherParams = implode(',', $this->otherParams);
        $returnType = $this->getReturnType();

        parent::__construct(
            $this->methodName(),
            [],
            [
                "return \$this->{$relationsFunction}($modelName::class , $methodOtherParams)",
            ],
            returnType: $returnType,
            imports: [
                new PhpImportString($relatedModelNamespace),
                $this->getRelationImportString(),
            ],
            docBlocs: [
                new DocBlockPropertyString(
                    name: "$returnType<{$this->relatedModel->modelName}, static>",
                    tag: 'return',
                    imports: [$this->getRelationImportString(), new PhpImportString($relatedModelNamespace)]
                ),
            ]
        );
    }

    private function getRelationImportString(): PhpImportString
    {
        return match ($this->type) {
            RelationsTypeEnum::ManyToMany => new PhpImportString(BelongsToMany::class),
            RelationsTypeEnum::BelongsTo  => new PhpImportString(BelongsTo::class),
            RelationsTypeEnum::HasMany    => new PhpImportString(HasMany::class),
            RelationsTypeEnum::HasOne     => new PhpImportString(HasOne::class)
        };
    }

    private function getReturnType(): string
    {
        return match ($this->type) {
            RelationsTypeEnum::ManyToMany => 'BelongsToMany',
            RelationsTypeEnum::BelongsTo  => 'BelongsTo',
            RelationsTypeEnum::HasMany    => 'HasMany',
            RelationsTypeEnum::HasOne     => 'HasOne'
        };
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
            RelationsTypeEnum::ManyToMany => 'belongsToMany',
            RelationsTypeEnum::BelongsTo  => 'belongsTo',
            RelationsTypeEnum::HasMany    => 'hasMany',
            RelationsTypeEnum::HasOne     => 'hasOne'
        };
    }
}
