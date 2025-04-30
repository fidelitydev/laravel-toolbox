<?php

namespace Knighttower\Toolbox\Facades;

use Illuminate\Support\Facades\Facade;
use Knighttower\Toolbox\Helpers\LocalApiPort as LocalApi;

/**
* @see \Knighttower\Toolbox\Helpers\RequestHelper
*/
class LocalApiPort extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return new LocalApi();
    }
}
