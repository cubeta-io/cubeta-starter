<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

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

        $traits = $this->generateUsedTraits();

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
            'use HasFactory;' => $traits,
            '{casts}' => $modelAttributes['casts'],
        ];

        $this->generateFileFromStub($stubProperties, $modelPath->fullPath);

        $modelPath->format();

        CodeSniffer::make()->setModel($this->table)->checkForModelsRelations();
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

        foreach ($this->table->attributes as $attribute) {
            $fillable .= "'{$attribute->name}' ,\n";

            if ($attribute->type === ColumnTypeEnum::BOOLEAN->value) {
                $booleanValueScope .= "\tpublic function scope" . ucfirst(Str::studly($attribute->name)) . "(\$query)\t\n{\n\t\treturn \$query->where('" . $attribute->name . "' , 1);\n\t}\n";
                $casts .= "'{$attribute->name}' => 'boolean' , \n";
            }

            if ($attribute->isTranslatable()) {
                $casts .= "'{$attribute->name}' => \\App\\Casts\\Translatable::class, \n";
            }

            if ($attribute->isKey()) {
                $relatedModelName = $attribute->modelNaming(str_replace('_id', '', $attribute->name));
                $relatedModel = CubeTable::create($relatedModelName);

                if ($relatedModel->getModelPath()->exist()) {
                    $relationsFunctions .= $this->belongsToFunction($relatedModel);
                }
            }

            if (in_array($attribute->type, [
                ColumnTypeEnum::STRING->value,
                ColumnTypeEnum::TEXT->value,
                ColumnTypeEnum::JSON->value,
                ColumnTypeEnum::TRANSLATABLE->value
            ])) {

                $searchable .= "'{$attribute->name}' , \n";
                $properties .= "* @property string {$attribute->name} \n";

            } elseif ($attribute->isFile()) {
                $filesKeys .= "'{$attribute->name}' ,\n";
                $properties .= "* @property integer {$attribute->name} \n";
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
                $properties .= "* @property \DateTime {$attribute->name} \n";
            } elseif (in_array($attribute->type, [
                ColumnTypeEnum::BIG_INTEGER->value,
                ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
                ColumnTypeEnum::UNSIGNED_DOUBLE->value,
            ])) {
                $properties .= "* @property numeric {$attribute->name} \n";
            } else {
                $properties .= "* @property {$attribute->type} {$attribute->name} \n";
            }
        }

        foreach ($this->table->relations as $relation) {

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
            }

            $relationsSearchable .=
                "'{$relation->method()}' => [
                    //add your {$relation->method()} desired column to be search within
                  ] , \n";
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
        return $this->table->translatables()->count()
            ? "use HasFactory; \n use \App\Traits\Translations;\n"
            : "use HasFactory;";
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../stubs/model.stub';
    }
}
