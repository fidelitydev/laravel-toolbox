<?php

namespace Knighttower\Toolbox\Helpers;

// Framework
use Illuminate\Support\Facades\Request;

// Package
use Knighttower\Toolbox\Helpers\UrlHelper;
use Knighttower\Toolbox\Support\LocalApiHandle;
use Knighttower\Toolbox\Jobs\LocalApiPortLogger;

class LocalApiPort
{
    /**
    * Setup for outgoing headers
    *
    * @return array
    */
    private function headers()
    {
        // API Token
        $apiKey = emptyOrValue(\Auth::user()->api_token ?? session('_apiToken'));

        // Header array
        return [
            'Authorization' => 'Bearer ' . $apiKey,
            'X-Authorization' => 'Bearer ' . $apiKey,
            'X-CSRF-TOKEN' => csrf_token(),
            'X-Requested-With' => 'XMLHttpRequest',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Referer' => UrlHelper::host(),
        ];
    }

    // --------------------------------------
    // RESTful METHODS
    // -----------------------

    /**
    * POST method to call the API
    *
    * @param string $route Stablished API route
    * @param array $payload Array with call data to trasnmit for Post/Put/Delete
    * @param string|null $event Event that triggers the call
    *
    * @return $this->call
    */
    public function post(string $route, ?array $payload = [], ?string $event = 'Local Call - Post')
    {
        if (empty($payload)) {
            throw new \Exception('$payload is Required');
        }

        return $this->call($route, $event, 'POST', [], (array) $payload);
    }

    /**
    * GET method to call the API
    *
    * @param string $route Stablished API route
    * @param array|null $params Parameters only for Get/Patch
    * @param string|null $event Event that triggers the call
    *
    * @return mixed
    */
    public function get(string $route, ?array $params = [], ?string $event = 'Local Call - Get')
    {
        return $this->call($route, $event, 'GET', (array) $params);
    }

    /**
    * PATCH method to call the API
    *
    * @param string $route Stablished API route
    * @param array|null $params Array with parameters only for Get/Patch
    * @param string|null $event Event that triggers the call
    *
    * @return $this->call
    */
    public function patch(string $route, ?array $params = [], ?string $event = 'Local Call - Patch')
    {
        return $this->call($route, $event, 'PATCH', (array) $params);
    }


    /**
    * DELETE method to call the API
    *
    * @param string $route Stablished API route
    * @param array/null $payload Array with call data to trasnmit for Post/Put/Delete
    * @param string $event Event that triggers the call
    *
    * @return $this->call
    */
    public function delete(string $route, ?array $payload = [], ?string $event = 'Local Call - Delete')
    {
        return $this->call($route, $event, 'DELETE', [], (array) $payload);
    }


    /**
    * CLI way to call routes
    *
    * @param String $controller Controller Full path
    * @param String $method Function/Method name
    * @param array|null $payload Array with call data to trasnmit
    * @param array|null $routeParams Route params to inject back as dependancy injection
    *
    * @return mixed
    */
    public function direct(string $controller, string $method, ?array $payload = [], ?array $routeParams = [])
    {
        $this->logCall('log', 'direct method', ...func_get_args());

        try {
            $payload = emptyOrValue($payload, []);
            $routeParams = emptyOrValue($routeParams, []);
            return $this->handle((object) [
                'controller' => $controller,
                'method' => $method,
                'payload' => $payload,
                'routeParams' => $routeParams
            ]);
        } catch (\Exception $exception) {
            $errorMsg = $controller.'@'.$method. "\r\n";
            $errorMsg .= '------ERROR---LOCAL API ---START----'. "\r\n";
            $errorMsg .= $controller.'@'.$method. "\r\n";
            $errorMsg .= '------payload----'. "\r\n";
            $errorMsg .= (var_export($payload, true)) . "\r\n";
            $errorMsg .= '------route params----'. "\r\n";
            $errorMsg .= (var_export($routeParams, true)) . "\r\n";
            $errorMsg .= '------Request----'. "\r\n";
            $errorMsg .= (var_export(request(), true)) . "\r\n";
            $errorMsg .= '------error----'. "\r\n";
            $errorMsg .= (var_export($exception->getMessage(), true)) . "\r\n";
            $errorMsg .= '------ERROR---LOCAL API ---END------'. "\r\n";

            $this->logCall(
                'error',
                "direct method ($controller)",
                (new \Exception($errorMsg)),
                ...func_get_args()
            );

            return $exception->getMessage();
        }
    }

    // ---------------------------------------
    // PRIVATE EXECUTION METHODS
    // -------------------------------

    /**
    * Handle the execution of the direct call
     *
    * @internal
    * @param object $params
    * @return mixed
    */
    private function handle(object $params)
    {
        $app = app();
        $app->scoped('localApiExecute', function () use ($app) {
            return $app->make(LocalApiHandle::class);
        });
        $results = $app->call('localApiExecute', ['params' => $params], 'execute');
        $app->forgetScopedInstances();

        return $results;
    }


    // phpcs:disable
    /**
    * Main method to call the API
    *
    * @param string $route Stablished API route
    * @param string $event Event that triggers the call
    * @param string $method Call method [GET, PUT, POST, PUT, DELETE]
    * @param array $params Array With parameters only for Get/Patch
    * @param array|null $payload Array with call data to trasnmit for Post/Put/Delete
    *
    * @return mixed
    */
    private function call(
        string $route,
        ?string $event = 'Local Call',
        ?string $method = 'GET',
        ?array $params = [],
        ?array $payload = []
    ) {
        // phpcs:enable
        // Static, web or requests not made via API controllers or requests
        if (!request()->expectsJson()) {
            return $this->routeToDirect(...func_get_args());
        }

        $this->logCall(
            'log',
            'call (via API) method',
            ...func_get_args()
        );

        // else, if it came from an API request:
        try {
            $payload = !empty($payload) ? json_encode($payload) : null;
            // *use: $params for Get/Patch and $payload for Put/Post/Delete
            $request = Request::create($route, $method, $params, $cookies = [], $files = [], $server = [], $payload);
            // Inject the headers into the new request
            $request->headers->add($this->headers());
            // send to API
            $dispatch = app()->handle($request);
            // log the event
            $sentInfo = !empty($payload) ? $payload : $params;
            if ($dispatch->getStatusCode() === 200) {
                $response = $dispatch->getContent();

                $this->logCall(
                    'success',
                    'call (via API) method',
                    $sentInfo,
                    ...func_get_args()
                );

                return is_string($response) ?
                    collect(json_decode($response))->toArray() :
                    $response;
            } else {
                $message = $event .' ⟹ '. var_export($sentInfo, true) . '-->with Code: '. $dispatch->getStatusCode();
                $this->logCall(
                    'error',
                    'call (via API) method',
                    (new \Exception($message)),
                    ...func_get_args()
                );
            }
        } catch (\Exception $exception) {
            $this->logCall('error', 'call (via API) method', $exception, ...func_get_args());
        }

        return false;
    }

    // phpcs:disable
    /**
    * Switch if an API call is not from the api
    *
    * @param string $route Stablished API route
    * @param string $event Event that triggers the call
    * @param string $method Call method [GET, PUT, POST, PUT, DELETE]
    * @param array $params Array with parameters only for Get/Patch
    * @param array/null $payload Array with call data to trasnmit for Post/Put/Delete
    *
    * @return mixed
    */
    private function routeToDirect(
        string $route,
        string $event,
        string $method,
        array $params,
        array $payload = []
    ) {
        // phpcs:enable
        $tempPayload = !empty($payload) ? json_encode($payload) : null;
        $request = Request::create($route, $method, $params, $cookies = [], $files = [], $server = [], $tempPayload);

        $this->logCall(
            'log',
            'routed from API to direct method',
            ...func_get_args()
        );

        try {
            $routeInfo = \Route::getRoutes()->match($request);
        } catch (\Exception $exception) {
            $message = 'Wrong or error in provided route --- '. $event;
            $this->logCall(
                'error',
                'routed from API to direct method',
                (new \Exception($message)),
                $exception,
                ...func_get_args()
            );

            return $message;
        }
        try {
            $controllerInfo = explode('@', $routeInfo->action['controller']);
            $controller = $controllerInfo[0];
            $action = $controllerInfo[1];
            $payload = !empty($params) ? $params : $payload;
            //Inject any route params: {paramName}
            $routeParams = $routeInfo->parameters;
            if (!empty($routeParams)) {
                if (is_array($payload)) {
                    $payload = array_merge($payload, $routeParams);
                } elseif (empty($payload)) {
                    $payload = $routeParams;
                }
            }
            // response
            return $this->direct($controller, $action, $payload, $routeParams);
        } catch (\Exception $exception) {
            $this->logCall(
                'error',
                'routed from API to direct method',
                $exception,
                ...func_get_args()
            );
        }
    }


    /**
    * Log all events
     *
    * @param string $type
    * @param string $method
    * @param mixed ...$callInfo
    * @return void
    */
    private function logCall($type, $method, ...$callInfo)
    {
        LocalApiPortLogger::dispatch([
            'type' => $type,
            'method' => $method,
            'callInfo' => $callInfo,
        ])->delay(now()->addMinutes(2));
    }
}
