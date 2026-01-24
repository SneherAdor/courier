<?php

/**
 * Test Pathao authentication with credentials in headers
 */

require __DIR__ . '/vendor/autoload.php';

use Millat\DeshCourier\Support\HttpClient;

$baseUrl = 'https://courier-api-sandbox.pathao.com/api/v1/issue-token';
$httpClient = new HttpClient();

echo "Testing Pathao authentication with different header configurations...\n\n";

// Test with Basic Auth
echo "Test 1: Basic Auth with client_id:client_secret\n";
try {
    $response = $httpClient->post($baseUrl, [
        'auth' => ['QBeX5jWayK', 'Gz6BNcJG2zYwnSl1AbX13qEyQQjT7M2U0kafk85g'],
        'json' => [
            'username' => 'sneherador.sa.sa@gmail.com',
            'password' => '686436832@Sa',
            'grant_type' => 'password',
        ],
    ]);
    echo "  Status: " . $response['status'] . "\n";
    echo "  Response: " . json_encode($response['data'], JSON_PRETTY_PRINT) . "\n";
    if ($response['status'] === 200 && (isset($response['data']['access_token']) || isset($response['data']['token']))) {
        echo "  ✓ SUCCESS!\n";
    }
} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test with all credentials in body (current format)
echo "Test 2: All credentials in JSON body (current implementation)\n";
try {
    $response = $httpClient->post($baseUrl, [
        'json' => [
            'client_id' => 'QBeX5jWayK',
            'client_secret' => 'Gz6BNcJG2zYwnSl1AbX13qEyQQjT7M2U0kafk85g',
            'username' => 'sneherador.sa.sa@gmail.com',
            'password' => '686436832@Sa',
            'grant_type' => 'password',
        ],
    ]);
    echo "  Status: " . $response['status'] . "\n";
    echo "  Response: " . json_encode($response['data'], JSON_PRETTY_PRINT) . "\n";
    
    // Check if response has token in different possible fields
    $data = $response['data'] ?? [];
    if (isset($data['access_token']) || isset($data['token']) || isset($data['data']['access_token'])) {
        echo "  ✓ SUCCESS! Token found in response.\n";
        $token = $data['access_token'] ?? $data['token'] ?? $data['data']['access_token'] ?? null;
        echo "  Token: " . substr($token, 0, 20) . "...\n";
    } else {
        echo "  ✗ No token found in response\n";
    }
} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\nNote: If all tests show 'Unauthorized!', the credentials may need to be verified with Pathao support.\n";
