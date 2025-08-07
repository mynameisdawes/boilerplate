<?php

namespace Vektor\Shop\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Vektor\Api\Http\Controllers\ApiController;

class BuilderController extends ApiController
{
    public function slugify($string, $replace = [], $delimiter = '-')
    {
        if (!extension_loaded('iconv')) {
            throw new Exception('iconv module not loaded');
        }

        $oldLocale = setlocale(LC_ALL, '0');
        setlocale(LC_ALL, 'en_US.UTF-8');
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

        if (!empty($replace)) {
            $clean = str_replace((array) $replace, ' ', $clean);
        }

        $clean = preg_replace('/[^a-zA-Z0-9\/_|+ -]/', '', $clean);
        $clean = strtolower($clean);
        $clean = preg_replace('/[\/_|+ -]+/', $delimiter, $clean);
        $clean = trim($clean, $delimiter);

        setlocale(LC_ALL, $oldLocale);

        return $clean;
    }

    public function handleFileUpload(Request $request)
    {
        $image_size__main = 2600;
        $image_size__thumbnail = 220;
        $timestamp = date('U');

        try {
            if ($request->hasFile('file')) {
                $extension = $request->file->getClientOriginalExtension();
                $full_name = $request->file->getClientOriginalName();
                $short_name = $this->slugify($full_name, [$extension]);
                $hashed_name = 'preview--'.strtolower($short_name).'_'.$timestamp.'.'.$extension;
                $request->file->storeAs('builder_uploads', $hashed_name);

                if ('svg' != $extension) {
                    // non-resized image
                    $image = \Image::make(Storage::get('builder_uploads/'.$hashed_name));
                    $image->orientate();
                    // $main_image = $image->save(public_path("builder_previews/{$hashed_name}"));

                    // resize image
                    // $preview_name = 'preview--' . $hashed_name;
                    $preview_image = $image->save(public_path("builder_uploads/{$hashed_name}"));

                    // resize if > 2600px tall/wide
                    if ($preview_image->width() > $image_size__main || $preview_image->height() > $image_size__main) {
                        if ($preview_image->width() < $preview_image->height()) {
                            $preview_image->resize($image_size__main, null, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        } else {
                            $preview_image->resize(null, $image_size__main, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        }
                        $preview_image->save();
                    }
                }

                return $this->response([
                    'success' => true,
                    'data' => [
                        'file_name' => $hashed_name,
                        'file_path' => url("builder_uploads/{$hashed_name}"),
                        'file_extension' => $extension,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            return $this->response([
                'http_code' => 500,
                'error' => true,
                'error_message' => $e->getMessage(),
            ]);
        }

        return $this->response([
            'error' => true,
        ]);
    }

    public function handleFileDelete(Request $request)
    {
        try {
            $file_name = $request->input('server_file_name');

            if (!$file_name) {
                return $this->response([
                    'error' => true,
                    'error_message' => 'No file name provided.',
                    'http_code' => 400,
                ]);
            }

            $file_path = 'builder_uploads/'.$file_name;

            if (!Storage::exists($file_path)) {
                return $this->response([
                    'error' => true,
                    'error_message' => 'File not found.',
                    'http_code' => 404,
                ]);
            }

            // Delete the file from storage
            Storage::delete($file_path);

            // Also delete from public directory if it exists
            $public_path = public_path("builder_uploads/{$file_name}");
            if (file_exists($public_path)) {
                unlink($public_path);
            }

            return $this->response([
                'success' => true,
                'success_message' => 'File deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return $this->response([
                'error' => true,
                'error_message' => $e->getMessage(),
                'http_code' => 500,
            ]);
        }
    }
}
