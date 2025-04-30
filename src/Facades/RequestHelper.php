<?php

namespace Knighttower\Toolbox\Facades;

use Illuminate\Support\Facades\Facade;
use Knighttower\Toolbox\Helpers\RequestHelper as Helper;

/**
* @see \Knighttower\Toolbox\Helpers\RequestHelper
*/
class RequestHelper extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return new Helper();
    }
}
