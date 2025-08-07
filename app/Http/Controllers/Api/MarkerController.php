<?php

namespace App\Http\Controllers\Api;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;

class MarkerController extends ApiController
{
    public function handle(Request $request)
    {
        $data = json_decode(file_get_contents(storage_path('markers.json')), true);

        $response = [
            'success' => true,
            'edata' => [
                'markers' => $data,
            ],
        ];

        return $this->response($response);
        $client = new Client();
        $url = 'https://enter.asahibeer.local/api/markers';

        try {
            $response = $client->post($url, [
                'headers' => [
                    'X-API-KEY' => '4H5V9NHT6B19HQHZBXBGWLEFDJX7W64R493S',
                ],
                'json' => [
                    'brand_id' => '4',
                ],
            ]);

            if (200 === $response->getStatusCode()) {
                $body = json_decode($response->getBody(), true);

                $response = $body;
                $response['edata'] = $body['data'];

                return $this->response($response);
            }
        } catch (\Exception $e) {
        }

        return $this->response([
            'error' => true,
        ]);
    }
}
