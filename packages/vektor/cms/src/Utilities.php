<?php

namespace Vektor\CMS;

use Illuminate\Support\Str;

class Utilities
{
    public static function markdownParse($value)
    {
        // $value = preg_replace_callback('/\{(\w+):(\d+)\}/', function ($matches) {
        //     $id = $matches[2];
        //     $model_name = $matches[1];
        //     $model_class = "Vektor\\CMS\\Models\\" . ucfirst($model_name);

        //     if (class_exists($model_class)) {
        //         $model = (new $model_class)::find($id);
        //         if ($model && $model->href && !empty($model->href)) {
        //             return $model->href;
        //         }
        //     }

        //     return '';
        // }, $value);

        return Str::markdown($value);
    }
}
