<?php

namespace Vektor\Shop;

use Vektor\Shop\Formatter as ShopFormatter;
use Vektor\Shop\Models\Attribute;
use Vektor\Shop\Models\Product;
use Vektor\Shop\Services\ShippingCalculatorService;
use Vektor\Utilities\Formatter;

class Utilities
{
    public static function compileProducts($request)
    {
        $user = \Auth::user();
        $onecrm_category_id = null;

        if (
            $user
            && isset($user->configuration, $user->configuration['onecrm_product_category_id'])

            && !empty($user->configuration['onecrm_product_category_id'])
        ) {
            $onecrm_category_id = $user->configuration['onecrm_product_category_id'];
        } elseif (config('onecrm.product_category_id')) {
            $onecrm_category_id = config('onecrm.product_category_id');
        }

        $_product_query = Product::with([
            'attributes'=> function ($query) {
                $query->select('product_id', 'attribute_id', 'value_label');
            },
            'products' => function ($query) {
                $query->select('id', 'parent_id', 'slug', 'name', 'price', 'images', 'configuration', 'sort_order')->orderBy('sort_order')->with(['attributes' => function ($query) {
                    $query->select('product_id', 'configuration', 'value');
                }]);
            },
        ])->whereNull('parent_id')->where('is_enabled', 1)->select('id', 'slug', 'name', 'price', 'images', 'configuration', 'sort_order');

        if ($request->has('ids')) {
            $ids = (array) $request->input('ids');
            $_product_query->whereIn('id', $ids);
        }

        if ($request->has('filters')) {
            $filters = collect($request->input('filters'));
            $filters_grouped = $filters->groupBy('attribute_name');

            foreach ($filters_grouped as $attribute_name => $group) {
                $_product_query->where(function ($_parent_product_query) use ($attribute_name, $group) {
                    $_parent_product_query->whereHas('attributes', function ($query) use ($attribute_name, $group) {
                        $query->whereHas('attribute', function ($query) use ($attribute_name) {
                            $query->where('name', $attribute_name);
                        })->whereIn('value', $group->pluck('value')->toArray());
                    })->orWhereHas('products.attributes', function ($query) use ($attribute_name, $group) {
                        $query->whereHas('attribute', function ($query) use ($attribute_name) {
                            $query->where('name', $attribute_name);
                        })->whereIn('value', $group->pluck('value')->toArray());
                    });
                });
            }
        }

        if ($onecrm_category_id) {
            $_product_query->whereJsonContains('configuration->onecrm_category_id', $onecrm_category_id);
        }

        if (
            null === $user
            || (
                $user && isset($user->configuration) && (
                    !isset($user->configuration['can_purchase_services'])
                        || (isset($user->configuration['can_purchase_services']) && false === $user->configuration['can_purchase_services'])
                )
            )
        ) {
            $_product_query->whereJsonDoesntContain('configuration->service', true);
        }

        return $_product_query->orderBy('sort_order');
    }

    public static function paginatedProducts($request)
    {
        $_product_query = self::compileProducts($request);
        $_products = $_product_query->paginate($request->input('per_page', 1))->toArray();
        if (!empty($_products['data'])) {
            $_products['data'] = ShopFormatter::products($_products['data']);

            return $_products;
        }

        return [];
    }

    public static function products($request)
    {
        $_product_query = self::compileProducts($request);
        $_products = $_product_query->get()->toArray();
        if (!empty($_products)) {
            return ShopFormatter::products($_products);
        }

        return [];
    }

    public static function product($id, $swatches, $other_attributes)
    {
        $product_query = Product::select('id', 'name', 'price', 'images', 'configuration', 'metadata', 'weight');

        $product_query->with([
            'attributes',
            'products' => function ($query) {
                $query->select('id', 'parent_id', 'price', 'images', 'configuration', 'metadata', 'is_enabled', 'weight');
                $query->orderBy('sort_order');
            },
            'products.attributes' => function ($query) use ($swatches) {
                $query->whereIn('attribute_id', $swatches->keys()->toArray());
            },
        ]);

        $_product = $product_query->where('is_enabled', 1)->find($id);

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

            $temp_values = $_product->attributes->keyBy(fn ($attribute) => $other_attributes->get($attribute['attribute_id'])['name']);
            $_product->setRelation('attributes', $temp_values);

            $_product->products = $_product->products->map(function ($child) use ($swatches) {
                $child->configuration = [
                    'builder_images' => data_get($child->configuration, 'builder_images', []),
                    'tax_percentage' => data_get($child->configuration, 'tax_percentage'),
                ];

                $temp_values = $child->attributes->keyBy(fn ($attribute) => $swatches->get($attribute['attribute_id'])['name']);
                $child->setRelation('attributes', $temp_values);

                return $child;
            });

            return $_product;
        }

        return null;
    }

    public static function product_attributes($request)
    {
        $query = Attribute::query();

        if ($request->has('attribute_names')) {
            $attribute_names = $request->input('attribute_names');
            $query->whereIn('name', $attribute_names);
        }

        if ($request->has('attribute_names_excluded')) {
            $attribute_names_excluded = $request->input('attribute_names_excluded');
            $query->whereNotIn('name', $attribute_names_excluded);
        }

        $required_values = $request->input('required_values', []);
        $attribute_ids = [];
        if (!empty($required_values)) {
            $attribute_ids = Attribute::whereIn('name', array_keys($required_values))
                ->pluck('id', 'name')
            ;
        }

        $subquery = function ($query) use ($required_values, $attribute_ids) {
            foreach ($required_values as $name => $value) {
                $id = $attribute_ids[$name] ?? null;
                if (null !== $id) {
                    $query->whereHas('attributes', function ($innerQuery) use ($id, $value) {
                        $innerQuery->where('attribute_id', $id)
                            ->where('value', $value)
                        ;
                    });
                }
            }
        };

        $attributes = $query->with(['attributes' => function ($query) use ($required_values, $subquery) {
            if (count($required_values) > 0) {
                $query->whereHas('product', $subquery);
            }
            $query->select('product_attributes.attribute_id', 'product_attributes.value', 'product_attributes.value_label')
                ->selectRaw('MAX(product_attributes.configuration) AS configuration')
                // ->join('products', 'product_attributes.product_id', '=', 'products.id')
                ->orderBy('product_attributes.sort_order')
                ->groupBy('product_attributes.attribute_id', 'product_attributes.value', 'product_attributes.value_label')
            ;
        }])->get();

        return $attributes;
    }

    public static function addPercentage($value, $percentage)
    {
        if (empty($percentage)) {
            return floatval(Formatter::decimalPlaces($value));
        }

        return floatval(Formatter::decimalPlaces($value * (1 + ($percentage / 100))));
    }

    public static function transformLine($data, $html = false)
    {
        $line_model = null;
        $name = '';
        if ('shipping' == $data['id']) {
            $name .= $data['name'];
        } else {
            $name_parts = [];
            $line_model = Product::where('id', $data['id'])->first();
            if ($line_model) {
                $name_parts[] = "{$line_model->name} [{$line_model->sku}]";
            } else {
                $name_parts[] = $data['name'];
                if (isset($data['options']['color'])) {
                    $name_parts[] = strtoupper(substr($data['options']['color'], 0, 3));
                } elseif (isset($data['options']['colour'])) {
                    $name_parts[] = strtoupper(substr($data['options']['colour'], 0, 3));
                }
                if (isset($data['options']['size'])) {
                    $name_parts[] = strtoupper($data['options']['size']);
                }
            }
            $name .= implode('-', $name_parts);
        }

        $output = [
            'id' => $data['id'],
            'formatted' => [],
            'name' => $name,
            'quantity' => $data['qty'],
            'unit_price' => $data['price'],
            'std_unit_price' => $data['price'],
            'ext_price' => $data['subtotal'],
            'net_price' => $data['subtotal'],
        ];

        if ('shipping' == $data['id']) {
            $output['related_type'] = 'ProductCatalog';
            $output['related_id'] = config('onecrm.shipping_related_id');
            $output['mfr_part_no'] = config('onecrm.shipping_mfr_part_no');
            $output['display_price'] = self::addPercentage($data['price'], 20);
            $output['tax'] = round($output['display_price'] - $data['price'], 2);
            $output['formatted']['display_price'] = Formatter::currency($output['display_price']);
            $output['formatted']['tax'] = Formatter::currency($output['tax']);

            $comment = '';
            $comment_parts = [];

            if (isset($data['options']['method_name'])) {
                $comment_parts[] = 'Shipping Method: '.$data['options']['method_name'];
            }

            if (!empty($comment_parts)) {
                if ($html) {
                    $comment .= $data['name'].'<br/><small>'.implode(', ', $comment_parts).'</small>';
                } else {
                    $comment .= implode(', ', $comment_parts);
                }
            }

            $output['comment'] = $comment;
        } else {
            $output['display_price'] = $data['price'];
            $output['tax'] = 0;
            $output['formatted']['display_price'] = Formatter::currency($output['display_price']);
            $output['formatted']['tax'] = Formatter::currency($output['tax']);
            if ($line_model && $line_model->configuration) {
                if (isset($line_model->configuration['onecrm_id'])) {
                    $output['related_type'] = 'ProductCatalog';
                    $output['related_id'] = $line_model->configuration['onecrm_id'];
                    $output['mfr_part_no'] = $line_model->sku;
                }
                if (isset($line_model->configuration['onecrm_tax_code_id'])) {
                    $output['tax_class_id'] = $line_model->configuration['onecrm_tax_code_id'];
                }
                if (isset($line_model->configuration['tax_percentage'])) {
                    $output['display_price'] = self::addPercentage($data['price'], $line_model->configuration['tax_percentage']);
                    $output['tax'] = round($output['display_price'] - $data['price'], 2);
                    $output['formatted']['display_price'] = Formatter::currency($output['display_price']);
                    $output['formatted']['tax'] = Formatter::currency($output['tax']);
                }
            }

            if ($line_model && $line_model->attributes) {
                foreach ($line_model->attributes as $attribute) {
                    if (isset($attribute['configuration'], $attribute['configuration']['onecrm_id'])) {
                        if (!isset($output['adjustments'])) {
                            $output['adjustments'] = [];
                        }
                        $output['adjustments'][] = [
                            'id' => $attribute['configuration']['onecrm_id'],
                            'name' => "{$attribute['name_label']} : {$attribute['value_label']}",
                        ];
                    }
                }
            }

            $comment = '';
            $comment_parts_html = [];
            $comment_parts = [];

            foreach ($data['options'] as $option_key => $option_value) {
                if (!in_array($option_key, [
                    'rowId',
                    'primary',
                    'secondary',
                    'is_multi_select',
                    'is_custom',
                ])) {
                    if ('color' == $option_key) {
                        $option_key = 'colour';
                    }
                    $readable_option_key = ucwords(str_replace('_', ' ', $option_key));
                    $comment_parts_html[] = "{$readable_option_key}: ".((in_array($option_key, ['size'])) ? strtoupper($option_value) : $option_value);
                    if (!in_array($option_key, [
                        'size',
                        'colour',
                        'color',
                    ])) {
                        $comment_parts[] = "{$readable_option_key}: ".((in_array($option_key, ['size'])) ? strtoupper($option_value) : $option_value);
                    }
                }
            }

            if (!empty($comment_parts_html)) {
                if ($html) {
                    $comment = $data['name'].'<br/><small>'.implode(', ', $comment_parts_html).'</small>';
                }
            }

            if (!empty($comment_parts)) {
                if (false === $html) {
                    $comment = implode(', ', $comment_parts);
                }
            }

            $output['comment'] = $comment;

            if (isset($data['customisation_id'])) {
                $output['customisation_id'] = $data['customisation_id'];
            }
        }

        return $output;
    }

    public static function unravelMultiSelectLine($cart_item)
    {
        $lines = [];
        $_options = $cart_item->options->toArray();
        $options = $_options['garment_options'];
        $primary = $_options['primary'];
        $secondary = $_options['secondary'];
        foreach ($options[$secondary] as $option) {
            if ($option['qty'] > 0) {
                $line = $cart_item->toArray();
                $line['options'] = [
                    $primary => $options[$primary],
                    $secondary => $option['value'],
                ];
                $line['qty'] = $option['qty'];
                if ($cart_item->options['is_custom']) {
                    $line['customisation_id'] = $line['id'];
                }
                $line['id'] = $option['id'];
                $line['subtotal'] = $line['price'] * $line['qty'];
                $lines[] = $line;
            }
        }

        return $lines;
    }

    public static function transformLines($array, $html = false)
    {
        if (!empty($array)) {
            foreach ($array as &$line) {
                if (self::detectMultiSelectLines($line)) {
                    $new_lines = self::unravelMultiSelectLine($line);
                    $line = null;
                    $array = array_merge($array, $new_lines);
                } elseif (self::detectCustomLines($line)) {
                    if (!isset($line['customisation_id'])) {
                        $line['customisation_id'] = $line['id'];
                        $line['id'] = $line['options']['id'];
                    }
                }
            }
            $array = array_filter($array);
            foreach ($array as &$line) {
                $line = self::transformLine($line, $html);
            }
            unset($line);
        }

        return array_values(array_filter($array));
    }

    public static function detectMultiSelectLines($item)
    {
        return isset($item['options']) && isset($item['options']['is_multi_select']) && true === $item['options']['is_multi_select'] ? true : false;
    }

    public static function detectCustomLines($item)
    {
        return isset($item['options']) && isset($item['options']['is_custom']) && true === $item['options']['is_custom'] ? true : false;
    }

    public static function cleanHtml($value)
    {
        return trim(preg_replace('/[\s]{2,}/', '', preg_replace('/[\n\t\r]/', ' ', $value)));
    }

    public static function filenameToUrl($filename, $relative = false, $files = null)
    {
        $files = $files ?? self::fetchProductImageFilenames();
        if (!empty($files)) {
            $file = pathinfo($filename)['filename'];
            if (isset($files[$file])) {
                if ($relative) {
                    return $files[$file]['filename'];
                }

                return route('shop.product_images.product_images', ['base_dir' => $files[$file]['filename']]);
            }
            $sub_dir = $files->keys()->filter(fn ($key) => str_starts_with($filename, $key))->first();
            if (isset($sub_dir)) {
                $sub_files = self::fetchProductImageFilenames($sub_dir);
                $found_file = self::filenameToUrl($filename, $relative, $sub_files);

                return $found_file ? $sub_dir.'/'.$found_file : null;
            }
        }

        return null;
    }

    public static function filenamesToUrl($filenames, $relative = false, $files = null)
    {
        $urls = [];
        if (is_string($filenames)) {
            $filenames_array = array_map('trim', explode(',', $filenames));
        }
        if (is_array($filenames)) {
            $filenames_array = $filenames;
        }
        if (!empty($filenames_array)) {
            $files = $files ?? self::fetchProductImageFilenames();
            foreach ($filenames_array as $filename) {
                $url = self::filenameToUrl($filename, $relative, $files);
                if (null !== $url) {
                    $urls[] = $url;
                }
            }
        }

        return $urls;
    }

    public static function fetchProductImageFilenames($sub_dir = null)
    {
        return collect(glob(resource_path('assets/products/'.($sub_dir ? $sub_dir.'/' : '').'*')))->map(function ($file) {
            return pathinfo($file);
        })->keyBy('filename');
    }

    public static function cartCustomisations($options = [])
    {
        if (isset($options['instance'])) {
            \Cart::instance($options['instance']);
        }

        $customisations_collection = [];
        $cart_collection = \Cart::content();

        if ($cart_collection->count() > 0) {
            $cart_collection->each(function ($cart_item) use (&$customisations_collection, $options) {
                $cart_item_customisation_id = $cart_item->options->get('customisation_id');

                if ($cart_item_customisation_id) {
                    if (\Customisations::has($cart_item_customisation_id) && !isset($customisations_collection[$cart_item_customisation_id])) {
                        $cart_item_customisation = \Customisations::get($cart_item_customisation_id);

                        $cart_item_customisation_designs = [];

                        if (!empty($cart_item_customisation['designs'])) {
                            $customisation_count = 1;

                            if (!isset($cart_item_customisation['production_process'])) {
                                $cart_item_customisation['production_process'] = 'dtf';
                            }

                            foreach ($cart_item_customisation['designs'] as $customisation_name => $customisation_design) {
                                if ('dtf' === $cart_item_customisation['production_process'] && false === $customisation_design['selected']) {
                                    continue;
                                }

                                $customisation_design_preview_url_start = null;
                                $customisation_design_preview_url_end = null;

                                if (isset($customisation_design['links'], $customisation_design['links']['original'])) {
                                    if (!empty($cart_item->product->attributes)) {
                                        foreach ($cart_item->product->attributes as $attribute) {
                                            $link_match = preg_match('/preview\/(.+)\/(design[\d]{1,3})\/(.+)\/original/', $customisation_design['links']['original'], $link_matches);
                                            if ($link_match && 4 == count($link_matches)) {
                                                $customisation_design_preview_url_start = url(implode('/', [
                                                    'preview',
                                                    $link_matches[1],
                                                    $link_matches[2],
                                                ]));

                                                $customisation_design_preview_url_end = implode('/', [
                                                    $link_matches[3],
                                                    '?model=quote&image=true&size=100',
                                                ]);
                                            }

                                            break;
                                        }
                                    }
                                }

                                if (
                                    isset($options['customisations'], $options['customisations']['exclude'])

                                    && !empty($options['customisations']['exclude'])
                                ) {
                                    foreach ($options['customisations']['exclude'] as $exclude) {
                                        if (isset($customisation_design[$exclude])) {
                                            unset($customisation_design[$exclude]);
                                        }
                                    }
                                }

                                if ('dtf' === $cart_item_customisation['production_process']) {
                                    $cart_item_customisation_designs[$customisation_name] = array_merge($customisation_design, [
                                        'formatted' => [
                                            'title' => Formatter::ordinal($customisation_count).' Location',
                                            'label' => $customisation_design['label'].' '.$customisation_design['position'],
                                            'web' => round($customisation_design['printPixelValues']['w'], 0).'px/'.round($customisation_design['printPixelValues']['h'], 0).'px',
                                            'print' => round($customisation_design['dimensions']['w'], 0).'mm/'.round($customisation_design['dimensions']['h'], 0).'mm',
                                            'image' => isset($cart_item->product->configuration['builder_images']) && isset($cart_item->product->configuration['builder_images'][$customisation_name.'_image']) ? $cart_item->product->configuration['builder_images'][$customisation_name.'_image'] : null,
                                            'preview_url_start' => $customisation_design_preview_url_start,
                                            'preview_url_end' => $customisation_design_preview_url_end,
                                        ],
                                    ]);
                                } elseif ('scr' === $cart_item_customisation['production_process']) {
                                    $cart_item_customisation_designs[$customisation_name] = $customisation_design;
                                }

                                ++$customisation_count;
                            }
                        }

                        $cart_item_customisation['designs'] = $cart_item_customisation_designs;

                        $customisations_collection[$cart_item_customisation_id] = $cart_item_customisation;
                    }
                }
            });
        }

        return collect($customisations_collection);
    }

    public static function cartWithProducts($options = [])
    {
        if (isset($options['instance'])) {
            \Cart::instance($options['instance']);
        }

        $cart_collection = \Cart::content($options['show_unavailable'] ?? false);

        if ($cart_collection->count() > 0) {
            $cart_collection_product_ids = $cart_collection->pluck('id');
            $cart_collection_products = Product::with(['parent'])->findMany($cart_collection_product_ids);

            if ($cart_collection_products->count() > 0) {
                $cart_collection = $cart_collection->map(function ($cart_item) use ($cart_collection_products) {
                    $matched_cart_collection_products = $cart_collection_products->filter(function ($cart_collection_product) use ($cart_item) {
                        return $cart_collection_product->id == $cart_item->id;
                    });

                    if ($matched_cart_collection_products->count() > 0) {
                        $matched_cart_collection_product = $matched_cart_collection_products->first();

                        if ($matched_cart_collection_product) {
                            $matched_cart_collection_product_attributes = [];

                            if ($matched_cart_collection_product->attributes->count() > 0) {
                                $matched_cart_collection_product_attributes = array_merge($matched_cart_collection_product_attributes, array_map(function ($matched_cart_collection_product_attribute) use ($cart_item) {
                                    if ('text' == $matched_cart_collection_product_attribute['value']) {
                                        $matched_cart_collection_product_attribute['value_label'] = null;
                                        if ($cart_item->options->get($matched_cart_collection_product_attribute['name'])) {
                                            $matched_cart_collection_product_attribute['value_label'] = $cart_item->options->get($matched_cart_collection_product_attribute['name']);
                                        }
                                    }

                                    return [
                                        'name' => $matched_cart_collection_product_attribute['name'],
                                        'name_label' => $matched_cart_collection_product_attribute['name_label'],
                                        'value' => $matched_cart_collection_product_attribute['value'],
                                        'value_label' => $matched_cart_collection_product_attribute['value_label'],
                                    ];
                                }, $matched_cart_collection_product->attributes->append(['name', 'name_label'])->toArray()));
                            }

                            if (!empty($matched_cart_collection_product->parent)) {
                                if ($matched_cart_collection_product->parent->attributes->count() > 0) {
                                    $matched_cart_collection_product_attributes = array_merge($matched_cart_collection_product_attributes, array_map(function ($matched_cart_collection_product_attribute) use ($cart_item) {
                                        if ('text' == $matched_cart_collection_product_attribute['value']) {
                                            $matched_cart_collection_product_attribute['value_label'] = null;
                                            if ($cart_item->options->get($matched_cart_collection_product_attribute['name'])) {
                                                $matched_cart_collection_product_attribute['value_label'] = $cart_item->options->get($matched_cart_collection_product_attribute['name']);
                                            }
                                        }

                                        return [
                                            'name' => $matched_cart_collection_product_attribute['name'],
                                            'name_label' => $matched_cart_collection_product_attribute['name_label'],
                                            'value' => $matched_cart_collection_product_attribute['value'],
                                            'value_label' => $matched_cart_collection_product_attribute['value_label'],
                                        ];
                                    }, $matched_cart_collection_product->parent->attributes->append(['name', 'name_label'])->toArray()));
                                }
                            }

                            $cart_item->custom_price = isset($cart_item->custom_price) ? round($cart_item->custom_price, 2) : null;
                            $cart_item->display_price = round($cart_item->price + $cart_item->tax, 2);
                            $cart_item->display_subtotal = round($cart_item->display_price * $cart_item->qty, 2);
                            $cart_item->total = round($cart_item->display_price * $cart_item->qty, 2);

                            if ($cart_item->options->get('customisation_id') && \Customisations::has($cart_item->options->get('customisation_id'))) {
                            }

                            $cart_item_formatted = [
                                'sort_order' => $matched_cart_collection_product->sort_order,
                                'name' => $matched_cart_collection_product->name_label,
                                'url' => ($cart_item->options->get('customisation_id') && \Customisations::has($cart_item->options->get('customisation_id'))) ? route('shop.product.edit', ['slug' => $matched_cart_collection_product->slug, 'customisation' => $cart_item->options->get('customisation_id')]) : route('shop.product.show', $matched_cart_collection_product->slug),
                                'price' => Formatter::currency($cart_item->price),
                                'subtotal' => Formatter::currency($cart_item->subtotal),
                                'tax' => Formatter::currency($cart_item->tax),
                                'custom_price' => null,
                                'display_price' => Formatter::currency($cart_item->display_price),
                                'display_subtotal' => Formatter::currency($cart_item->display_subtotal),
                                'total' => Formatter::currency($cart_item->total),
                                'attributes' => $matched_cart_collection_product_attributes,
                                'images' => $matched_cart_collection_product->images,
                                'builder_images' => data_get($matched_cart_collection_product, 'configuration.builder_images', []),
                            ];

                            if (null !== $cart_item->custom_price) {
                                $cart_item_formatted['custom_price'] = Formatter::currency($cart_item->custom_price);
                            }

                            if (!empty($matched_cart_collection_product->parent)) {
                                $cart_item_formatted['name'] = $matched_cart_collection_product->parent->name_label;
                                $cart_item_formatted['url'] = ($cart_item->options->get('customisation_id') && \Customisations::has($cart_item->options->get('customisation_id'))) ? route('shop.product.edit', ['slug' => $matched_cart_collection_product->parent->slug, 'customisation' => $cart_item->options->get('customisation_id')]) : route('shop.product.show', $matched_cart_collection_product->parent->slug);
                            }

                            $cart_item->formatted = (object) $cart_item_formatted;
                            $cart_item->product = $matched_cart_collection_product;
                        }
                    } else {
                        $cart_item->custom_price = isset($cart_item->custom_price) ? round($cart_item->custom_price, 2) : null;
                        $cart_item->display_price = round($cart_item->price + $cart_item->tax, 2);
                        $cart_item->display_subtotal = round($cart_item->display_price * $cart_item->qty, 2);

                        $cart_item_formatted = [
                            'sort_order' => 0,
                            'name' => $cart_item->name,
                            'url' => null,
                            'price' => Formatter::currency($cart_item->price),
                            'subtotal' => Formatter::currency($cart_item->subtotal),
                            'tax' => Formatter::currency($cart_item->tax),
                            'custom_price' => null,
                            'display_price' => Formatter::currency($cart_item->display_price),
                            'display_subtotal' => Formatter::currency($cart_item->display_subtotal),
                            'attributes' => [],
                            'images' => [],
                            'builder_images' => [],
                        ];

                        if (null !== $cart_item->custom_price) {
                            $cart_item_formatted['custom_price'] = Formatter::currency($cart_item->custom_price);
                        }

                        $cart_item->formatted = (object) $cart_item_formatted;
                    }

                    return $cart_item;
                });
            }

            $cart_collection = $cart_collection->sortBy('formatted.sort_order')->sortBy('formatted.name');
        }

        return $cart_collection;
    }

    public static function groupItems($cart_items)
    {
        $grouped_cart_items = collect([]);
        if ($cart_items->count() > 0) {
            $cart_items->each(function ($cart_item) use (&$grouped_cart_items) {
                if (
                    'product' == $cart_item->type
                    && $cart_item->product
                    && $cart_item->product->parent
                    && isset($cart_item->product->parent->configuration['is_multi_select'])
                    && true === $cart_item->product->parent->configuration['is_multi_select']
                ) {
                    $existing_grouped_cart_items = $grouped_cart_items->filter(function ($grouped_cart_item) use ($cart_item) {
                        $generated_cart_item_id = self::generateSerialisedOptionId($cart_item->product->parent->id, $cart_item->options->toArray());
                        $grouped_cart_item_options = $grouped_cart_item->options->toArray();
                        $generated_grouped_cart_item_id = self::generateSerialisedOptionId($cart_item->product->parent->id, $grouped_cart_item_options);
                        if ($generated_cart_item_id == $generated_grouped_cart_item_id) {
                            return true;
                        }

                        return false;
                    });

                    if (0 === $existing_grouped_cart_items->count()) {
                        $cart_item_qty = 0;
                        $multiselect_sizes = [];

                        if ($cart_item->product->parent->products->count() > 0) {
                            $cart_item_products = $cart_item->product->parent->products->sortBy('sort_order')->filter(function ($product) use ($cart_item) {
                                if ($product->attributes->count() > 0) {
                                    foreach ($product->attributes as $attribute) {
                                        if (
                                            isset($cart_item->formatted->attributes[0])
                                            && $cart_item->formatted->attributes[0]['name'] == $attribute['name']
                                            && $cart_item->formatted->attributes[0]['value'] == $attribute['value']
                                        ) {
                                            return true;
                                        }
                                    }
                                }

                                return false;
                            });

                            if ($cart_item_products->count() > 0) {
                                $cart_item_products->each(function ($product) use ($cart_item, &$cart_item_qty, &$multiselect_sizes) {
                                    if ($cart_item->id == $product->id) {
                                        $cart_item_qty = intval($cart_item_qty) + intval($cart_item->qty);
                                    }

                                    $product_attributes = [];

                                    if ($product->attributes->count() > 0) {
                                        $product_attributes = array_merge($product_attributes, array_map(function ($product_attribute) use ($cart_item) {
                                            if ('text' == $product_attribute['value']) {
                                                $product_attribute['value_label'] = null;
                                                if ($cart_item->options->get($product_attribute['name'])) {
                                                    $product_attribute['value_label'] = $cart_item->options->get($product_attribute['name']);
                                                }
                                            }

                                            return [
                                                'name' => $product_attribute['name'],
                                                'name_label' => $product_attribute['name_label'],
                                                'value' => $product_attribute['value'],
                                                'value_label' => $product_attribute['value_label'],
                                            ];
                                        }, $product->attributes->append(['name', 'name_label'])->toArray()));
                                    }

                                    $multiselect_size = [
                                        'rowId' => $cart_item->id == $product->id ? $cart_item->rowId : null,
                                        'id' => $product->id,
                                        'is_enabled' => $product->is_enabled,
                                        'qty' => $cart_item->id == $product->id ? $cart_item->qty : 0,
                                        'formatted' => [
                                            'attributes' => $product_attributes,
                                        ],
                                    ];

                                    $multiselect_sizes[] = $multiselect_size;
                                });
                            }
                        }

                        $grouped_cart_item_data = [
                            'id' => $cart_item->product->parent->id,
                            'name' => $cart_item->product->parent->name_label,
                            'price' => $cart_item->price,
                            'type' => 'product',
                            'weight' => 0,
                            'options' => $cart_item->options->toArray(),
                            'attributes' => array_merge([
                                'multi_select' => true,
                                'sizes' => $multiselect_sizes,
                            ], $cart_item->attributes->toArray()),
                        ];

                        if ($cart_item->attributes->count() > 0) {
                            $grouped_cart_item_data['attributes'] = array_merge($grouped_cart_item_data['attributes'], $cart_item->attributes->toArray());
                        }

                        $grouped_cart_item = CartItem::fromArray($grouped_cart_item_data)->associate(Product::class);

                        $grouped_cart_item->setQuantity(intval($cart_item_qty));
                        if ($cart_item->product->configuration && isset($cart_item->product->configuration['tax_percentage'])) {
                            $grouped_cart_item->setTaxRate($cart_item->product->configuration['tax_percentage']);
                        }

                        $matched_cart_collection_parent_product_attributes = [];

                        if (!empty($cart_item->product->parent)) {
                            if ($cart_item->product->parent->attributes->count() > 0) {
                                $matched_cart_collection_parent_product_attributes = array_map(function ($matched_cart_collection_product_attribute) use ($cart_item) {
                                    if ('text' == $matched_cart_collection_product_attribute['value']) {
                                        $matched_cart_collection_product_attribute['value_label'] = null;
                                        if ($cart_item->options->get($matched_cart_collection_product_attribute['name'])) {
                                            $matched_cart_collection_product_attribute['value_label'] = $cart_item->options->get($matched_cart_collection_product_attribute['name']);
                                        }
                                    }

                                    return [
                                        'name' => $matched_cart_collection_product_attribute['name'],
                                        'name_label' => $matched_cart_collection_product_attribute['name_label'],
                                        'value' => $matched_cart_collection_product_attribute['value'],
                                        'value_label' => $matched_cart_collection_product_attribute['value_label'],
                                    ];
                                }, $cart_item->product->parent->attributes->toArray());
                            }
                        }

                        if (!empty($cart_item->product)) {
                            if ($cart_item->product->attributes->count() > 0) {
                                $matched_cart_collection_product_attributes = array_map(function ($matched_cart_collection_product_attribute) use ($cart_item) {
                                    if ('text' == $matched_cart_collection_product_attribute['value']) {
                                        $matched_cart_collection_product_attribute['value_label'] = null;
                                        if ($cart_item->options->get($matched_cart_collection_product_attribute['name'])) {
                                            $matched_cart_collection_product_attribute['value_label'] = $cart_item->options->get($matched_cart_collection_product_attribute['name']);
                                        }
                                    }

                                    return [
                                        'name' => $matched_cart_collection_product_attribute['name'],
                                        'name_label' => $matched_cart_collection_product_attribute['name_label'],
                                        'value' => $matched_cart_collection_product_attribute['value'],
                                        'value_label' => $matched_cart_collection_product_attribute['value_label'],
                                    ];
                                }, $cart_item->product->attributes->toArray());
                                $matched_cart_collection_parent_product_attributes = array_merge($matched_cart_collection_parent_product_attributes, $matched_cart_collection_product_attributes);
                            }
                        }

                        $grouped_cart_item->custom_price = isset($cart_item->custom_price) ? round($cart_item->custom_price, 2) : null;
                        $grouped_cart_item->display_price = round($grouped_cart_item->price + $grouped_cart_item->tax, 2);
                        $grouped_cart_item->display_subtotal = round($grouped_cart_item->display_price * $grouped_cart_item->qty, 2);

                        $customisations = [];
                        $cart_item_customisations = $cart_item->options->get('customisations');
                        if ($cart_item_customisations && !empty($cart_item_customisations)) {
                            $customisation_count = 1;
                            foreach ($cart_item_customisations as $cart_item_customisation_name => $cart_item_customisation) {
                                if (false === $cart_item_customisation['selected']) {
                                    continue;
                                }
                                $customisations[] = [
                                    'title' => Formatter::ordinal($customisation_count).' Location',
                                    'label' => $cart_item_customisation['label'].' '.$cart_item_customisation['position'],
                                    'web' => round($cart_item_customisation['dimensions']['w'], 0).'px/'.round($cart_item_customisation['dimensions']['h'], 0).'px',
                                    'print' => round($cart_item_customisation['dimensions']['w'], 0).'mm/'.round($cart_item_customisation['dimensions']['h'], 0).'mm',
                                    'preview_base64' => isset($cart_item_customisation['preview']) ? $cart_item_customisation['preview'] : null,
                                    'image' => isset($cart_item->product->configuration['builder_images']) && isset($cart_item->product->configuration['builder_images'][$cart_item_customisation_name.'_image']) ? $cart_item->product->configuration['builder_images'][$cart_item_customisation_name.'_image'] : null,
                                ];
                                ++$customisation_count;
                            }
                        }

                        $grouped_cart_item_formatted = [
                            'sort_order' => $cart_item->product->sort_order,
                            'name' => $cart_item->product->parent->name_label,
                            'url' => ($cart_item->options->get('customisation_id') && \Customisations::has($cart_item->options->get('customisation_id'))) ? route('shop.product.edit', ['slug' => $cart_item->product->parent->slug, 'customisation' => $cart_item->options->get('customisation_id')]) : route('shop.product.show', $cart_item->product->parent->slug),
                            'price' => Formatter::currency($grouped_cart_item->price),
                            'subtotal' => Formatter::currency($grouped_cart_item->subtotal),
                            'tax' => Formatter::currency($grouped_cart_item->tax),
                            'custom_price' => null,
                            'display_price' => Formatter::currency($grouped_cart_item->display_price),
                            'display_subtotal' => Formatter::currency($grouped_cart_item->display_subtotal),
                            'subtotal' => Formatter::currency($grouped_cart_item->subtotal),
                            'attributes' => $matched_cart_collection_parent_product_attributes,
                            'images' => $cart_item->product->images,
                            'builder_images' => data_get($cart_item->product, 'configuration.builder_images', []),
                        ];

                        if (null !== $grouped_cart_item->custom_price) {
                            $grouped_cart_item_formatted['custom_price'] = Formatter::currency($grouped_cart_item->custom_price);
                        }

                        $grouped_cart_item->formatted = (object) $grouped_cart_item_formatted;

                        $grouped_cart_item->product = $cart_item->product->parent;

                        $grouped_cart_items->put($grouped_cart_item->rowId, $grouped_cart_item);
                    } else {
                        $existing_grouped_cart_item = $existing_grouped_cart_items->first();
                        $existing_grouped_cart_item_attributes = $existing_grouped_cart_item->attributes;
                        $existing_grouped_cart_item_attributes_sizes = $existing_grouped_cart_item_attributes->sizes;

                        if (!empty($existing_grouped_cart_item_attributes_sizes)) {
                            foreach ($existing_grouped_cart_item_attributes_sizes as &$multiselect_size) {
                                if ($multiselect_size['id'] == $cart_item->id) {
                                    $multiselect_size['rowId'] = $cart_item->rowId;
                                    $multiselect_size['qty'] = intval($cart_item->qty);

                                    $existing_grouped_cart_item->setQuantity(intval($existing_grouped_cart_item->qty + $cart_item->qty));

                                    $existing_grouped_cart_item->display_subtotal = round($existing_grouped_cart_item->display_price * $existing_grouped_cart_item->qty, 2);

                                    $existing_grouped_cart_item->formatted->display_subtotal = Formatter::currency($existing_grouped_cart_item->display_subtotal);
                                }
                            }
                            unset($multiselect_size);
                        }

                        $existing_grouped_cart_item_data = [
                            'attributes' => array_merge($existing_grouped_cart_item_attributes->toArray(), [
                                'sizes' => $existing_grouped_cart_item_attributes_sizes,
                            ]),
                        ];

                        $existing_grouped_cart_item->updateFromArray($existing_grouped_cart_item_data);
                    }
                } else {
                    $grouped_cart_items->put($cart_item->rowId, $cart_item);
                }
            });

            $grouped_cart_items = $grouped_cart_items->sortBy('formatted.sort_order')->sortBy('formatted.name');
        }

        return $grouped_cart_items;
    }

    public static function cart($options = [])
    {
        if (isset($options['instance'])) {
            \Cart::instance($options['instance']);
        }

        if (!isset($options['show_unavailable'])) {
            $options['show_unavailable'] = true;
        }

        $cart_items = self::cartWithProducts($options);

        $product_items = $cart_items->filter(function ($item) {
            return 'product' == $item->type;
        });
        $product_count = $product_items->sum('qty');
        $available_product_items = $product_items->filter(fn ($item) => $item->isAvailable);
        $available_product_count = $available_product_items->sum('qty');
        $product_subtotal = $available_product_items->reduce(function ($total, $item) {
            return $total + ($item->qty * $item->price);
        }, 0);
        $product_weight = $available_product_items->reduce(function ($weight, $item) {
            return $weight + ($item->qty * $item->weight);
        });

        $shipping_data = self::computeShipping($available_product_count, $product_subtotal, $product_weight, $cart_items);

        $grouped_cart_items = self::groupItems($cart_items);
        $grouped_product_items = $grouped_cart_items->filter(function ($item) {
            return 'product' == $item->type;
        });

        $cart_customisations = self::cartCustomisations($options);

        $discount_code = null;
        if (floatval(\Cart::discount()) > 0) {
            $discount_code = session('cart_discount_code.'.\Cart::currentInstance());
        }

        return (object) [
            'items' => $cart_items,
            'count' => intval(\Cart::count()),
            'subtotal' => floatval(\Cart::subtotal()),
            'product_items' => $product_items,
            'available_product_count' => $available_product_count,
            'product_count' => $product_count,
            'product_subtotal' => $product_subtotal,
            'shipping_items' => $shipping_data['items'],
            'shipping_count' => $shipping_data['count'],
            'shipping_subtotal' => $shipping_data['subtotal'],
            'discount_code' => $discount_code,
            'discount' => floatval(\Cart::discount()),
            'tax' => floatval(\Cart::tax()),
            'total' => floatval(\Cart::total()),
            'formatted' => [
                'subtotal' => Formatter::currency(\Cart::subtotal()),
                'product_subtotal' => Formatter::currency($product_subtotal),
                'shipping_subtotal' => Formatter::currency($shipping_data['subtotal']),
                'discount' => Formatter::currency(\Cart::discount()),
                'tax' => Formatter::currency(\Cart::tax()),
                'total' => Formatter::currency(\Cart::total()),
            ],
            'grouped' => (object) [
                'items' => $grouped_cart_items,
                'product_items' => $grouped_product_items,
            ],
            'customisations' => $cart_customisations,
        ];
    }

    protected static function generateSerialisedOptionId($id, array $options)
    {
        ksort($options);

        return md5($id.serialize($options));
    }

    private static function computeShipping($product_count, $product_subtotal, $product_weight, $cart_items)
    {
        $shipping_items = $cart_items->filter(function ($item) {
            return 'shipping' == $item->type;
        });
        $shipping_calculator = new ShippingCalculatorService();
        $shipping_items->each(function (&$item) use ($shipping_calculator, $product_count, $product_subtotal, $product_weight) {
            $item->price = $shipping_calculator->updateMethodPrice($item->options->method_code, $item->options->shipping_country, $product_count, $product_subtotal, $product_weight);
        });
        $shipping_count = $shipping_items->sum('qty');
        $shipping_subtotal = $shipping_items->reduce(function ($total, $item) {
            return $total + ($item->qty * $item->price);
        }, 0);

        return [
            'items' => $shipping_items,
            'count' => $shipping_count,
            'subtotal' => $shipping_subtotal,
        ];
    }
}
