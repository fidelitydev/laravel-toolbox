<?php

namespace Knighttower\Toolbox\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class UrlHelper extends URL
{
    /**
     * Generate the base url.
     *
     * @return string
     */
    public static function host(): string
    {
        return self::to('/');
    }

    /**
     * Generate base url plus provided location to.
     *
     * @param string $to
     * @return string
     */
    public static function makeUrl(string $to): string
    {
        return self::host() . $to;
    }

    /**
     * Create a temporary signed url.
     *
     * @param string $route
     * @param Carbon $expiration
     * @param array  $parameters
     * @return string
     */
    public static function expiryUrl(string $route, Carbon $expiration, array $parameters): string
    {
        return URL::temporarySignedRoute($route, $expiration, $parameters);
    }
}
