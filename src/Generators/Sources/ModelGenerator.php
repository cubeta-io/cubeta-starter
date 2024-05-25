<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
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
            '{namespace}' => config('cubeta-starter.model_namespace'),
            '{modelName}' => $this->table->modelName,
            '{relations}' => $modelAttributes['relationsCode'],
            '{properties}' => $modelAttributes['properties'],
            '{fileGetter}' => $modelAttributes['filesFunctions'],
            '{fillable}' => $modelAttributes['fillable'],
            '{filesKeys}' => $modelAttributes['filesKeys'],
            '{scopes}' => $modelAttributes['booleanValueScopes'],
            '{searchableKeys}' => $modelAttributes['searchable'],
            '{searchableRelations}' => $modelAttributes['relationSearchable'],
            '{casts}' => $modelAttributes['casts'],
            '{exportables}' => $modelAttributes['exportables']
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

        $this->table->attributes()->each(function ($attribute) use (
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
                $properties .= "* @property integer {$attribute->name} \n";

                if ($relatedModel->getModelPath()->exist()) {
                    $relationsFunctions .= $this->belongsToFunction($relatedModel);
                }
            } elseif ($attribute->isString()) {
                $searchable .= "'{$attribute->name}' , \n";
                $properties .= "* @property string {$attribute->name} \n";

            } elseif ($attribute->isFile()) {
                $filesKeys .= "'{$attribute->name}' ,\n";
                $properties .= "* @property string {$attribute->name} \n";
                $colName = $attribute->modelNaming();
                $fileFunctions .= '/**
                              * return the full path of the stored ' . $colName . '
                              * @return string|null
                              */
                              public function get' . $colName . "Path() : ?string
                              {
                                  return \$this->{$colName} != null ? asset('storage/'.\$this->{$colName}) : null;
                              }\n";
                FileUtils::ensureDirectoryExists(storage_path('app/public/' . Str::lower($this->table->modelName) . '/' . Str::plural($colName)));

            } elseif (ColumnTypeEnum::isDateTimeType($attribute->type)) {
                $properties .= "* @property string {$attribute->name} \n";
            } elseif (ColumnTypeEnum::isNumericType($attribute->type)) {
                $properties .= "* @property numeric {$attribute->name} \n";
            } else {
                $properties .= "* @property {$attribute->type} {$attribute->name} \n";
            }
        });

        $this->table->relations()->each(function (CubeRelation $relation) use (&$relationsFunctions, &$relationsSearchable, &$exportables, &$properties) {

            $relatedTable = $relation->getTable();

            if ($relation->getModelPath()->exist()) {
                if ($relation->isHasMany()) {
                    $relationsFunctions .= $this->hasManyFunction($relation);
                }

                if ($relation->isManyToMany()) {
                    $relationsFunctions .= $this->manyToManyFunction($relation);
                }
            }

            if ($relation->isBelongsTo()) {
                $properties .= "* @property {$relation->modelName} {$relation->method()}\n";

                if ($relatedTable && $relation->exists()) {
                    $relationName = $relation->relationMethodNaming();
                    $col = $relatedTable->titleable()->name;
                    $exportedCol = "$relationName.$col";
                } else {
                    $exportedCol = $relation->key;
                }

                $exportables .= "'$exportedCol' , \n";
            }

            if ($relation->loadable()) {
                if ($relatedTable) {
                    $relationsSearchable .=
                        "'{$relation->method()}' => [\n{{$relatedTable->searchableColsAsString()}}\n//add your {$relation->method()} desired column to be search within\n] , \n";
                } else {
                    $relationsSearchable .=
                        "'{$relation->method()}' => [\n//add your {$relation->method()} desired column to be search within\n] , \n";
                }
            }
        });

        $properties .= "*/ \n";

        return [
            'fillable' => $fillable,
            'filesKeys' => $filesKeys,
            'searchable' => $searchable,
            'relationSearchable' => $relationsSearchable,
            'properties' => $properties,
            'filesFunctions' => $fileFunctions,
            'booleanValueScopes' => $booleanValueScope,
            'casts' => $casts,
            'relationsCode' => $relationsFunctions,
            'exportables' => $exportables
        ];
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../stubs/model.stub';
    }
}
