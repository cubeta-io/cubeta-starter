<?php

namespace Cubeta\CubetaStarter\Helpers;

use Closure;
use Illuminate\Support\Collection;

/**
 * @template T
 * @extends Collection<T>
 */
class CubeCollection extends Collection
{
    /**
     * @param Closure(T):string|null $callable
     * @return CubeCollection
     */
    public function stringifyEachOne(Closure $callable = null): CubeCollection
    {
        return $this->map(function ($attribute) use ($callable) {
            if ($callable) {
                return "'{$callable($attribute)}'";
            }
            return "'{$attribute->usedString}'";
        });
    }
}