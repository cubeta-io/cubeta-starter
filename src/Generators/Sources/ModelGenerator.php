<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Error;
use Illuminate\Support\Str;
use Throwable;

class ModelGenerator extends AbstractGenerator
{
    public static string $key = 'model';

    use StringsGenerator;

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $modelName = $this->generatedFileName();
        $this->mergeRelations();
        $modelPath = $this->getGeneratingPath($modelName);
        $this->addToJsonFile();

        throw_if(file_exists($modelPath), new Error("{$modelName} Already Exists"));

        $modelAttributes = $this->generateModelClassAttributes();

        $traits = $this->generateUsedTraits();

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.model_namespace'),
            '{modelName}' => $modelName,
            '{relations}' => $modelAttributes['relationsCode'],
            '{properties}' => $modelAttributes['properties'],
            '{fileGetter}' => $modelAttributes['filesFunctions'],
            '{fillable}' => $modelAttributes['fillable'],
            '{filesKeys}' => $modelAttributes['filesKeys'],
            '{scopes}' => $modelAttributes['booleanValueScopes'],
            '{searchableKeys}' => $modelAttributes['searchable'],
            '{searchableRelations}' => $modelAttributes['relationSearchable'],
            'use HasFactory;' => $traits,
            '{casts}' => $modelAttributes['casts'],
        ];

        $this->generateFileFromStub($stubProperties, $modelPath);

        CodeSniffer::make()->setModel($modelName)->checkForModelsRelations();

        $this->formatFile($modelPath);
    }

    public function generatedFileName(): string
    {
        return $this->modelName($this->fileName);
    }

    public function mergeRelations(): void
    {
        $belongToRelations = [];
        foreach ($this->attributes as $attribute => $type) {
            if ($type === ColumnTypeEnum::KEY->value) {
                $belongToRelations["$attribute"] = RelationsTypeEnum::BelongsTo->value;
            }
        }
        $this->relations = array_merge($this->relations, $belongToRelations);
    }

    private function generateModelClassAttributes(): array
    {
        $fillable = '';
        $filesKeys = '';
        $searchable = '';
        $relationsSearchable = '';
        $properties = "/**  \n";
        $fileFunctions = '';
        $booleanValueScope = '';
        $casts = '';
        $relationsFunctions = '';

        if (isset($this->attributes) && count($this->attributes) > 0) {
            foreach ($this->attributes as $attribute => $type) {
                $attribute = $this->columnName($attribute);
                $fillable .= "'{$attribute}' ,\n";

                if ($type === ColumnTypeEnum::BOOLEAN->value) {
                    $booleanValueScope .= '\tpublic function scope' . ucfirst(Str::studly($attribute)) . "(\$query)\t\n{\n\t\treturn \$query->where('" . $attribute . "' , 1);\n\t}\n";
                    $casts .= "'{$attribute}' => 'boolean' , \n";
                }

                if ($type === ColumnTypeEnum::TRANSLATABLE->value) {
                    $casts .= "'{$attribute}' => \\App\\Casts\\Translatable::class, \n";
                }

                if ($type === ColumnTypeEnum::KEY->value) {
                    $relatedModelName = $this->modelName(str_replace('_id', '', $attribute));

                    if (file_exists(getModelPath($relatedModelName))) {
                        $relationsFunctions .= $this->belongsToFunction($relatedModelName);
                    }
                }

                if (in_array($type, [
                    ColumnTypeEnum::STRING->value,
                    ColumnTypeEnum::TEXT->value,
                    ColumnTypeEnum::JSON->value,
                    ColumnTypeEnum::TRANSLATABLE->value
                ])) {

                    $searchable .= "'{$attribute}' , \n";
                    $properties .= "* @property string {$attribute} \n";

                } elseif ($type === ColumnTypeEnum::FILE->value) {
                    $filesKeys .= "'{$attribute}' ,\n";
                    $properties .= "* @property integer {$attribute} \n";
                    $colName = $this->modelName($attribute);
                    $fileFunctions .= '/**
                              * return the full path of the stored ' . $colName . '
                              * @return string|null
                              */
                              public function get' . $colName . "Path() : ?string
                              {
                                  return \$this->{$colName} != null ? asset('storage/'.\$this->{$colName}) : null;
                              }\n";
                    $this->ensureDirectoryExists(storage_path('app/public/' . Str::lower($this->generatedFileName()) . '/' . Str::plural($colName)));

                } elseif (ColumnTypeEnum::isDateTimeType($type)) {
                    $properties .= "* @property \DateTime {$attribute} \n";
                } elseif (in_array($type, [
                    ColumnTypeEnum::BIG_INTEGER->value,
                    ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
                    ColumnTypeEnum::UNSIGNED_DOUBLE->value,
                ])) {
                    $properties .= "* @property numeric {$attribute} \n";
                } else {
                    $properties .= "* @property {$type} {$attribute} \n";
                }
            }
        }

        if (isset($this->relations) && count($this->relations) > 0) {
            foreach ($this->relations as $relation => $type) {

                if (file_exists(getModelPath($this->modelName($relation))) && $type == RelationsTypeEnum::HasMany->value) {
                    $relationsFunctions .= $this->hasManyFunction($relation);
                }

                if (file_exists(getModelPath($this->modelName($relation))) && $type == RelationsTypeEnum::ManyToMany->value) {
                    $relationsFunctions .= $this->manyToManyFunction($relation);
                }

                if ($type !== RelationsTypeEnum::BelongsTo->value) {
                    $modelName = $this->modelName($relation);
                    $properties .= "* @property {$modelName} {$relation}\n";
                }

                $relationsSearchable .=
                    "'{$relation}' => [
                    //add your {$relation} desired column to be search within
                  ] , \n";
            }
        }

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
        ];
    }

    private function generateUsedTraits(): string
    {
        //TODO::when merging with dev there will be a config option for traits so make the use traits use the config option for traits namespace
        return in_array('translatable', $this->attributes) ? "use HasFactory; \n use \App\Traits\Translations;\n" : "use HasFactory;";
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/model.stub';
    }
}
