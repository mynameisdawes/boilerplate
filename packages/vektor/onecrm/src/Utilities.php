<?php

namespace Vektor\OneCRM;

use Vektor\OneCRM\Models\Customisation;

class Utilities
{
    public static function itemDescriptions($customisations, $cart)
    {
        return array_reduce($customisations, function ($description, $customisation) use ($cart) {
            $items = array_values(array_filter($cart, function ($item) use ($customisation) {
                return $item['id'] == $customisation->get_id();
            }));

            if (count($items) > 0) {
                $equals_sm = '====';
                $equals_lg = str_repeat($equals_sm, 2);
                $equals_br = str_repeat($equals_lg, 8);
                $description .= "{$equals_br}\n";
                foreach ($items as $item) {
                    $description .= "\n".strtoupper($item['name']).' - '.strtoupper($item['options']['garment_options'][$item['options']['primary']])."\n";
                    $description .= "\n{$equals_lg} QUANTITIES {$equals_lg}\n\n";
                    $description .= implode("\n", array_map(function ($option) {
                        return strtoupper($option['value']).': '.$option['qty'];
                    }, $item['options']['garment_options'][$item['options']['secondary']]));
                    $description .= "\n\n{$equals_br}\n";
                }
                $description .= $customisation->get_comment()."\n\n";
            }

            return $description;
        });
    }

    public static function transformCustomisations($array, $directory, $cart)
    {
        $customisations = [];

        if (!empty($array)) {
            for ($i = 0; $i < count($array); ++$i) {
                $id = $array[$i]['id'];
                $options = array_filter(array_map(function ($item) use ($id) {
                    if ($id == $item['id']) {
                        if (isset($item['options']['garment_options'])) {
                            $options = $item['options']['garment_options'];
                            $options['primary'] = $item['options']['primary'];
                            $options['secondary'] = $item['options']['secondary'];

                            return $options;
                        }

                        return $item['options'];
                    }
                }, $cart));
                $customisations[$id] = new Customisation($i + 1, $array[$i], $directory, $options);
            }
        }

        return array_filter($customisations);
    }

    public static function createGUIdSection($characters)
    {
        $return = '';
        for ($i = 0; $i < $characters; ++$i) {
            $return .= sprintf('%x', mt_rand(0, 15));
        }

        return $return;
    }

    public static function ensureLength(&$string, $length)
    {
        $strlen = strlen($string);
        if ($strlen < $length) {
            $string = str_pad($string, $length, '0');
        } elseif ($strlen > $length) {
            $string = substr($string, 0, $length);
        }
    }

    public static function createMessageId($current_user_id, $host_name)
    {
        $id = '<';
        if (!empty($current_user_id)) {
            $id .= $current_user_id.'-';
        }
        $id .= uniqid('');
        $id .= '@'.$host_name;
        $id .= '>';

        return $id;
    }

    public static function createGUId()
    {
        $microTime = microtime();
        list($a_dec, $a_sec) = explode(' ', $microTime);

        $dec_hex = sprintf('%x', $a_dec * 1000000);
        $sec_hex = sprintf('%x', $a_sec);

        self::ensureLength($dec_hex, 5);
        self::ensureLength($sec_hex, 6);

        $guid = '';
        $guid .= $dec_hex;
        $guid .= self::createGUIDSection(3);
        $guid .= '-';
        $guid .= self::createGUIDSection(4);
        $guid .= '-';
        $guid .= self::createGUIDSection(4);
        $guid .= '-';
        $guid .= self::createGUIDSection(4);
        $guid .= '-';
        $guid .= $sec_hex;
        $guid .= self::createGUIDSection(6);

        return $guid;
    }
}
