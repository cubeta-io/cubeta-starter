<?php

namespace Cubeta\CubetaStarter\Helpers;

use Closure;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeRelation;
use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 * @template-covariant TValue
 * @extends Collection<TKey , TValue>
 */
class CubeCollection extends Collection
{
    /**
     * @param Closure(TValue):string|null $callable
     * @return CubeCollection
     */
    public function stringifyEachOne(Closure $callable = null): CubeCollection
    {
        return $this->map(function ($attribute) use ($callable) {
            if ($callable) {
                return "'{$callable($attribute)}'";
            }
            if ($attribute instanceof CubeRelation | $attribute instanceof CubeAttribute) {
                return "'{$attribute->usedString}'";
            } else {
                return "'$attribute'";
            }
        });
    }
}