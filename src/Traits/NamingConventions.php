<?php

namespace Cubeta\CubetaStarter\Traits;

use Carbon\Carbon;
use Cubeta\CubetaStarter\app\Models\CubetaAttribute;
use Cubeta\CubetaStarter\app\Models\CubetaRelation;
use Cubeta\CubetaStarter\app\Models\CubetaTable;
use Illuminate\Support\Str;

/**
 * @mixin CubetaTable|CubetaRelation|CubetaAttribute
 */
trait NamingConventions
{
    /**
     * @var string
     */
    public string $usedString;

    /**
     * @param string $name
     * @return string
     */
    public static function getModelName(string $name): string
    {
        return ucfirst(Str::singular(Str::studly($name)));
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getTableName(string $name): string
    {
        return strtolower(Str::plural(Str::snake($name)));
    }

    /**
     * @param string $name
     * @return string
     */
    public static function columnNaming(string $name): string
    {
        return strtolower(Str::snake($name));
    }

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
        if ($name) {
            return Str::singular(Str::camel($name));
        }
        return Str::singular(Str::camel($this->usedString));
    }

    /**
     * return the lower case and the plural in kebab case of the input string
     * or for the model name if a string isn't provided
     * @param string|null $name
     * @return string
     */
    public function lowerPluralKebabNaming(?string $name = null): string
    {
        if ($name) {
            return strtolower(Str::plural(Str::kebab($name)));
        }
        return strtolower(Str::plural(Str::kebab($this->usedString)));
    }

    /**
     * return the name based on name convention for routes for a given string if provided or to the model name if not provided
     */
    public function routeUrlNaming(?string $name = null): string
    {
        if ($name) {
            return lowerPluralKebabNaming($name);
        }
        return lowerPluralKebabNaming($this->usedString);
    }

    /**
     * return the used name of the model for the route name for a given string if provided or to the model name if not provided
     */
    public function routeNameNaming(?string $name = null): string
    {
        if ($name) {
            return str_replace('-', '.', lowerPluralKebabNaming($name));
        }
        return str_replace('-', '.', lowerPluralKebabNaming($this->usedString));
    }

    /**
     * return the name based on name convention for relation functions in the models for a given string if provided or to the model name if not provided
     * @param string|null $name
     * @param bool $singular
     * @return string
     */
    public function relationFunctionNaming(?string $name = null, bool $singular = true): string
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
        if ($name) {
            return lowerPluralKebabNaming($name);
        }
        return lowerPluralKebabNaming($this->usedString);
    }

    /**
     * @param string|null $name
     * @return string
     */
    public function titleNaming(?string $name = null): string
    {
        if ($name) {
            return Str::headline($name);
        }
        return Str::headline($this->usedString);
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
        if ($name) {
            return ucfirst(Str::singular(Str::studly($name)));
        }
        return ucfirst(Str::singular(Str::studly($this->usedString)));
    }

    /**
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->modelNaming() . "Controller";
    }

    /**
     * @return string
     */
    public function getMigrationName(): string
    {
        $date = Carbon::now()->subSecond()->format('Y_m_d_His');
        return "{$date}_create_" . $this->tableNaming() . "_table";
    }

    /**
     * return the name based on name convention for tables
     */
    public function tableNaming(?string $name = null): string
    {
        if ($name) {
            return strtolower(Str::plural(Str::snake($name)));
        }
        return strtolower(Str::plural(Str::snake($this->usedString)));
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
    public function getServiceInterfaceName(): string
    {
        return "I" . $this->getServiceName();
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
}
