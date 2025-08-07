<?php

namespace App\Http\Controllers;

class AssetController extends Controller
{
    public function favicon()
    {
        $files = glob(resource_path('assets/favicon.*'));
        if (!empty($files)) {
            $file = current($files);
            $file_pathinfo = pathinfo($file);
            $file_mime_type = mime_content_type($file);

            return response(file_get_contents($file))
                ->header('Content-Type', $file_mime_type)
                ->header('Content-Disposition', 'inline; filename="'.$file_pathinfo['basename'].'"')
            ;
        }

        return response(file_get_contents(public_path('favicon.png')))
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'inline; filename="favicon.png"')
        ;
    }

    public function favicon_type()
    {
        $files = glob(resource_path('assets/favicon.*'));
        if (!empty($files)) {
            $file = current($files);

            return mime_content_type($file);
        }

        return 'image/png';
    }

    public function logo()
    {
        $files = glob(resource_path('assets/logo.*'));
        if (!empty($files)) {
            $file = current($files);
            $file_pathinfo = pathinfo($file);
            $file_mime_type = mime_content_type($file);

            return response(file_get_contents($file))
                ->header('Content-Type', $file_mime_type)
                ->header('Content-Disposition', 'inline; filename="'.$file_pathinfo['basename'].'"')
            ;
        }

        return response(file_get_contents(public_path('logo.svg')))
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'inline; filename="logo.svg"')
        ;
    }

    public function email_logo()
    {
        $files = glob(resource_path('assets/email_logo.*'));
        if (!empty($files)) {
            $file = current($files);
            $file_pathinfo = pathinfo($file);
            $file_mime_type = mime_content_type($file);

            return response(file_get_contents($file))
                ->header('Content-Type', $file_mime_type)
                ->header('Content-Disposition', 'inline; filename="'.$file_pathinfo['basename'].'"')
            ;
        }
        abort(404);
    }
}
