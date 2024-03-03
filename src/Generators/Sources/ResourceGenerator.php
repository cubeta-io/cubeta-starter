<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\app\Models\CubetaAttribute;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Throwable;

class ResourceGenerator extends AbstractGenerator
{
    public static string $key = 'resource';
    public static string $configPath = 'cubeta-starter.resource_path';

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $resourceName = $this->table->getResourceName();
        $this->addToJsonFile();

        $stubProperties = [
            '{model}' => "\\" . $this->table->getModelClassString(),
            '{namespace}' => config('cubeta-starter.resource_namespace'),
            '{class}' => $resourceName,
            '{resource_fields}' => $this->generateFields(),
        ];

        $resourcePath = $this->table->getResourcePath();

        if ($resourcePath->exist()) {
            $resourcePath->logAlreadyExist("Generating Resource For {$this->table->modelName} Model");
        }

        $this->generateFileFromStub($stubProperties, $resourcePath->fullPath);

        CodeSniffer::make()->setModel($this->table->modelName)->checkForResourceRelations();

        $resourcePath->format();
    }

    private function generateFields(): string
    {
        $fields = "'id' => \$this->id, \n\t\t\t";
        $this->table->attributes()->each(function (CubetaAttribute $attribute) use (&$fields) {
            if ($attribute->type == ColumnTypeEnum::FILE->value) {
                $fields .= "'{$attribute->name}' => \$this->get" . $attribute->modelNaming() . "Path(), \n\t\t\t";
            } else {
                $fields .= "'{$attribute->name}' => \$this->{$attribute->name},\n\t\t\t";
            }
        });

        foreach ($this->table->relations() as $rel) {

            if (!$rel->getModelPath()->exist() or !$rel->getResourcePath()->exist()) {
                continue;
            }

            if ($rel->isHasOne() || $rel->isBelongsTo()) {
                $relation = $rel->method();
                $relatedModelResource = $rel->getResourceName();

                // check that the resource model has the relation method
                if (!method_exists($this->table->getModelClassString(), $relation)) {
                    continue;
                }

                $fields .= "'{$relation}' =>  new {$relatedModelResource}(\$this->whenLoaded('{$relation}')) , \n\t\t\t";

            } elseif ($rel->isManyToMany() || $rel->isHasMany()) {
                $relation = $rel->method();
                $relatedModelResource = $rel->getResourceName();

                // check that the resource model has the relation method
                if (!method_exists($this->table->getModelClassString(), $relation)) {
                    continue;
                }

                $fields .= "'{$relation}' =>  {$relatedModelResource}::collection(\$this->whenLoaded('{$relation}')) , \n\t\t\t";
            }
        }

        return $fields;
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/resource.stub';
    }
}
