<?php

namespace Vektor\Shop\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Shop\Utilities as ShopUtilities;

class ProductController extends ApiController
{
    public function index(Request $request)
    {
        $products = [];

        if ($request->input('paginate', false)) {
            $products = ShopUtilities::paginatedProducts($request);
        } else {
            $products = ShopUtilities::products($request);
        }

        return $this->response([
            'success' => true,
            'data' => [
                'products' => collect($products),
            ],
        ]);
    }
}
