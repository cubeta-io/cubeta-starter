<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Illuminate\Support\Str;

class ResourceGenerator extends AbstractGenerator
{
    public static string $key = 'resource';

    public function run(bool $override = false): void
    {
        $resourceName = $this->table->getResourceName();

        $resourcePath = $this->table->getResourcePath();

        if ($resourcePath->exist()) {
            $resourcePath->logAlreadyExist("Generating Resource For {$this->table->modelName} Model");
            return;
        }

        $resourcePath->ensureDirectoryExists();

        $stubProperties = [
            '{model}'           => $this->table->getModelClassString(),
            '{namespace}'       => $this->table->getResourceNameSpace(false, true),
            '{class}'           => $resourceName,
            '{resource_fields}' => $this->generateFields(),
        ];

        $this->generateFileFromStub($stubProperties, $resourcePath->fullPath);

        $resourcePath->format();

        CodeSniffer::make()->setModel($this->table)->checkForResourceRelations();
    }

    private function generateFields(): string
    {
        $fields = "'id' => \$this->id, \n\t\t\t";
        $this->table->attributes()->each(function (CubeAttribute $attribute) use (&$fields) {
            $key = Str::snake($attribute->name);
            $fields .= "'{$key}' => \$this->{$attribute->name},\n\t\t\t";
        });

        foreach ($this->table->relations() as $rel) {

            if (!$rel->getModelPath()->exist() or !$rel->getResourcePath()->exist()) {
                continue;
            }

            if ($rel->isHasOne() || $rel->isBelongsTo()) {
                $relation = $rel->method();
                $key = Str::snake($relation);
                $relatedModelResource = $rel->getResourceName();

                // check that the resource model has the relation method
                if (!method_exists($this->table->getModelClassString(), $relation)) {
                    continue;
                }

                $fields .= "'{$key}' =>  new {$relatedModelResource}(\$this->whenLoaded('{$relation}')) , \n\t\t\t";

            } elseif ($rel->isManyToMany() || $rel->isHasMany()) {
                $relation = $rel->method();
                $key = Str::snake($relation);
                $relatedModelResource = $rel->getResourceName();

                // check that the resource model has the relation method
                if (!method_exists($this->table->getModelClassString(), $relation)) {
                    continue;
                }

                $fields .= "'{$key}' =>  {$relatedModelResource}::collection(\$this->whenLoaded('{$relation}')) , \n\t\t\t";
            }
        }

        return $fields;
    }

    protected function stubsPath(): string
    {
        return CubePath::stubPath('resource.stub');
    }
}
