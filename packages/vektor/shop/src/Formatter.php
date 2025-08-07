<?php

namespace Vektor\Shop;

use Vektor\Shop\Utilities as ShopUtilities;

class Formatter
{
    public static function unravelProductConfiguration($product, $product_images)
    {
        if (isset($product['products']) && !empty($product['products'])) {
            foreach ($product['products'] as &$product_inner) {
                $product_inner = self::unravelProductConfiguration($product_inner, $product_images);
                if (empty($product_inner['sku'])) {
                    unset($product_inner['sku']);
                }
                if (empty($product_inner['price'])) {
                    unset($product_inner['price']);
                } else {
                    if (isset($product['configuration'], $product['configuration']['tax_percentage'])) {
                        $product_inner['display_price'] = ShopUtilities::addPercentage($product_inner['price'], $product['configuration']['tax_percentage']);
                        $product_inner['tax'] = round($product_inner['display_price'] - $product_inner['price'], 2);
                    }
                }
                if (empty($product_inner['images'])) {
                    unset($product_inner['images']);
                } else {
                    $product_inner['images'] = ShopUtilities::filenamesToUrl($product_inner['images'], false, $product_images);
                }
            }
            unset($product_inner);
        }

        if (isset($product['configuration']) && !empty($product['configuration'])) {
            $product = array_replace($product, $product['configuration']);
            unset($product['configuration']);
        }

        if (isset($product['builder_images'])) {
            foreach ($product['builder_images'] as &$image) {
                $image = ShopUtilities::filenameToUrl($image, false, $product_images);
            }
        }

        return $product;
    }

    public static function product($_product, $product_images = [])
    {
        // $product_images = !empty($product_images) ? $product_images : ShopUtilities::fetchProductImageFilenames();
        // $_product['images'] = ShopUtilities::filenamesToUrl($_product['images'], false, $product_images);
        $_product['display_price'] = ShopUtilities::addPercentage($_product['price'], $_product['configuration']['tax_percentage']);
        $_product['tax'] = round($_product['display_price'] - $_product['price'], 2);

        return $_product;
    }

    public static function products($_products)
    {
        $product_images = ShopUtilities::fetchProductImageFilenames();
        $products = [];
        if (!empty($_products)) {
            foreach ($_products as $_product) {
                $products[] = self::product($_product, $product_images);
            }
        }

        return $products;
    }
}
