<?php

namespace Cubeta\CubetaStarter;

use Illuminate\Support\Facades\Facade;

/**
 * @see CubetaRepository
 */
class CubetaRepositoryFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cubeta-repository';
    }
}
