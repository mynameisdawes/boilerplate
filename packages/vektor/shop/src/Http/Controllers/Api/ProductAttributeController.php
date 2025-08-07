<?php

namespace Vektor\Shop\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Shop\Utilities as ShopUtilities;

class ProductAttributeController extends ApiController
{
    public function index(Request $request)
    {
        $product_attributes = ShopUtilities::product_attributes($request);

        return $this->response([
            'success' => true,
            'data' => [
                'attributes' => collect($product_attributes),
            ],
        ]);
    }
}
