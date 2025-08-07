<?php

namespace Vektor\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Shop\Models\Attribute;
use Vektor\Shop\Models\PrintConfigDtf;
use Vektor\Shop\Models\PrintPriceDtf;
use Vektor\Shop\Models\Product;
use Vektor\Shop\Models\ProductAttribute;
use Vektor\Shop\Models\ProductMarkup;
use Vektor\Shop\Utilities;

class ProductController extends ApiController
{
    public function index(Request $request)
    {
        if (true === config('shop.as_base') || true === config('shop.only')) {
            return redirect()->route('base');
        }

        if (config('shop.single_product_slug')) {
            return redirect()->route('shop.product.show', config('shop.single_product_slug'));
        }

        return view('shop::index');
    }

    public function product_type_index(Request $request, $product_type)
    {
        if (true === config('shop.as_base') || true === config('shop.only')) {
            return redirect()->route('base');
        }

        if (config('shop.single_product_slug')) {
            return redirect()->route('shop.product.show', config('shop.single_product_slug'));
        }

        $product_attribute = ProductAttribute::whereHas('attribute', function ($query) {
            $query->where('name', 'product_type');
        })->where('value', $product_type)->first();

        if ($product_attribute) {
            return view('shop::index', ['product_type' => $product_type, 'product_type_title' => Str::plural(Str::headline($product_type))]);
        }

        return redirect()->route('shop.product.index');
    }

    public function show(Request $request, $slug, $customisation_id = null)
    {
        [$swatches, $other_attributes] = Attribute::select('id', 'name', 'name_label', 'configuration')->get()->keyBy('id')->partition(function ($attribute) {
            return $attribute->configuration['is_swatch'];
        });

        $_product = Product::with(['attributes', 'products' => function ($query) {
            $query->select('id', 'parent_id', 'price', 'images', 'configuration', 'metadata', 'is_enabled', 'weight');
            $query->orderBy('sort_order');
        }, 'products.attributes'])->where('slug', $slug)->where('is_enabled', 1)->first();

        if ($_product) {
            if ($_product->parent_id) {
                return redirect()->route('shop.product.show', ['slug' => $_product->parent->slug, 'customisation' => $customisation_id]);
            }

            $customisable = isset($_product->configuration, $_product->configuration['is_customisable']) && true === $_product->configuration['is_customisable'] ? true : false;
            $multi_select = isset($_product->configuration, $_product->configuration['is_multi_select']) && true === $_product->configuration['is_multi_select'] ? true : false;

            $customisations = null;
            if ($customisation_id) {
                try {
                    $customisations = \Customisations::get($customisation_id);
                    if (!empty($customisations)) {
                        $customisations = collect($customisations)->toJson();
                    }

                    $customisations_products = \Cart::content()->where('options.customisation_id', $customisation_id);

                    if ($multi_select) {
                        $customisations_products_selected = [];
                        if ($customisations_products->count() > 0) {
                            foreach ($customisations_products as $customisations_product) {
                                $customisations_products_selected[$customisations_product->id] = [
                                    'qty' => $customisations_product->qty,
                                    'rowId' => $customisations_product->rowId,
                                ];
                            }
                        }
                        $_product->selected = $customisations_products_selected;
                    } else {
                        if ($customisations_products->count() > 0) {
                            foreach ($customisations_products as $customisations_product) {
                                $_product->qty = $customisations_product->qty;
                                $_product->rowId = $customisations_product->rowId;
                                if (isset($customisations_product->formatted, $customisations_product->formatted->attributes)) {
                                    $_product->options = [
                                        $customisations_product->formatted->attributes[0]['name'] => $customisations_product->formatted->attributes[0]['value'],
                                        $customisations_product->formatted->attributes[1]['name'] => $customisations_product->formatted->attributes[1]['value'],
                                    ];
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    return redirect()->route('shop.product.show', ['slug' => $_product->slug]);
                }
            }

            if ($_product) {
                $_product->configuration = [
                    'tax_percentage' => data_get($_product->configuration, 'tax_percentage'),
                    'description' => data_get($_product->configuration, 'description'),
                    'size_guide' => data_get($_product->configuration, 'size_guide'),
                    'size_guide_note' => data_get($_product->configuration, 'size_guide_note'),
                    'shipping' => data_get($_product->configuration, 'shipping'),
                    'builder_config' => data_get($_product->configuration, 'builder_config'),
                    'is_customisable' => data_get($_product->configuration, 'is_customisable'),
                    'is_multi_select' => data_get($_product->configuration, 'is_multi_select'),
                ];

                // $temp_values = $_product->attributes->keyBy(function ($attribute) use ($other_attributes) {
                //     return $other_attributes->get($attribute['attribute_id']) ? $other_attributes->get($attribute['attribute_id'])['name'] : null;
                // });

                // $_product->setRelation('attributes', $temp_values);

                // $_product->products = $_product->products->map(function ($child) use ($swatches) {
                //     $child->configuration = [
                //         'builder_images' => data_get($child->configuration, 'builder_images', []),
                //         'tax_percentage' => data_get($child->configuration, 'tax_percentage'),
                //     ];

                //     $temp_values = $child->attributes->keyBy(function ($attribute) use ($swatches) {
                //         return $swatches->get($attribute['attribute_id']) ? $swatches->get($attribute['attribute_id'])['name'] : null;
                //     });

                //     $child->setRelation('attributes', $temp_values);

                //     return $child;
                // });
            }

            $view_variables = [
                'title' => $_product->name_label,
                'product' => $_product->toJson(),
                'customisable' => $customisable,
                'multi_select' => $multi_select,
                'customisations' => $customisations,
            ];

            return view('shop::show', $view_variables);
        }

        return redirect()->route('shop.product.index');
    }
}
