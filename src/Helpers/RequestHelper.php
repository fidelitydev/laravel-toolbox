<?php

namespace Knighttower\Toolbox\Helpers;

/**
* @package Helpers\RequestHelper
* Request handy functions to simplify code blocks
*/

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RequestHelper
{
    /**
    * Request object
     *
    * @var $request
    */
    private $request;

    /**
    * Construct
     *
    * @return void
    */
    public function __construct()
    {
        $this->request = request();
    }

    /**
    * get the correct request
     *
    * @return object
    */
    public function request()
    {
        return $this->request = emptyOrValue($this->request, request());
    }

    /**
    * Set a custom request if needed
     *
    * @param Request $request
    * @return $this
    */
    public function use($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
    * Check if the request is API
     *
    * @api
    * @return Response
    */
    public function isApi()
    {
        return (
            $this->request()->expectsJson() ||
            $this->request()->is('api/*') ||
            !empty($this->request()->input('isApi'))
        );
    }

    /**
    * Check if console of background only
    *
    * @return Response
    */
    public function isBackgroundProcess()
    {
        return (
            (bool) app()->runningInConsole() ||
            !empty($this->request()->input('runInBackground')) ||
            !empty(config('runInBackground'))
        );
    }


    /**
    * Check if consumed by self
     *
    * @return Response
    */
    public function reponseAsCollection()
    {
        return (
            (
                !empty($this->request()->input('returnAsCollection')) ||
                !empty(config('returnAsCollection'))
            )
        );
    }


    /**
    * Alias to reponseAsCollection()
    *
    * @return Response
    */
    public function returnAsCollection()
    {
        return $this->reponseAsCollection();
    }


    /**
    * Set to consume by self
    *
    * @return void
    */
    public function setReponseAsCollection()
    {
        config(['returnAsCollection' => true]);
    }


    /**
    * Cleans the request object to prevent passing unwanted parameters
     *
    * @param mixed $payload
    * @return void
    */
    public function cleanup($payload = []): void
    {
        // Handle Json objects passing as string
        if (is_string($payload)) {
            $payload = json_decode($payload);
            $payload = (array) $payload;
            if (!empty($payload['data']) || !empty($payload['response'])) {
                $payload = $payload['response'] ?? $payload['data'];
                $payload = (array) $payload;
            }
        }

        if (empty($payload)) {
            $payload = [];
        }

        // Special params that need to be removed
        $payload = array_merge($payload, [
            'runInBackground' => true,
            'returnAsCollection' => true,
            'isApi' => true,
        ]);

        foreach ($payload as $key => $param) {
            $this->request()->query->remove($key);
            $this->request()->except($key);
            config([$key => false]);
        }
    }

    // phpcs:disable
    /**
    * alias to cleanup
    * @return mixed
    */
    public function unset()
    {
        return $this->cleanup();
    }
    // phpcs:enable


    /**
    * Validate the request and return the error
     *
    * @api
    * @param array|object $request Actual Request object or Array of it
    * @param array $fields Assoc Key:value
    * @return bool|Response
    * @throws \Exception If an object is passed it has to be an instance of Request.
    */
    public function validateRequest($request, array $fields)
    {
        if (!is_array($request)) {
            if ($request instanceof \Illuminate\Http\Request) {
                $request = $request->all();
            } else {
                throw new \Exception('$request must be a valid array of fields or instance of Request');
            }
        }

        $validator = Validator::make($request, $fields);

        if ($validator->fails()) {
            // phpcs:ignore
            abort(500, var_export($validator->errors(), true));
        }

        return true;
    }
}
