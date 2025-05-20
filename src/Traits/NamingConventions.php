<?php

namespace Cubeta\CubetaStarter\Traits;

use Carbon\Carbon;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeRelation;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/**
 * Provides naming conventions for various components such as variables, routes, models, tables, etc.
 * @mixin CubeRelation
 * @mixin CubeAttribute
 */
trait NamingConventions
{
    /**
     * @var string
     */
    public string $usedString;

    /**
     * @param string|null $name
     * @return string
     */
    public function idVariable(?string $name = null): string
    {
        return $name
            ? $this->variableNaming($name) . "Id"
            : $this->variableNaming() . "Id";
    }

    /**
     * return the variable name from the model name
     * if string provided the result will be base on the given string else on the modelName property of the class
     */
    public function variableNaming(?string $name = null): string
    {
        return $name
            ? Str::singular(Str::camel($name))
            : Str::singular(Str::camel($this->usedString));
    }

    /**
     * return the lower case and the plural in kebab case of the input string
     * or for the model name if a string isn't provided
     * @param string|null $name
     * @return string
     */
    public function lowerPluralKebabNaming(?string $name = null): string
    {
        return $name
            ? strtolower(Str::plural(Str::kebab($name)))
            : strtolower(Str::plural(Str::kebab($this->usedString)));
    }

    /**
     * return the name based on name convention for routes for a given string if provided or to the model name if not
     * provided
     */
    public function routeUrlNaming(?string $name = null): string
    {
        return $name
            ? $this->lowerPluralKebabNaming($name)
            : $this->lowerPluralKebabNaming($this->usedString);
    }

    /**
     * return the used name of the model for the route name for a given string if provided or to the model name if not
     * provided
     */
    public function routeNameNaming(?string $name = null): string
    {
        return $name
            ? str_replace('-', '.', $this->lowerPluralKebabNaming($name))
            : str_replace('-', '.', $this->lowerPluralKebabNaming($this->usedString));
    }

    /**
     * return the name based on name convention for relation functions in the models for a given string if provided or
     * to the model name if not provided
     * @param string|null $name
     * @param bool        $singular
     * @return string
     */
    public function relationMethodNaming(?string $name = null, bool $singular = true): string
    {
        if (!$name) {
            $name = $this->usedString;
        }

        if ($singular) {
            return Str::camel(lcfirst(Str::singular(Str::studly($name))));
        }
        return Str::camel(lcfirst(Str::plural(Str::studly($name))));
    }

    /**
     * @param string|null $name
     * @return string
     */
    public function viewNaming(?string $name = null): string
    {
        return $name
            ? $this->lowerPluralKebabNaming($name)
            : $this->lowerPluralKebabNaming($this->usedString);
    }

    /**
     * @param string|null $name
     * @return string
     */
    public function titleNaming(?string $name = null): string
    {

        return $name
            ? Str::headline($name)
            : Str::headline($this->usedString);
    }

    /**
     * @return string
     */
    public function getResourceName(): string
    {
        return $this->modelNaming() . "Resource";
    }

    /**
     * return the name based on name convention for models
     */
    public function modelNaming(?string $name = null): string
    {
        return Naming::model($name ?? $this->usedString);
    }

    /**
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->modelNaming() . "Controller";
    }

    /**
     * @param bool $withoutDate
     * @return string
     */
    public function getMigrationName(bool $withoutDate = false): string
    {
        if ($withoutDate) {
            return "create_" . $this->tableNaming() . "_table";
        }
        $date = Carbon::now()->subSecond()->format('Y_m_d_His');
        return "{$date}_create_" . $this->tableNaming() . "_table";
    }

    /**
     * return the name based on name convention for tables
     */
    public function tableNaming(?string $name = null): string
    {
        return Naming::table($name ?? $this->usedString);
    }

    /**
     * @return string
     */
    public function getRequestName(): string
    {
        return 'StoreUpdate' . $this->modelNaming() . 'Request';
    }

    /**
     * @return string
     */
    public function getFactoryName(): string
    {
        return $this->modelNaming() . "Factory";
    }

    /**
     * @return string
     */
    public function getRepositoryName(): string
    {
        return $this->modelNaming() . "Repository";
    }

    /**
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->modelNaming() . "Service";
    }

    /**
     * @return string
     */
    public function getSeederName(): string
    {
        return $this->modelNaming() . "Seeder";
    }

    /**
     * @return string
     */
    public function getTestName(): string
    {
        return $this->modelNaming() . "Test";
    }

    /**
     * @return string
     */
    public function keyName(): string
    {
        return strtolower(Str::singular($this->usedString)) . "_id";
    }

    public function columnNaming(string $name = null): string
    {
        return Naming::column($name ?? $this->usedString);
    }

    /**
     * @return array{true:string , false:string}
     */
    public function booleanLabels(): array
    {
        if (Str::contains($this->name, 'is_')) {
            $base = Str::replace('is_', '', $this->name);
            return [
                'true' => Str::title($base),
                'false' => 'Not' . Str::title($base),
            ];
        } else return [
            'true' => Str::title($this->name),
            'false' => 'Not' . Str::title($this->name),
        ];
    }

    public function routeParameterNaming(): Stringable
    {
        return str($this->usedString)->snake()->singular()->lower();
    }
}
