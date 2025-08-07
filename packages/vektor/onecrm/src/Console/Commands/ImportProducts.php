<?php

namespace Vektor\OneCRM\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Vektor\OneCRM\Models\WrapperProductCatalog;
use Vektor\Shop\Models\Attribute;
use Vektor\Shop\Models\Product;
use Vektor\Shop\Models\ProductAttribute;

class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onecrm:import_products 
                                {--f|force_update : Update regardless of which record was updated most recently} 
                                {--ignore_children : Only update the exact sku given, ignoring any children} 
                                {--ignore_attributes : Ignore attribute updates, they can be slow so disable if not needed }
                                {product_sku? : Sku to update - matches using contains (eg JH001 will also match JH001AWTXS)}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports products from OneCRM';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getCategoryParents($onecrm_category_id)
    {
        $_product_catalog = new WrapperProductCatalog();

        return $_product_catalog->parentSkus($onecrm_category_id);
    }

    public function getCategoryProducts($onecrm_category_id, $product_sku = null, $ignore_children = false, $ignore_attributes = false)
    {
        $_product_catalog = new WrapperProductCatalog();

        $_product_catalog_response = $_product_catalog->index($onecrm_category_id, $product_sku, $ignore_children, $ignore_attributes);

        if (!empty($_product_catalog_response)) {
            foreach ($_product_catalog_response as $_product) {
                try {
                    $product = null;
                    if (isset($_product['configuration'], $_product['configuration']['onecrm_id'])) {
                        $product = Product::whereJsonContains('configuration->onecrm_id', $_product['configuration']['onecrm_id'])->first();
                    }
                    if ($product) {
                        if ($_product['modified_at'] > $product['updated_at'] || $this->option('force_update')) {
                            $product->update($_product);
                            $this->createProductAttributes($_product, $product);
                        }
                    } elseif (isset($_product['name'])) {
                        try {
                            $product = Product::create($_product);
                            $this->createProductAttributes($_product, $product);
                        } catch (\Exception $e) {
                            throw $e;
                        }
                    }

                    if (isset($_product['products']) && !empty($_product['products'])) {
                        foreach ($_product['products'] as $_product_inner) {
                            $product_inner = null;
                            if (isset($_product_inner['configuration'], $_product_inner['configuration']['onecrm_id'])) {
                                $product_inner = Product::whereJsonContains('configuration->onecrm_id', $_product_inner['configuration']['onecrm_id'])->first();
                            }
                            if ($product_inner) {
                                $product_inner->update($_product_inner);
                                $this->createProductAttributes($_product_inner, $product_inner);
                            } else {
                                $product_inner = Product::create($_product_inner);
                                $product_inner->parent()->associate($product);
                                $product_inner->save();
                                $this->createProductAttributes($_product_inner, $product_inner);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    echo '<pre>';
                    var_dump($e->getMessage());
                    echo '</pre>';
                    echo '<pre>';
                    var_dump($_product);
                    echo '</pre>';

                    exit;
                }
            }
        }
    }

    public function categorizeColor($hexColor)
    {
        if (!$hexColor) {
            return null;
        }

        if (strlen($hexColor) > 7) {
            return null;
        }

        $hexColor = ltrim($hexColor, '#');

        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));

        if ($r > 200 && $g > 200 && $b > 200) {
            return ['name' => 'White', 'color' => '#EEEEEE'];
        }
        if ($r < 50 && $g < 50 && $b < 50) {
            return ['name' => 'Black', 'color' => '#333333'];
        }
        if ($r > 200 && $g > 200 && $b < 100) {
            return ['name' => 'Yellow', 'color' => '#FFFF1A'];
        }
        if ($r > $g && $r > $b) {
            if (abs($r - $g) < 40) {
                return ['name' => 'Orange', 'color' => '#FF8B00'];
            }
            if (abs($r - $b) > 80 && $g < 150) {
                return ['name' => 'Red', 'color' => '#F52400'];
            }

            return ['name' => 'Pink', 'color' => '#FF37C8'];
        }
        if ($g > $r && $g > $b) {
            return ['name' => 'Green', 'color' => '#09A505'];
        }
        if ($b > $r && $b > $g) {
            if ($r > $g && abs($b - $r) < 60) {
                return ['name' => 'Purple', 'color' => '#7E266A'];
            }

            return ['name' => 'Blue', 'color' => '#3374DD'];
        }
        if ($r > 100 && $g < 80 && $b < 80) {
            return ['name' => 'Brown', 'color' => '#69462E'];
        }

        return null;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::all();
        $onecrm_category_ids = [
            config('onecrm.product_category_id'),
        ];

        if ($users->count() > 0) {
            foreach ($users as $user) {
                if (
                    $user
                    && isset($user->configuration, $user->configuration['onecrm_product_category_id'])

                    && !empty($user->configuration['onecrm_product_category_id'])
                ) {
                    $onecrm_category_ids[] = $user->configuration['onecrm_product_category_id'];
                }
            }
        }

        $onecrm_category_ids = array_unique($onecrm_category_ids);

        if (!empty($onecrm_category_ids)) {
            foreach ($onecrm_category_ids as $onecrm_category_id) {
                if ($this->argument('product_sku')) {
                    $this->getCategoryProducts($onecrm_category_id, $this->argument('product_sku') ?? null, $this->option('ignore_children'), $this->option('ignore_attributes'));
                } else {
                    $parents = $this->getCategoryParents($onecrm_category_id);
                    $count = count($parents);
                    foreach ($parents as $idx => $parent) {
                        ++$idx;
                        echo "Processing parent {$idx} of {$count}: {$parent}\n";
                        $this->getCategoryProducts($onecrm_category_id, $parent, $this->option('ignore_children'), $this->option('ignore_attributes'));
                    }
                }
            }
        }

        $colour_attributes = Attribute::with('attributes')->whereIn('name', [
            'color',
            'colour',
        ])->get();
        if ($colour_attributes->count() > 0) {
            $colour_group_attribute = Attribute::where('name', 'color_group')->first();
            if (!$colour_group_attribute) {
                $colour_group_attribute = Attribute::create([
                    'name' => 'color_group',
                    'name_label' => 'Colour',
                    'configuration' => [
                        'is_swatch' => false,
                    ],
                ]);
            }
            foreach ($colour_attributes as $attribute) {
                if ($attribute->attributes->count() > 0) {
                    $attribute_attributes = $attribute->attributes;

                    foreach ($attribute_attributes as $attribute_attribute) {
                        if (!empty($attribute_attribute->configuration) && !empty($attribute_attribute->configuration['color'])) {
                            $existing_colour_group_attribute = ProductAttribute::where('product_id', $attribute_attribute->product_id)->where('attribute_id', $colour_group_attribute->id)->first();

                            if (!$existing_colour_group_attribute) {
                                $color_category = $this->categorizeColor($attribute_attribute->configuration['color']);

                                if ($color_category) {
                                    ProductAttribute::create([
                                        'product_id' => $attribute_attribute->product_id,
                                        'attribute_id' => $colour_group_attribute->id,
                                        'value' => $color_category['name'],
                                        'value_label' => strtolower(str_replace(' ', '', $color_category['name'])),
                                        'configuration' => [
                                            'color' => $color_category['color'],
                                        ],
                                        'sort_order' => 0,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        $attributes = Attribute::with('attributes')->whereIn('name', [
            'brand',
            'color',
            'colour',
            'color_group',
            'gender',
            'product_type',
            'production_process',
        ])->get();
        if ($attributes->count() > 0) {
            foreach ($attributes as $attribute) {
                $sort_order = 0;
                if ($attribute->attributes->count() > 0) {
                    $attribute_attributes = $attribute->attributes;
                    $attribute_attributes_sorted = $attribute_attributes->sortBy('value_label');
                    foreach ($attribute_attributes_sorted as $item) {
                        $item->sort_order = $sort_order;
                        ++$sort_order;
                    }

                    $attribute_attributes_sorted->each->save();
                }
            }
        }
    }

    private function createProductAttributes($_product, $product)
    {
        if (!$this->option('ignore_attributes')) {
            ProductAttribute::where('product_id', $product->id)->delete();

            if (!empty($_product['attributes'])) {
                foreach ($_product['attributes'] as $attribute) {
                    $_attribute = Attribute::firstOrCreate(
                        [
                            'name' => $attribute['name'],
                        ],
                        [
                            'name' => $attribute['name'],
                            'name_label' => $attribute['name_label'],
                            'configuration' => in_array($attribute['name'], ['color', 'colour', 'size']) ? [
                                'is_swatch' => true,
                            ] : [
                                'is_swatch' => false,
                            ],
                        ]
                    );

                $existing_product_attribute = ProductAttribute::where('product_id', $product->id)->where('attribute_id', $_attribute->id)->first();

                if (null === $existing_product_attribute) {
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $_attribute->id,
                        'value' => $attribute['value'],
                        'value_label' => $attribute['value_label'],
                        'configuration' => $attribute['configuration'] ?? null,
                        'sort_order' => isset($product->sort_order) ? $product->sort_order : 0,
                    ]);
                }
            }
        }
    }
}
