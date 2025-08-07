<?php

namespace Vektor\Utilities\Http\Controllers\Api;

use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Utilities\Countries;

class CountriesController extends ApiController
{
    public function index()
    {
        return $this->response([
            'success' => true,
            'data' => [
                'countries' => Countries::select(),
            ],
        ]);
    }
}
