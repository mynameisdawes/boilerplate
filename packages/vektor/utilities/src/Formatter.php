<?php

namespace Vektor\Utilities;

class Formatter
{
    public static function decimalPlaces($value, $locale = 'en_GB', $decimal_places = 2)
    {
        $locales = [
            'en_GB' => 'GBP',
            'en_US' => 'USD',
        ];

        if (isset($locales[$locale])) {
            $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
            $formatter->setAttribute($formatter::FRACTION_DIGITS, $decimal_places);
            $parsed = $formatter->parse($value);

            return $formatter->format($parsed);
        }

        return $value;
    }

    public static function currency($value, $locale = 'en_GB')
    {
        $locales = [
            'en_GB' => 'GBP',
            'en_US' => 'USD',
        ];

        if (isset($locales[$locale])) {
            $value = floatval($value);
            $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

            return $formatter->formatCurrency($value, $locales[$locale]);
        }

        return $value;
    }

    public static function ordinal($value)
    {
        if (is_numeric($value)) {
            $non_standard_suffixes = ['st', 'nd', 'rd'];
            $non_standard_key = (($value + 90) % 100 - 10) % 10 - 1;
            $suffix = isset($non_standard_suffixes[$non_standard_key]) ? $non_standard_suffixes[$non_standard_key] : 'th';

            return $value.$suffix;
        }

        return $value;
    }

    public static function name($value)
    {
        if (is_string($value)) {
            $value = ucwords(strtolower(preg_replace("/[\\s\n\r]{2,}/", ' ', trim($value))), " \t\r\n\f\v\\'\\-(");
            if (preg_match('/(ma?c)([^aeiou])(.+)/i', $value, $value_chunks)) {
                $value = $value_chunks[1].strtoupper($value_chunks[2]).$value_chunks[3];
            }
            $value = str_replace("'S ", "'s ", $value);
        }

        return $value;
    }

    public static function postcode($value)
    {
        if (is_string($value)) {
            $value = strtoupper(preg_replace('/(.+)[\s]?(.{3})$/', '$1 $2', preg_replace("/[\\s\n\r]/", '', trim($value))));
        }

        return $value;
    }

    public static function email($value)
    {
        if (is_string($value)) {
            $value = strtolower(trim($value));
        }

        return $value;
    }

    public static function phone($value)
    {
        if (is_string($value)) {
            $value = preg_replace('/([\d]{0,})([\d]{3,3})([\d]{3,3})$/', '$1 $2 $3', preg_replace("/[\\s\n\r]/", '', trim($value)));
        }

        return $value;
    }

    public static function arrayToString($value, $delimiter = ',')
    {
        $array = (array) $value;
        $string = '';

        if (!empty($array)) {
            $array = array_values(array_filter($array));
        }

        if (!empty($array)) {
            $string = implode($delimiter, $array);
        }

        return $string;
    }

    public static function arrayToStringWithAmpersand($items = [])
    {
        $count = count($items);

        if (0 === $count) {
            return '';
        }
        if (1 === $count) {
            return $items[0];
        }

        $lastItem = array_pop($items);

        return (2 === $count)
            ? implode(' & ', [$items[0], $lastItem])
            : implode(', ', $items).' & '.$lastItem;
    }
}
