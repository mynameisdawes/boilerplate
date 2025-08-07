<?php

namespace Vektor\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Vektor\Api\Api;

abstract class ApiController extends Controller
{
    protected $api;

    public function __construct()
    {
        $this->api = new Api();
    }

    public function response($data = null)
    {
        return $this->api->response($data);
    }

    public function isSuccess($data = null)
    {
        $response_data = $this->api->transformResponse($data);
        if ($response_data['success']) {
            return true;
        }

        return false;
    }

    public function isError($data = null)
    {
        $response_data = $this->api->transformResponse($data);
        if ($response_data['error']) {
            return true;
        }

        return false;
    }
}
