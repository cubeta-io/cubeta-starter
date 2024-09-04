<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Illuminate\Support\Str;

class ModelGenerator extends AbstractGenerator
{
    public static string $key = 'model';

    use StringsGenerator;

    public function run(bool $override = false): void
    {
        $modelPath = $this->table->getModelPath();

        if ($modelPath->exist()) {
            $modelPath->logAlreadyExist("Generating {$this->table->modelName} Model");
            return;
        }

        $modelPath->ensureDirectoryExists();

        $modelAttributes = $this->generateModelClassAttributes();

        $stubProperties = [
            '{namespace}'           => config('cubeta-starter.model_namespace'),
            '{modelName}'           => $this->table->modelName,
            '{relations}'           => $modelAttributes['relationsCode'],
            '{properties}'          => $modelAttributes['properties'],
            '{fileGetter}'          => $modelAttributes['filesFunctions'],
            '{fillable}'            => $modelAttributes['fillable'],
            '{filesKeys}'           => $modelAttributes['filesKeys'],
            '{scopes}'              => $modelAttributes['booleanValueScopes'],
            '{searchableKeys}'      => $modelAttributes['searchable'],
            '{searchableRelations}' => $modelAttributes['relationSearchable'],
            '{casts}'               => $modelAttributes['casts'],
            '{exportables}'         => $modelAttributes['exportables'],
        ];

        $this->generateFileFromStub($stubProperties, $modelPath->fullPath);

        $modelPath->format();

        CodeSniffer::make()->setModel($this->table)->checkForModelsRelations();
    }

    private function generateModelClassAttributes(): array
    {
        $fillable = '';
        $exportables = '';
        $filesKeys = '';
        $searchable = '';
        $relationsSearchable = '';
        $properties = "/**  \n";
        $fileFunctions = '';
        $booleanValueScope = '';
        $casts = '';
        $relationsFunctions = '';

        $this->table->attributes()->each(function (CubeAttribute $attribute) use (
            &$fileFunctions,
            &$filesKeys,
            &$searchable,
            &$relationsFunctions,
            &$properties,
            &$casts,
            &$booleanValueScope,
            &$exportables,
            &$fillable
        ) {
            $fillable .= "'{$attribute->name}' ,\n";

            $nullableProperty = $attribute->nullable ? "null|" : "";

            if (!$attribute->isKey()) {
                $exportables .= "'{$attribute->name}' ,\n";
            }

            if ($attribute->type === ColumnTypeEnum::BOOLEAN->value) {
                $booleanValueScope .= "\tpublic function scope" . ucfirst(Str::studly($attribute->name)) . "(\$query)\t\n{\n\t\treturn \$query->where('" . $attribute->name . "' , 1);\n\t}\n";
                $casts .= "'{$attribute->name}' => 'boolean' , \n";
            } elseif ($attribute->isTranslatable()) {
                $searchable .= "'{$attribute->name}' , \n";
                $casts .= "'{$attribute->name}' => \\App\\Casts\\Translatable::class, \n";
            } elseif ($attribute->isKey()) {
                $relatedModelName = $attribute->modelNaming(str_replace('_id', '', $attribute->name));
                $relatedModel = CubeTable::create($relatedModelName);
                $properties .= "* @property {$nullableProperty}integer {$attribute->name} \n";

                if ($relatedModel->getModelPath()->exist()) {
                    $relationsFunctions .= $this->belongsToFunction($relatedModel);
                }
            } elseif ($attribute->isString()) {
                $searchable .= "'{$attribute->name}' , \n";
                $properties .= "* @property {$nullableProperty}string {$attribute->name} \n";

            } elseif ($attribute->isFile()) {
                $filesKeys .= "'{$attribute->name}' ,\n";
                $properties .= "* @property {$nullableProperty}string {$attribute->name} \n";
                $colName = $attribute->modelNaming();
                $fileFunctions .= 'protected function ' . $attribute->name . '(): \Illuminate\Database\Eloquent\Casts\Attribute
                                   {
                                        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
                                            get: fn ($value, array $attributes) => $value != null ? asset(\'storage/\' . $value) : null,
                                        );
                                   }';
                FileUtils::ensureDirectoryExists(storage_path('app/public/' . Str::lower($this->table->modelName) . '/' . Str::plural($colName)));

            } elseif (ColumnTypeEnum::isDateTimeType($attribute->type)) {
                $properties .= "* @property {$nullableProperty}string {$attribute->name} \n";
            } elseif (ColumnTypeEnum::isNumericType($attribute->type)) {
                $properties .= "* @property {$nullableProperty}numeric {$attribute->name} \n";
            } else {
                $properties .= "* @property {$nullableProperty}{$attribute->type} {$attribute->name} \n";
            }
        });

        $this->table->relations()->each(function (CubeRelation $relation) use (&$relationsFunctions, &$relationsSearchable, &$exportables, &$properties) {

            $relatedTable = $relation->getTable();

            if ($relation->getModelPath()->exist()) {
                if ($relation->isHasMany()) {
                    $relationsFunctions .= $this->hasManyFunction($relation);
                    $properties .= "* @property  \Illuminate\Support\Collection<{$relation->modelName}>|\Illuminate\Database\Eloquent\Collection<{$relation->modelName}>|{$relation->modelName}[] {$relation->method()}\n";
                    $properties .= "* @method  \Illuminate\Database\Eloquent\Relations\HasMany {$relation->method()}\n";
                }

                if ($relation->isManyToMany()) {
                    $relationsFunctions .= $this->manyToManyFunction($relation, $relation->getPivotTableName());
                    $properties .= "* @property  \Illuminate\Support\Collection<{$relation->modelName}>|\Illuminate\Database\Eloquent\Collection<{$relation->modelName}>|{$relation->modelName}[] {$relation->method()}\n";
                    $properties .= "* @method  \Illuminate\Database\Eloquent\Relations\BelongsToMany {$relation->method()}\n";
                }
            }

            if ($relation->isBelongsTo()) {
                $properties .= "* @property {$relation->modelName} {$relation->method()}\n";
                $properties .= "* @method  \Illuminate\Database\Eloquent\Relations\BelongsTo {$relation->method()}\n";

                if ($relatedTable && $relation->exists()) {
                    $relationName = $relation->relationMethodNaming();
                    $col = $relatedTable->titleable()->name;
                    $exportedCol = "$relationName.$col";
                } else {
                    $exportedCol = $relation->key;
                }

                $exportables .= "'$exportedCol' , \n";
            }

            if ($relation->getModelPath()->exist()) {
                if ($relatedTable) {
                    $relationsSearchable .=
                        "'{$relation->method()}' => [\n{$relatedTable->searchableColsAsString()}\n//add your {$relation->method()} desired column to be search within\n] , \n";
                } else {
                    $relationsSearchable .=
                        "'{$relation->method()}' => [\n//add your {$relation->method()} desired column to be search within\n] , \n";
                }
            }
        });

        $properties .= "*/ \n";

        return [
            'fillable'           => $fillable,
            'filesKeys'          => $filesKeys,
            'searchable'         => $searchable,
            'relationSearchable' => $relationsSearchable,
            'properties'         => $properties,
            'filesFunctions'     => $fileFunctions,
            'booleanValueScopes' => $booleanValueScope,
            'casts'              => $casts,
            'relationsCode'      => $relationsFunctions,
            'exportables'        => $exportables,
        ];
    }

    protected function stubsPath(): string
    {
        return CubePath::stubPath('model.stub');
    }
}
