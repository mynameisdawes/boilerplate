<?php

namespace Vektor\Shop\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Shop\Models\Product;
use Vektor\Shop\Utilities as ShopUtilities;

class CartController extends ApiController
{
    public function index(Request $request, $instance = null)
    {
        $options = [];

        if ($instance) {
            \Cart::instance($instance);
            $options['instance'] = $instance;
        }

        if ($request->input('customisations_exclude_preview')) {
            $options['customisations'] = ['exclude' => ['preview']];
        }

        $cart = ShopUtilities::cart($options);

        return $this->response([
            'success' => true,
            'data' => $cart,
        ]);
    }

    public function store(Request $request, $instance = null)
    {
        $options = [];

        if ($instance) {
            \Cart::instance($instance);
            $options['instance'] = $instance;
        }

        $item_name = 'a product';
        $items = $request->input('items');

        if ($items && !empty($items)) {
            foreach ($items as $item) {
                $item_type = data_get($item, 'type', 'product');

                if ('shipping' == $item_type) {
                    $cart = ShopUtilities::cart($options);

                    $existing_cart_item = $cart->items->where('type', 'shipping')->first();

                    if ($existing_cart_item) {
                        $cart_item_data = [
                            'row_id' => $existing_cart_item->rowId,
                        ];

                        $cart_item = \Cart::remove($cart_item_data['row_id']);
                    }
                }

                $item_options = data_get($item, 'options', []);

                if (
                    isset($item_options['customisation_id'], $item_options['customisations'])
                ) {
                    $customisation_options = [
                        'designs' => $item_options['customisations'],
                        'note' => data_get($item_options, 'note'),
                    ];

                    $customisation = \Customisations::add($item_options['customisation_id'], $customisation_options) ?: \Customisations::update($item_options['customisation_id'], $customisation_options);

                    unset($item_options['customisations']);
                }

                if (data_get($item, 'rowId')) {
                    $cart_item_data = [
                        'row_id' => data_get($item, 'rowId'),
                    ];

                    $cart_item = \Cart::remove($cart_item_data['row_id']);
                }

                $cart_item_data = [
                    'type' => $item_type,
                    'id' => data_get($item, 'id'),
                    'qty' => data_get($item, 'qty', 1),
                    'name' => data_get($item, 'name', ''),
                    'price' => data_get($item, 'price', 0),
                    'weight' => data_get($item, 'weight', 0),
                    'options' => $item_options,
                    'attributes' => data_get($item, 'attributes', []),
                ];

                $item_name = $cart_item_data['name'];
                $cart_item = \Cart::add($cart_item_data)->associate(Product::class);

                if ('shipping' == $item_type) {
                    $cart_item->setTaxRate(20);
                }
                if ($cart_item->model && isset($cart_item->model->configuration, $cart_item->model->configuration['tax_percentage'])) {
                    $cart_item->setTaxRate($cart_item->model->configuration['tax_percentage']);
                }
            }
        }

        $cart = ShopUtilities::cart($options);

        return $this->response([
            'success' => true,
            'success_message' => "You added {$item_name} to your shopping cart",
            'http_code' => 201,
            'data' => $cart,
        ]);
    }

    public function update(Request $request, $row_id = null, $instance = null)
    {
        $options = [];

        if ($instance) {
            \Cart::instance($instance);
            $options['instance'] = $instance;
        }

        $cart = ShopUtilities::cart($options);

        $multi_select = $request->input('multi_select');
        $size = $request->input('size');

        if ($multi_select || $size) {
            $grouped_cart_change = false;
            $existing_grouped_cart_item = $cart->grouped->items->get($row_id);
            if ($existing_grouped_cart_item) {
                if (is_array($existing_grouped_cart_item->attributes->sizes) && count($existing_grouped_cart_item->attributes->sizes) > 0) {
                    foreach ($existing_grouped_cart_item->attributes->sizes as $attribute_size) {
                        if ($multi_select) {
                            if (null !== $attribute_size['rowId']) {
                                $existing_cart_item = $cart->items->get($attribute_size['rowId']);

                                if ($existing_cart_item) {
                                    if ($request->input('custom_price')) {
                                        $existing_cart_item->price = $request->input('custom_price');
                                        $existing_cart_item->custom_price = ($existing_cart_item->product && $existing_cart_item->product->price == $existing_cart_item->price) ? null : $existing_cart_item->price;
                                    } else {
                                        $existing_cart_item->price = $request->input('price');
                                        $existing_cart_item->custom_price = null;
                                    }

                                    $cart_item = \Cart::update($existing_cart_item->rowId, $existing_cart_item->toArray());
                                    $grouped_cart_change = true;
                                }
                            }
                        }

                        if ($size) {
                            if ($size['id'] == $attribute_size['id']) {
                                if (null === $attribute_size['rowId']) {
                                    $cart_item_data = [
                                        'type' => 'product',
                                        'id' => data_get($size, 'id'),
                                        'qty' => data_get($size, 'qty', 1),
                                        'name' => $existing_grouped_cart_item->name,
                                        'price' => $existing_grouped_cart_item->price,
                                        'weight' => $existing_grouped_cart_item->weight,
                                        'options' => $existing_grouped_cart_item->options->toArray(),
                                        'attributes' => [],
                                    ];

                                    $cart_item = \Cart::add($cart_item_data)->associate(Product::class);
                                    $grouped_cart_change = true;
                                } else {
                                    $existing_cart_item = $cart->items->get($attribute_size['rowId']);

                                    if ($existing_cart_item) {
                                        if (data_get($size, 'qty')) {
                                            $existing_cart_item->setQuantity(data_get($size, 'qty'));
                                            $cart_item = \Cart::update($existing_cart_item->rowId, $existing_cart_item->toArray());
                                        } else {
                                            $cart_item = \Cart::remove($existing_cart_item->rowId);
                                        }
                                        $grouped_cart_change = true;
                                    }
                                }

                                break;
                            }
                        }
                    }
                }

                if (true === $grouped_cart_change) {
                    $cart = ShopUtilities::cart($options);

                    return $this->response([
                        'success' => true,
                        'success_message' => "You updated {$existing_grouped_cart_item->formatted->name} in your shopping cart",
                        'data' => $cart,
                    ]);
                }
            }
        } else {
            $existing_cart_item = $cart->items->get($row_id);

            if ($existing_cart_item) {
                if ($request->input('custom_price')) {
                    $existing_cart_item->price = $request->input('custom_price');
                    $existing_cart_item->custom_price = ($existing_cart_item->product && $existing_cart_item->product->price == $existing_cart_item->price) ? null : $existing_cart_item->price;
                } else {
                    $existing_cart_item->price = $request->input('price');
                    $existing_cart_item->custom_price = null;
                }

                if ($request->input('qty')) {
                    $existing_cart_item->setQuantity($request->input('qty'));
                    $cart_item = \Cart::update($existing_cart_item->rowId, $existing_cart_item->toArray());

                    $cart = ShopUtilities::cart($options);

                    return $this->response([
                        'success' => true,
                        'success_message' => "You updated {$existing_cart_item->formatted->name} in your shopping cart",
                        'data' => $cart,
                    ]);
                }
                $cart_item = \Cart::remove($existing_cart_item->rowId);

                $cart = ShopUtilities::cart($options);

                return $this->response([
                    'success' => true,
                    'success_message' => "You removed {$existing_cart_item->formatted->name} from your shopping cart",
                    'data' => $cart,
                ]);
            }
        }

        return $this->response([
            'error' => true,
            'error_message' => 'The item you attempted to update no longer exists in the cart',
            'http_code' => 404,
        ]);
    }

    public function remove(Request $request, $row_id, $instance = null)
    {
        $options = [];

        if ($instance) {
            \Cart::instance($instance);
            $options['instance'] = $instance;
        }

        $item_type = $request->input('type');

        $cart = ShopUtilities::cart($options);

        if ('shipping' == $item_type) {
            $existing_cart_item = $cart->items->where('type', 'shipping')->first();

            if ($existing_cart_item) {
                $cart_item_data = [
                    'row_id' => $existing_cart_item->rowId,
                ];

                $cart_item = \Cart::remove($cart_item_data['row_id']);

                $cart = ShopUtilities::cart($options);

                return $this->response([
                    'success' => true,
                    'success_message' => 'You removed the shipping from your shopping cart',
                    'data' => $cart,
                ]);
            }
        } else {
            $grouped_cart_change = false;
            $existing_grouped_cart_item = $cart->grouped->items->get($row_id);
            if ($existing_grouped_cart_item) {
                if (is_array($existing_grouped_cart_item->attributes->sizes) && count($existing_grouped_cart_item->attributes->sizes) > 0) {
                    foreach ($existing_grouped_cart_item->attributes->sizes as $attribute_size) {
                        if (null !== $attribute_size['rowId']) {
                            $cart_item = \Cart::remove($attribute_size['rowId']);
                            $grouped_cart_change = true;
                        }
                    }

                    if (true === $grouped_cart_change) {
                        $cart = ShopUtilities::cart($options);

                        return $this->response([
                            'success' => true,
                            'success_message' => "You removed {$existing_grouped_cart_item->formatted->name} from your shopping cart",
                            'data' => $cart,
                        ]);
                    }
                }
            }

            $existing_cart_item = $cart->items->get($row_id);
            if ($existing_cart_item) {
                $cart_item_data = [
                    'row_id' => $existing_cart_item->rowId,
                ];

                $cart_item = \Cart::remove($cart_item_data['row_id']);

                $cart = ShopUtilities::cart($options);

                return $this->response([
                    'success' => true,
                    'success_message' => "You removed {$existing_cart_item->formatted->name} from your shopping cart",
                    'data' => $cart,
                ]);
            }
        }

        return $this->response([
            'error' => true,
            'error_message' => 'The item you attempted to remove no longer exists in the cart',
            'http_code' => 404,
        ]);
    }

    public function destroy(Request $request, $instance = null)
    {
        $options = [];

        if ($instance) {
            \Cart::instance($instance);
            $options['instance'] = $instance;
        }

        if ('shipping' == $request->input('type')) {
            $cart = ShopUtilities::cart($options);

            $existing_cart_item = $cart->items->where('type', 'shipping')->first();

            if ($existing_cart_item) {
                $cart_item_data = [
                    'row_id' => $existing_cart_item->rowId,
                ];

                $cart_item = \Cart::remove($cart_item_data['row_id']);
            }
        } else {
            \Cart::destroy();
        }

        $cart = ShopUtilities::cart($options);

        return $this->response([
            'success' => true,
            'data' => $cart,
        ]);
    }

    public function storeToDb(Request $request, $instance = null)
    {
        if ($instance) {
            \Cart::instance($instance);
        }

        $identifier = null;
        $user = \Auth::user();
        if ($user) {
            $identifier = $user->id.'_'.Str::slug(microtime());
        } elseif ($request->input('identifier')) {
            $identifier = $request->input('identifier');
        }

        if ($identifier) {
            try {
                \Cart::store($identifier);
                if ($user) {
                    DB::table('shop_cart_user')->insert([
                        'user_id' => $user->id,
                        'identifier' => $identifier,
                        'instance' => null !== $instance ? $instance : 'default',
                        'name' => $request->input('name'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                \Cart::destroy();

                return $this->response([
                    'success' => true,
                    'success_message' => 'The cart was stored successfully',
                ]);
            } catch (\Exception $e) {
            }
        }

        return $this->response([
            'error' => true,
            'error_message' => 'The cart could not be stored',
            'http_code' => 403,
        ]);
    }

    public function restoreFromDb(Request $request, $instance = null)
    {
        if ($instance) {
            \Cart::instance($instance);
        }

        if ($request->input('identifier')) {
            try {
                \Cart::restore($request->input('identifier'));

                return $this->response([
                    'success' => true,
                    'success_message' => 'The cart was restored successfully',
                ]);
            } catch (\Exception $e) {
            }
        }

        return $this->response([
            'error' => true,
            'error_message' => 'The cart could not be restored',
            'http_code' => 403,
        ]);
    }

    public function fetchSavedCarts(Request $request, $instance = null)
    {
        $user = Auth::user();

        if ($user) {
            $query = DB::table('shop_cart_user')->join('shop_cart', function ($join) {
                $join->on('shop_cart_user.identifier', '=', 'shop_cart.identifier')->on('shop_cart_user.instance', '=', 'shop_cart.instance');
            })->where('shop_cart_user.user_id', $user->id);

            if ($instance) {
                $query->where('shop_cart_user.instance', $instance);
            }

            $records = $query->get([
                'shop_cart_user.identifier',
                'shop_cart_user.instance',
                'shop_cart_user.name',
                'shop_cart_user.created_at as saved_at',
                'shop_cart.content',
            ]);

            $carts = [];
            foreach ($records as $record) {
                $carts[] = [
                    'identifier' => $record->identifier,
                    'instance' => $record->instance,
                    'name' => $record->name,
                    'saved_at' => $record->saved_at,
                    // 'content'    => json_decode($record->content, true),
                ];
            }

            return $this->response([
                'success' => true,
                'data' => [
                    'carts' => $carts,
                ],
            ]);
        }

        return $this->response([
            'error' => true,
            'error_message' => 'The carts could not be fetched',
            'http_code' => 404,
        ]);
    }
}
