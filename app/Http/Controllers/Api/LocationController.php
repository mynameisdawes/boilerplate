<?php

namespace App\Http\Controllers\Api;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;

class LocationController extends ApiController
{
    public function geocode_place(Request $request)
    {
        $client = new Client();
        $url = 'https://enter.asahibeer.co.uk/api/locations/geocode/place';

        try {
            $response = $client->post($url, [
                'headers' => [
                    'X-API-KEY' => '4H5V9NHT6B19HQHZBXBGWLEFDJX7W64R493S',
                ],
                'json' => $request->all(),
            ]);

            if (200 === $response->getStatusCode()) {
                $body = json_decode($response->getBody(), true);

                $response = $body;

                return $this->response($response);
            }
        } catch (\Exception $e) {
        }

        return $this->response([
            'error' => true,
        ]);
    }

    public function autocomplete_places(Request $request)
    {
        $client = new Client();
        $url = 'https://enter.asahibeer.co.uk/api/locations/autocomplete/places';

        try {
            $response = $client->post($url, [
                'headers' => [
                    'X-API-KEY' => '4H5V9NHT6B19HQHZBXBGWLEFDJX7W64R493S',
                ],
                'json' => $request->all(),
            ]);

            if (200 === $response->getStatusCode()) {
                $body = json_decode($response->getBody(), true);

                $response = $body;

                return $this->response($response);
            }
        } catch (\Exception $e) {
        }

        return $this->response([
            'error' => true,
        ]);
    }
}
