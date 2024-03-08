<?php

namespace Cubeta\CubetaStarter\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Generators\Sources\ViewsGenerator;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\CubeWarning;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Warnings\ContentNotFound;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Illuminate\Support\Str;

class CodeSniffer
{
    use StringsGenerator;

    private static $instance;

    private ?CubeTable $table = null;

    private function __construct()
    {
        //
    }

    public static function destroy(): void
    {
        self::$instance = null;
    }

    public function setModel(CubeTable $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function checkForModelsRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubeRelation $relation) {
            $relatedPath = $relation->getModelPath();

            if (!$relatedPath->exist()) {
                return true;
            }

            if ($relation->isHasMany()) {
                ClassUtils::addMethodToClass(
                    $this->table->relationFunctionNaming(),
                    $relatedPath,
                    $this->belongsToFunction($this->table)
                );
            }

            if ($relation->isManyToMany()) {
                ClassUtils::addMethodToClass(
                    $this->table->relationFunctionNaming(singular: false),
                    $relatedPath,
                    $this->manyToManyFunction($this->table)
                );
            }

            if ($relation->isBelongsTo() || $relation->isHasOne()) {
                ClassUtils::addMethodToClass(
                    $this->table->relationFunctionNaming(singular: false),
                    $relatedPath,
                    $this->hasManyFunction($this->table)
                );
            }
        });

        return $this;
    }

    public function checkForFactoryRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubeRelation $relation) {
            $relatedPath = $relation->getFactoryPath();

            if (!$relation->loadable() or !$relatedPath->exist()) {
                return true;
            }

            if ($relation->isBelongsTo() || $relation->isHasOne() || $relation->isManyToMany()) {
                $methodName = "with" . Str::plural($this->table->modelName);
                ClassUtils::addMethodToClass(
                    $methodName,
                    $relatedPath,
                    $this->factoryRelationMethod($this->table)
                );
            }
        });

        return $this;
    }

    public function checkForResourceRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubeRelation $relation) {
            $relatedClassName = $relation->getResourceClassString();
            $relatedResourcePath = $relation->getResourcePath();
            $currentResourceClass = $this->table->getResourceClassString();
            $relatedModelPath = $relation->getModelPath();

            if (!$relatedResourcePath->exist() or !$relation->loadable()) {
                return true;
            }

            if ($relation->isHasMany()) {
                $relationName = $this->table->relationFunctionNaming();

                if ($relatedModelPath->exist() and ClassUtils::isMethodDefined($relatedModelPath, $relationName)) {
                    $content = "'$relationName' => new $currentResourceClass(\$this->whenLoaded('$relationName')) , \n";
                    ClassUtils::addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                } else {
                    CubeLog::add(new ContentNotFound(
                        "$relationName method",
                        $relatedModelPath->fullPath,
                        "Sniffing The Resource Code To Add The {$this->table->modelName} Resource To {$relation->modelName} Resource"
                    ));
                }
            }

            if ($relation->isManyToMany()) {
                $relationName = $this->table->relationFunctionNaming(singular: false);

                if ($relatedModelPath->exist() and ClassUtils::isMethodDefined($relatedModelPath, $relationName)) {
                    $content = "'$relationName' => $currentResourceClass::collection(\$this->whenLoaded('$relationName')) , \n";
                    ClassUtils::addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                } else {
                    CubeLog::add(new ContentNotFound(
                        "$relationName method",
                        $relatedModelPath->fullPath,
                        "Sniffing The Resource Code To Add The {$this->table->modelName} Resource To {$relation->modelName} Resource"
                    ));
                }
            }

            if ($relation->isBelongsTo() || $relation->isHasOne()) {
                $relationName = $this->table->relationFunctionNaming(singular: false);
                $content = "'$relationName' => $currentResourceClass::collection(\$this->whenLoaded('$relationName')) , \n";

                if ($relatedModelPath->exist() and ClassUtils::isMethodDefined($relatedModelPath, $relationName)) {
                    ClassUtils::addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                } else {
                    CubeLog::add(new ContentNotFound(
                        "$relationName method",
                        $relatedModelPath->fullPath,
                        "Sniffing The Resource Code To Add The {$this->table->modelName} Resource To {$relation->modelName} Resource"
                    ));
                }
            }
        });

        return $this;
    }

    public function checkForWebRelations(string $select2RouteName): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubeRelation $relation) use ($select2RouteName) {
            $relatedControllerPath = $relation->getWebControllerPath();
            $relatedTable = Settings::make()->getTable($relation->modelName);

            if (!$relatedTable) {
                return true;
            }

            $relatedCreateView = $relation->getViewPath("create");
            $relatedUpdateView = $relation->getViewPath("update");
            $relatedIndexView = $relation->getViewPath("index");
            $relatedShowView = $relation->getViewPath("show");

            if ($relation->isHasMany()) {

                if (!$relation->loadable() and !$relatedControllerPath->exist()) {
                    return true;
                }

                $keyName = $this->table->keyName();
                $keyAttribute = $relatedTable->getAttribute($keyName);

                if (!$keyAttribute) {
                    return true;
                }

                if ($relatedCreateView->exist() and ClassUtils::isMethodDefined($relatedControllerPath, 'allPaginatedJson')) {
                    if ($keyAttribute->nullable) {
                        $required = "required";
                    } else {
                        $required = "";
                    }

                    $this->addSelect2ToForm($keyName, $select2RouteName, $required, $relatedCreateView);
                }

                if ($relatedUpdateView->exist() and ClassUtils::isMethodDefined($relatedControllerPath, 'allPaginatedJson')) {
                    $value = ":value=\"\$" . $relation->variableNaming() . "->$keyName\"";
                    $this->addSelect2ToForm($keyName, $select2RouteName, $value, $relatedUpdateView);
                }

                if ($relatedIndexView->exist() and ClassUtils::isMethodDefined($relatedControllerPath, 'data')) {
                    $titleable = $this->table->titleable()->name;
                    $attributeName = $this->table->relationFunctionNaming() . "." . $titleable;
                    $oldColName = strtolower(Str::singular($this->table->modelName)) . "_id";
                    $relatedIndexViewContent = $relatedIndexView->getContent();

                    // checking that if the user remove the key column from the view : like if he removed category_id
                    // if he didn't remove it then we can replace it with the relation column
                    if (str_contains($relatedIndexViewContent, $oldColName)) {
                        $relatedIndexViewContent = str_replace($oldColName, $attributeName, $relatedIndexViewContent);
                        $relatedIndexViewContent = preg_replace('/<th>\s*' . preg_quote($this->table->modelName, '/') . '\s*id\s*<\/th>/', "<th>{$this->table->modelName}</th>", $relatedIndexViewContent);
                        $relatedIndexView->putContent($relatedIndexViewContent);
                    } else { // or add new column to the view
                        $content = "\n\"data\":'$attributeName' , searchable:true , orderable:true";
                        ViewsGenerator::addColumnToDataTable($relatedIndexView, $content, $this->table->modelName);
                    }
                }

                if ($relatedShowView->exist() and ClassUtils::isMethodDefined($relatedControllerPath, "show")) {
                    $relationName = $this->table->relationFunctionNaming();
                    $showViewContent = $relatedShowView->getContent();
                    $value = "\${$relatedTable->variableNaming()}->{$relationName}->{$this->table->titleable()->name}";

                    if (str_contains($showViewContent, "\${$relatedTable->variableNaming()}->{$this->table->keyName()}")) {
                        $showViewContent = str_replace("\${$relatedTable->variableNaming()}->{$this->table->keyName()}", $value, $showViewContent);
                        $oldLabel = ucfirst(str_replace("_", ' ', $this->table->keyName()));
                        $showViewContent = str_replace($oldLabel, $this->table->modelName, $showViewContent);
                    } else {
                        $item = "\t\t<x-small-text-field :value=\"$value\" label=\"{$this->table->modelName}\" />\n\t</x-show-layout>";
                        $showViewContent = str_replace("</x-show-layout>", $item, $showViewContent);
                    }

                    $relatedShowView->putContent($showViewContent);
                }

                ClassUtils::addNewRelationsToWithMethod($relatedTable, $relatedControllerPath, [$this->table->relationFunctionNaming()]);
            }

        });

        return $this;
    }

    public static function make(): CodeSniffer
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $keyName
     * @param string $select2RouteName
     * @param string $tagAttributes
     * @param CubePath $relatedFormView
     * @return void
     */
    function addSelect2ToForm(string $keyName, string $select2RouteName, string $tagAttributes, CubePath $relatedFormView): void
    {
        $inputField = "<x-select2 label=\"{$this->table->modelName}\" name=\"{$keyName}\" api=\"{{route('{$select2RouteName}')}}\" option-value=\"id\" option-inner-text=\"{$this->table->titleable()->name}\" $tagAttributes/> \n";

        $createView = $relatedFormView->getContent();

        $createView = str_replace("</x-form>", "\n \t $inputField\n</x-form>", $createView);

        $relatedFormView->putContent($createView);

        CubeLog::add(new ContentAppended($inputField, $relatedFormView->fullPath));
    }

    public function checkForRequestWebTranslations() : static
    {
        $translatables = $this->table->translatables();
        $prepareForValidation = null;

        if ($translatables->count()) {
            $prepareForValidation = "protected function prepareForValidation()\n{\nif (request()->acceptsHtml()){\$this->merge([\n";
            foreach ($translatables as $tr) {
                $prepareForValidation .= "'{$tr->name}' => json_encode(\$this->{$tr->name}), \n";
            }
            $prepareForValidation .= "]);\n}\n}";
        }

        $requestPath = $this->table->getRequestPath();

        if ($requestPath->exist() && $prepareForValidation) {
            if (
                ClassUtils::isMethodDefined($requestPath, 'prepareForValidation')
                && !FileUtils::contentExistInFile($requestPath, $prepareForValidation)
            ) {
                CubeLog::add(new CubeWarning("You Should Add The Translatables Attributes To The PrepareForValidation Method Using json_encode (this mean that the translatable values should be json encoded before validation)"));
                return $this;
            }
            ClassUtils::addMethodToClass("prepareForValidation", $requestPath, $prepareForValidation);
            return $this;
        }

        return $this;
    }
}
