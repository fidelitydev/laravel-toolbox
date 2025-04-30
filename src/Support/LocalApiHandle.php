<?php

namespace Knighttower\Toolbox\Support;

use Illuminate\Container\Container;

/**
* Class to help handle the LocalApiPort executions
*/
class LocalApiHandle
{
    /**
     * Construct.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
    * Modify and create a new single use request
     *
    * @internal
    * @param array $payload
    * @param array $routeParams
    * @return object With request and dependencies
    */
    private function setRequest(array $payload, array $routeParams)
    {
        // INIT a new request to inject by cloning the current request
        $request = request()->duplicate(null, $payload);

        // if any params
        if (!empty($payload)) {
            // clean the request to avoid previous queries being reused
            foreach ($request->query->keys() as $key) {
                $request->query->remove($key);
            }
            $request->only([]);
            /*
                * Set the payload into the cloned request,
                * this avoids setting it into the actual request
                * and avoids conflicts
                */
            foreach ($payload as $key => $value) {
                $request->query->set($key, $value);
                $request->merge([$key => $value]);
            }
        }

        // Make the call and execute callback
        $dependencies = array_merge([
            'request' => $request,
        ], $routeParams);

        return (object) [
            'request' => $request,
            'dependencies' => $dependencies
        ];
    }

    /**
    * Execute with the service Container
     *
    * @param object $params
    * @return mixed
    */
    public function execute(object $params)
    {
        $app = $this->container;
        $tmpApp = clone $this->container;
        $request = $this->setRequest($params->payload, $params->routeParams);

        /**
        * Usefull Trick to replicate the app and manipulate the request entierly
         *
        * @see https://divinglaravel.com/laravel-octane-bootstrapping-the-application-and-handling-requests
        */

        Container::setInstance($tmpApp);
        $tmpApp->instance('request', $request->request);
        $tmpApp->scoped('localApiProcess', function () use ($tmpApp, $request, $params) {
            return $tmpApp->make($params->controller, $request->dependencies);
        });

        $response = $tmpApp->call('localApiProcess', $request->dependencies, $params->method);
        $tmpApp->forgetInstance('request');
        $tmpApp->forgetScopedInstances();
        Container::setInstance($app);

        return $response;
    }
}
