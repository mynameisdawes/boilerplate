<?php

namespace Vektor\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;

class SuccessController extends ApiController
{
    public function index(Request $request)
    {
        return view('shop::success');
    }

    public function redirect(Request $request)
    {
        return redirect()->route('base');
    }
}
