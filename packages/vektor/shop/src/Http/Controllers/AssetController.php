<?php

namespace Vektor\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;

class AssetController extends ApiController
{
    public function product_images(Request $request, $base_dir, $filename = null)
    {
        if (!isset($filename)) {
            $filename = $base_dir;
            $base_dir = null;
        }

        $files = glob(resource_path('assets/products/'.($base_dir ? $base_dir.'/' : '').'*'));
        if (!empty($files)) {
            foreach ($files as $file) {
                $file_pathinfo = pathinfo($file);
                if (!$base_dir && is_dir($file) && str_starts_with($filename, $file_pathinfo['filename'])) {
                    return $this->product_images($request, $file_pathinfo['filename'], $filename);
                }
                if ($file_pathinfo['filename'] == $filename) {
                    $file_mime_type = mime_content_type($file);

                    return response(file_get_contents($file))
                        ->header('Content-Type', $file_mime_type)
                        ->header('Content-Disposition', 'inline; filename="'.$file_pathinfo['basename'].'"')
                    ;
                }
            }
        }
    }
}
