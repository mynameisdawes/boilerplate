<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Vektor\Api\Http\Controllers\ApiController;

class FileUploadController extends ApiController
{
    public function nova_upload(Request $request)
    {
        try {
            if ($request->hasHeader('X-U-Secret-Key') && $request->header('X-U-Secret-Key') == config('nova-markdown-tui.mediaUploadHeaders.X-U-Secret-Key')) {
                $path = $request->file('file')->store('public');

                return response()->json([
                    'url' => url(Storage::url($path)),
                ]);
            }
        } catch (\Exception $e) {
        }

        return $this->response([
            'error' => true,
        ]);
    }

    public function standard_upload(Request $request)
    {
        try {
            $uploaded_file = $request->file('file');

            if (!$uploaded_file) {
                return $this->response([
                    'error' => true,
                    'error_message' => 'No file uploaded or incorrect format.',
                ]);
            }

            $original_name = pathinfo($uploaded_file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $uploaded_file->getClientOriginalExtension();

            $clean_name = Str::slug($original_name, '_');
            $unique_name = $clean_name.'_'.time().'.'.$extension;

            $path = $uploaded_file->storeAs('files', $unique_name);

            return $this->response([
                'success' => true,
                'data' => [
                    'file_name' => $unique_name,
                    'file_path' => url(Storage::url($path)),
                    'file_extension' => $extension,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->response([
                'error' => true,
                'error_message' => 'File uploads failed.',
            ]);
        }
    }

    public function delete_standard_upload(Request $request)
    {
        try {
            $file_name = $request->input('server_file_name');

            if (!$file_name) {
                return $this->response([
                    'error' => true,
                    'error_message' => 'No file ID provided.',
                ]);
            }

            $file_path = 'files/'.$file_name;

            if (!Storage::exists($file_path)) {
                return $this->response([
                    'error' => true,
                    'error_message' => 'File not found.',
                    'http_code' => 404,
                ]);
            }

            Storage::delete($file_path);

            return $this->response([
                'success' => true,
                'success_message' => 'File deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return $this->response([
                'error' => true,
                'error_message' => 'File deletion failed.',
            ]);
        }
    }
}
