<?php

namespace Vektor\Utilities;

class Utilities
{
    public static function arrayToInlineStyles(array $attributes)
    {
        $result = '';

        foreach ($attributes as $key => $value) {
            if (0 !== strpos($key, '--')) {
                $key = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $key));
            }

            $result .= "{$key}: {$value}; ";
        }

        return trim($result);
    }

    public static function arrayToVueAttributes(array $attributes)
    {
        $result = [];

        foreach ($attributes as $key => $value) {
            if (!is_string($value)) {
                $result[] = ":{$key}='".json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."'";
            } else {
                $result[] = "{$key}=".'"'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'"';
            }
        }

        return implode(' ', $result);
    }

    public static function arrayToCsvFile($data, $filename = '', $delimiter = ',')
    {
        if ('' != $filename) {
            file_put_contents($filename, '');
            $header = fopen($filename, 'a+');
            foreach ($data as $row) {
                fputcsv($header, $row, $delimiter);
            }
            fclose($header);
        }
    }

    public static function arrayToCsv($data, $filename = '', $delimiter = ',')
    {
        if ('' != $filename) {
            $header = fopen('php://memory', 'w');
            foreach ($data as $row) {
                fputcsv($header, $row, $delimiter);
            }
            fseek($header, 0);
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="'.$filename.'";');
            fpassthru($header);
        }
    }

    public static function csvToArray($filename = '', $delimiter = ',', $provide_header = false)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }
        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (!$header) {
                    if (true == $provide_header) {
                        $header = array_map(function ($item) {
                            return 'col_'.($item + 1);
                        }, array_keys($row));
                        $data[] = array_combine($header, $row);
                    } else {
                        $header = $row;
                    }
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }
}
