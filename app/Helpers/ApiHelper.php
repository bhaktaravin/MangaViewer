<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ApiHelper
{
    /**
     * Test connection to an API endpoint
     *
     * @param string $url The API endpoint URL to test
     * @param string $name The name of the API for logging purposes
     * @return array ['success' => bool, 'message' => string]
     */
    public static function testConnection($url, $name)
    {
        try {
            $client = new Client([
                'timeout' => 10,
                'connect_timeout' => 5,
                'http_errors' => false,
            ]);
            
            $response = $client->get($url);
            
            Log::info("{$name} API Test", [
                'status' => $response->getStatusCode(),
                'url' => $url
            ]);
            
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return [
                    'success' => true,
                    'message' => "Successfully connected to {$name} API!"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Error connecting to {$name} API. Status code: " . $response->getStatusCode()
                ];
            }
        } catch (\Exception $e) {
            Log::error("{$name} API Test Error", [
                'message' => $e->getMessage(),
                'url' => $url
            ]);
            
            return [
                'success' => false,
                'message' => "Error testing {$name} API connection: " . $e->getMessage()
            ];
        }
    }
}
