<?php

namespace Cubeta\CubetaStarter\Helpers;

use Closure;
use Illuminate\Support\Collection;
use Cubeta\CubetaStarter\Settings\CubeRelation;
use Cubeta\CubetaStarter\Settings\CubeAttribute;

/**
 * @template TKey of array-key
 *
 * @template-covariant TValue
 *
 * @extends Collection<TKey , TValue>
 */
class CubeCollection extends Collection
{
    /**
     * @param Closure(TValue):string|null $callable
     */
    public function stringifyEachOne(?Closure $callable = null): CubeCollection
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
