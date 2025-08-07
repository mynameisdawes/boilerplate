<?php

namespace Vektor\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;

class CartController extends ApiController
{
    public function index(Request $request)
    {
        return view('shop::cart');
    }
}
