<?php

use Knighttower\Toolbox\Helpers\DateHelper;
use Knighttower\Toolbox\Helpers\UrlHelper;
use React\EventLoop\Loop;
use React\Promise\Promise;
use Knighttower\Toolbox\Helpers\PageHelper;
use Knighttower\Toolbox\Helpers\MixAsset;

if (!function_exists('emptyOrValue')) {
    /**
     * Global function to handle if comparison of empty values
     * Pass this wrapped like: emptyOrValue(($value ?? null), $default),
     * so that it wont fail on unset props when working with objects or arrays
     *
     * @param mixed $value
     * @param mixed $default Value that should be used in case of Empty
     * @return mixed Returns the Original value, default or just null.
     */
    function emptyOrValue($value, $default = null)
    {
        return !empty($value) ? $value : $default;
    }
}

if (!function_exists('dateHelper')) {
    /**
     * Global function to expose the dateHelper static class and its methods
     *
     * @see \Knighttower\Toolbox\Helpers\DateHelper
     * @return mixed
     */
    function dateHelper()
    {
        return app(DateHelper::class);
    }
}

if (!function_exists('host')) {
    /**
     * Global function to get the current host url
     *
     * @see \Knighttower\Toolbox\Helpers\UrlHelper
     * @return mixed
     */
    function host()
    {
        return UrlHelper::host();
    }
}

if (!function_exists('makeUrl')) {
    /**
     * Global function to get compose a fully qualified URL
     *
     * @see \Knighttower\Toolbox\Helpers\UrlHelper
     * @param string $path
     * @return mixed
     */
    function makeUrl(string $path)
    {
        return UrlHelper::makeUrl($path);
    }
}


if (!function_exists('async')) {

    /**
     * Global function async
     *
     * @param callable|Closure $callback
     * @return Promise
     * @usage async (function () {}))
     * @url https://reactphp.org/promise/
     */
    function async($callback)
    {
        // $callback = new \Laravel\SerializableClosure\SerializableClosure($callback);
        return new \React\Promise\Promise(function ($resolve, $reject) use ($callback) {
            Loop::futureTick(function () use ($callback, $resolve, $reject) {
                try {
                    $resolve($callback());
                } catch (\Throwable $th) {
                    $reject($th);
                }
            });
        });
    }
}

if (!function_exists('proxy')) {
    /**
     * Global function to expose the proxy static class and its methods
     *
     * @see \Knighttower\Toolbox\Helpers\ProxyHelper
     * @return mixed
     */
    function proxy()
    {
        return app('ProxyHelper');
    }
}

if (!function_exists('user')) {
    /**
     * Global function to expose the current authenticated user
     *
     * @return mixed
     */
    function user()
    {
        if (!empty(auth()->user())) {
            return auth()->user();
        };
    }
}

if (!function_exists('pageHelper')) {
    /**
     * Assits in paginating a collection
     *
     * @param Illuminate\Support\Collection $model
     * @return PageHelper
     */
    function pageHelper(object|array $model)
    {
        return new PageHelper($model);
    }
}


if (!function_exists('isSecuredRequest')) {
    /**
     * Check if the request is secure
     *
     * @return bool
     */
    function isSecuredRequest()
    {
        if (
            request()->getScheme() === 'https' ||
            request()->server('HTTPS') === 'on' ||
            request()->server('HTTP_X_FORWARDED_PROTO') === 'https'
        ) {
            return true;
        }
        return false;
    }
}

if (!function_exists('mixAsset')) {
    /**
     * Global function to expose the mixAssets static class and its methods
     *
     * @see \Knighttower\Toolbox\Helpers\MixAssets
     * @return mixed
     */
    function mixAsset(string|null $manifestPath)
    {
        return new MixAsset($manifestPath);
    }
}
