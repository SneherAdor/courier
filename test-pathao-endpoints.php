<?php

/**
 * Test different Pathao API endpoints to find the correct one
 */

require __DIR__ . '/vendor/autoload.php';

use Millat\DeshCourier\Support\HttpClient;

$baseUrl = 'https://courier-api-sandbox.pathao.com';
$credentials = [
    'client_id' => 'QBeX5jWayK',
    'client_secret' => 'Gz6BNcJG2zYwnSl1AbX13qEyQQjT7M2U0kafk85g',
    'username' => 'sneherador.sa.sa@gmail.com',
    'password' => '686436832@Sa',
    'grant_type' => 'password',
];

$endpoints = [
    '/issue-token',
    '/v1/issue-token',
    '/api/v1/issue-token',
    '/oauth/token',
    '/api/issue-token',
    '/token',
];

$httpClient = new HttpClient();

echo "Testing different Pathao API authentication endpoints...\n\n";

foreach ($endpoints as $endpoint) {
    echo "Testing: {$baseUrl}{$endpoint}\n";
    
    try {
        $response = $httpClient->post(
            $baseUrl . $endpoint,
            [
                'json' => $credentials,
            ]
        );
        
        echo "  ✓ Status: " . $response['status'] . "\n";
        
        if ($response['status'] === 200) {
            echo "  ✓ SUCCESS! This is the correct endpoint.\n";
            echo "  Response:\n";
            echo json_encode($response['data'], JSON_PRETTY_PRINT) . "\n";
            echo "\n✅ Found working endpoint: {$endpoint}\n";
            break;
        } else {
            echo "  ✗ Status: " . $response['status'] . "\n";
            if (isset($response['data']['message'])) {
                echo "  Message: " . $response['data']['message'] . "\n";
            }
        }
    } catch (\Exception $e) {
        $message = $e->getMessage();
        if (strpos($message, '404') !== false) {
            echo "  ✗ 404 Not Found\n";
        } elseif (strpos($message, '401') !== false) {
            echo "  ✗ 401 Unauthorized (endpoint exists but credentials wrong)\n";
        } else {
            echo "  ✗ Error: " . $message . "\n";
        }
    }
    
    echo "\n";
}
