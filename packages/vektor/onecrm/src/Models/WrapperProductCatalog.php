<?php

namespace Vektor\OneCRM\Models;

use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;
use Vektor\Shop\Utilities;

class WrapperProductCatalog
{
    public $crm;

    public $crm_model;

    public $_tax_code;

    public $_product;

    public $_product_category;

    public function __construct()
    {
        $this->crm = new OneCRM();
        $this->crm_model = new OneCRMModel();

        $this->_tax_code = new TaxCode();
        $this->_product = new Product();
        $this->_product_category = new ProductCategory();

        return $this;
    }

    public function iso8859_1_to_utf8(string $s): string
    {
        $s .= $s;
        $len = \strlen($s);

        for ($i = $len >> 1, $j = 0; $i < $len; ++$i, ++$j) {
            switch (true) {
                case $s[$i] < "\x80": $s[$j] = $s[$i];

                    break;

                case $s[$i] < "\xC0": $s[$j] = "\xC2";
                    $s[++$j] = $s[$i];

                    break;

                default: $s[$j] = "\xC3";
                    $s[++$j] = \chr(\ord($s[$i]) - 64);

                    break;
            }
        }

        return substr($s, 0, $j);
    }

    public function fetchProductAttributes($_product, $ignore_attributes = false)
    {
        $_attributes = [];

        if (!$ignore_attributes) {
            if (isset($_product['id'])) {
                $_product_attributes = $this->_product->index_related_productattributes($_product['id']);

                if (!empty($_product_attributes)) {
                    foreach ($_product_attributes as $_product_attribute) {
                        $_product_attribute['name'] = trim($_product_attribute['name']);
                        $_product_attribute['value'] = trim($_product_attribute['value']);
                        $_attribute = [
                            'name' => strtolower($_product_attribute['name']),
                            'name_label' => $_product_attribute['name'],
                            'value' => strtolower(preg_replace('/[\s]/', '', $_product_attribute['value'])),
                            'value_label' => $_product_attribute['value'],
                            'configuration' => [
                                'onecrm_id' => $_product_attribute['id'],
                            ],
                        ];

                        if (isset($_product_attribute['hex_code']) && !empty($_product_attribute['hex_code'])) {
                            $_attribute['configuration']['color'] = trim($_product_attribute['hex_code']);
                        }

                        if (isset($_product_attribute['required']) && !empty($_product_attribute['required'])) {
                            $_attribute['configuration']['required'] = 1 == $_product_attribute['required'] ? true : false;
                        }

                        if (isset($_product_attribute['price']) && !empty($_product_attribute['price'])) {
                            $_attribute['configuration']['price'] = $_product_attribute['price'];
                        }

                        $_attributes[strtolower($_product_attribute['name'])] = $_attribute;
                    }
                }
            }

            if (!empty($_product['production_process'])) {
                foreach (explode('^,^', $_product['production_process']) as $attribute_value) {
                    $_attributes[] = [
                        'name' => 'production_process',
                        'name_label' => 'Production Process',
                        'value' => strtolower(preg_replace('/[\s]/', '_', trim($attribute_value))),
                        'value_label' => trim($attribute_value),
                    ];
                }
            }

            if (!empty($_product['product_types'])) {
                foreach (explode('^,^', $_product['product_types']) as $attribute_value) {
                    $_attributes[] = [
                        'name' => 'product_type',
                        'name_label' => 'Product Type',
                        'value' => strtolower(preg_replace('/[\s]/', '_', trim($attribute_value))),
                        'value_label' => trim($attribute_value),
                    ];
                }
            }

            if (!empty($_product['age_groups'])) {
                foreach (explode('^,^', $_product['age_groups']) as $attribute_value) {
                    $_attributes[] = [
                        'name' => 'age_group',
                        'name_label' => 'Age Group',
                        'value' => strtolower(preg_replace('/[\s]/', '_', trim($attribute_value))),
                        'value_label' => trim($attribute_value),
                    ];
                }
            }

            if (!empty($_product['brand'])) {
                $_attributes[] = [
                    'name' => 'brand',
                    'name_label' => 'Brand',
                    'value' => strtolower(preg_replace('/[\s\/]/', '_', trim($_product['brand']))),
                    'value_label' => trim($_product['brand']),
                ];
            }

            if (!empty($_product['gender'])) {
                $_attributes[] = [
                    'name' => 'gender',
                    'name_label' => 'Gender',
                    'value' => strtolower(preg_replace('/[\s]/', '_', trim($_product['gender']))),
                    'value_label' => trim($_product['gender']),
                ];
            }

            if (!empty($_attributes)) {
                ksort($_attributes);
                $_attributes = array_values($_attributes);
            }
        }

        return $_attributes;
    }

    public function extractBuilderConfig($product, $image_string)
    {
        $config = [];
        for ($i = 1; $i < 3; ++$i) {
            if (empty($product['parent_product_id'])) {
                if (isset($product['print_side_'.$i.'_enabled']) && $product['print_side_'.$i.'_enabled']) {
                    $config['side_'.$i] = [
                        'disabled' => false,
                        'label' => $product['print_side_'.$i.'_name'] ?? 'Side 1',
                        'copy_to' => $product['print_side_'.$i.'_copy_to'] ?? null,
                    ];
                    if (isset($product['print_side_'.$i.'_dimensions'])) {
                        $config['side_'.$i] = array_merge($config['side_'.$i], json_decode($product['print_side_'.$i.'_dimensions'], true));
                    }
                }
            } else {
                if (isset($product['print_side_'.$i.'_image']) && '' != $product['print_side_'.$i.'_image']) {
                    $config['side_'.$i.'_image'] = $product['print_side_'.$i.'_image'];
                }
            }
        }

        return $config;
    }

    public function fetchProducts($category_id, $product_sku = null, $ignore_children = false, $page = 1, $per_page = 50)
    {
        $category_products = [];

        $fields = [
            'accreditations',
            'age_groups',
            'brand',
            'cost',
            'credit_value',
            'customisable',
            'date_modified',
            'description',
            'gender',
            'image_url',
            'img_url',
            'is_available',
            'launch_date',
            'manufacturers_part_no',
            'multi_select',
            'name',
            'parent_product_id',
            'price',
            'print_side_1_copy_to',
            'print_side_1_dimensions',
            'print_side_1_enabled',
            'print_side_1_image',
            'print_side_1_name',
            'print_side_2_copy_to',
            'print_side_2_dimensions',
            'print_side_2_enabled',
            'print_side_2_image',
            'print_side_2_name',
            'product_category_id',
            'product_class',
            'product_types',
            'production_process',
            'purchase_from',
            'purchase_price',
            'qty_per_order',
            'qty_per_order_grouping',
            'shipping',
            'size_guide',
            'size_guide_note',
            'sort_order',
            'tax_code_id',
            'vendor_part_no',
            'weight_1',
            'weight_kg',
        ];

        if (isset($product_sku)) {
            $_category_products = $this->_product->index([
                'fields' => $fields,
                'filters' => [
                    'product_category_id' => $category_id,
                    'manufacturers_part_no' => $product_sku,
                ],
                'per_page' => $per_page,
                'page' => $page,
            ]);
            if ($ignore_children) {
                $_category_products = array_values(array_filter($_category_products, function ($product) use ($product_sku) {
                    return $product['manufacturers_part_no'] == $product_sku;
                }));
            }
        } else {
            $_category_products = $this->_product_category->index_related_products($category_id, [
                'fields' => $fields,
                'per_page' => $per_page,
                'page' => $page,
            ]);
        }

        if (!empty($_category_products)) {
            $category_products = array_merge($category_products, $_category_products);
        }

        return $category_products;
    }

    public function fetchParents($category_id, $page = 1, $per_page = 50)
    {
        $category_products = [];

        $fields = [
            'manufacturers_part_no',
            'parent_product_id',
            'product_category_id',
        ];

        return $this->_product_category->index_related_products($category_id, [
            'fields' => $fields,
            'per_page' => $per_page,
            'page' => $page,
        ]);
    }

    public function parentSkus($category_id)
    {
        $page = 1;
        $per_page = 200;
        $parent_skus = [];
        echo "Fetching parents from CRM\n";
        do {
            $_category_products = $this->fetchParents($category_id, $page, $per_page);
            $skus = array_filter(array_map(function ($_category_product) {
                if (null === $_category_product['parent_product_id']) {
                    return $_category_product['manufacturers_part_no'];
                }
            }, $_category_products));
            $parent_skus = array_merge($parent_skus, $skus);
            ++$page;
        } while (!empty($_category_products) && count($_category_products) >= $per_page);
        echo "Parents fetched from CRM\n";

        return $parent_skus;
    }

    public function index($category_id, $product_sku = null, $ignore_children = false, $ignore_attributes = false)
    {
        $page = 1;
        $per_page = 50;
        $category_products = [];

        echo "Importing from CRM\n";
        do {
            $_category_products = $this->fetchProducts($category_id, $product_sku, $ignore_children, $page, $per_page);
            $category_products = array_merge($category_products, $this->transformProductCategoryRelatedProducts($_category_products, $category_id, $ignore_attributes));
            ++$page;
        } while (!empty($_category_products) && count($_category_products) >= $per_page);
        echo 'Processed '.$page." pages\n";

        $hierarchical_category_products = [];
        echo "Creating product hierarchy\n";
        if (!empty($category_products)) {
            foreach ($category_products as $category_product_key => $category_product) {
                if (
                    isset($category_product['configuration'])
                    && array_key_exists('onecrm_parent_id', $category_product['configuration'])
                    && null === $category_product['configuration']['onecrm_parent_id']
                    && isset($category_product['configuration']['onecrm_id'])
                ) {
                    $hierarchical_category_products[$category_product['configuration']['onecrm_id']] = $category_product;
                    unset($category_products[$category_product_key]);
                }
            }
        }

        if (!empty($category_products)) {
            foreach ($category_products as $category_product_key => $category_product) {
                if (
                    isset($category_product['configuration'])
                    && array_key_exists('onecrm_parent_id', $category_product['configuration'])
                    && isset($category_product['configuration']['onecrm_parent_id'])
                ) {
                    $hierarchical_category_products[$category_product['configuration']['onecrm_parent_id']]['products'][] = $category_product;
                }
            }
        }

        $hierarchical_category_products = array_values($hierarchical_category_products);
        echo "Hierarchy created\n";

        return $hierarchical_category_products;
    }

    private function transformProductCategoryRelatedProducts($_category_products = [], $category_id = null, $ignore_attributes = false)
    {
        echo "Transforming Category Related Products\n";
        $category_products = [];
        if (!empty($_category_products)) {
            $files = Utilities::fetchProductImageFilenames();

            $tax_codes = $this->_tax_code->index();
            $current_parent_sku = '';
            foreach ($_category_products as $_category_product) {
                $product = [
                    'is_enabled' => ('yes' == $_category_product['is_available']) ? true : false,
                    'name' => $_category_product['name'],
                    'name_label' => $_category_product['name'],
                    'sku' => $_category_product['manufacturers_part_no'],
                    'supplier_sku' => $_category_product['vendor_part_no'],
                    'price' => floatval($_category_product['purchase_price']),
                    'weight' => floatval($_category_product['weight_kg']),
                    'images' => Utilities::filenamesToUrl($_category_product['img_url'], true, $files),
                    'configuration' => [
                        'onecrm_id' => $_category_product['id'],
                        'onecrm_parent_id' => !empty($_category_product['parent_product_id']) ? $_category_product['parent_product_id'] : null,
                        'onecrm_product_class' => !empty($_category_product['product_class']) ? $_category_product['product_class'] : 'simple',
                        'onecrm_category_id' => $category_id,
                        'onecrm_tax_code_id' => $_category_product['tax_code_id'],
                        'onecrm_tax_code' => isset($tax_codes[$_category_product['tax_code_id']]) ? $tax_codes[$_category_product['tax_code_id']] : null,
                        'tax_percentage' => isset($tax_codes[$_category_product['tax_code_id']]) && in_array($tax_codes[$_category_product['tax_code_id']], ['ZERO RATED', 'Tax Exempt']) ? 0 : 20,
                        'cost' => $_category_product['cost'] ? $_category_product['cost'] : null,
                        'weight' => $_category_product['weight_1'],
                        'description' => Utilities::cleanHtml($_category_product['description']),
                        'size_guide' => json_decode($this->iso8859_1_to_utf8(Utilities::cleanHtml($_category_product['size_guide'])), JSON_UNESCAPED_SLASHES),
                        'size_guide_note' => $_category_product['size_guide_note'],
                        'shipping' => Utilities::cleanHtml($_category_product['shipping']),
                        'qty_per_order' => (0 != $_category_product['qty_per_order']) ? intval($_category_product['qty_per_order']) : null,
                        'qty_per_order_grouping' => null,
                        'launch_date' => isset($_category_product['launch_date']) && !empty($_category_product['launch_date']) ? $_category_product['launch_date'] : null,
                        'purchase_from' => isset($_category_product['purchase_from']) && !empty($_category_product['purchase_from']) ? $_category_product['purchase_from'] : null,
                    ],
                    'attributes' => $this->fetchProductAttributes($_category_product, $ignore_attributes),
                    'metadata' => [
                        'accreditations' => !empty($_category_product['accreditations']) ? explode('^,^', $_category_product['accreditations']) : null,
                        'age_groups' => !empty($_category_product['age_groups']) ? explode('^,^', $_category_product['age_groups']) : null,
                        'brand' => !empty($_category_product['brand']) ? $_category_product['brand'] : null,
                        'gender' => !empty($_category_product['gender']) ? $_category_product['gender'] : null,
                        'product_types' => !empty($_category_product['product_types']) ? explode('^,^', $_category_product['product_types']) : null,
                    ],
                    'sort_order' => $_category_product['sort_order'],
                    'modified_at' => $_category_product['date_modified'],
                ];

                $builder_config = $this->extractBuilderConfig($_category_product, $product['images']);
                if (!empty($builder_config)) {
                    $builder_type = empty($_category_product['parent_product_id']) ? 'builder_config' : 'builder_images';
                    $product['configuration'][$builder_type] = $builder_config;
                }
                if (null == $_category_product['parent_product_id']) {
                    $product['configuration']['is_customisable'] = !empty($_category_product['customisable']) && $_category_product['customisable'] ? true : false;
                    $product['configuration']['is_multi_select'] = !empty($_category_product['multi_select']) && $_category_product['multi_select'] ? true : false;
                }
                $category_products[$_category_product['id']] = $product;
            }
        }

        return $category_products;
    }
}
