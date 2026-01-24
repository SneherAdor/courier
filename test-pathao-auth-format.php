<?php

/**
 * Test different authentication request formats for Pathao API
 */

require __DIR__ . '/vendor/autoload.php';

use Millat\DeshCourier\Support\HttpClient;

$baseUrl = 'https://courier-api-sandbox.pathao.com/api/v1/issue-token';
$credentials = [
    'client_id' => 'QBeX5jWayK',
    'client_secret' => 'Gz6BNcJG2zYwnSl1AbX13qEyQQjT7M2U0kafk85g',
    'username' => 'sneherador.sa.sa@gmail.com',
    'password' => '686436832@Sa',
    'grant_type' => 'password',
];

$httpClient = new HttpClient();

echo "Testing different request formats for Pathao authentication...\n\n";

// Test 1: JSON format
echo "Test 1: JSON format\n";
try {
    $response = $httpClient->post($baseUrl, [
        'json' => $credentials,
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
    ]);
    echo "  Status: " . $response['status'] . "\n";
    echo "  Response: " . json_encode($response['data'], JSON_PRETTY_PRINT) . "\n";
    if ($response['status'] === 200 && isset($response['data']['access_token'])) {
        echo "  ✓ SUCCESS with JSON format!\n";
    }
} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Form data format
echo "Test 2: Form data format\n";
try {
    $response = $httpClient->post($baseUrl, [
        'form_params' => $credentials,
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ],
    ]);
    echo "  Status: " . $response['status'] . "\n";
    echo "  Response: " . json_encode($response['data'], JSON_PRETTY_PRINT) . "\n";
    if ($response['status'] === 200 && isset($response['data']['access_token'])) {
        echo "  ✓ SUCCESS with form data format!\n";
    }
} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Different field names (email instead of username)
echo "Test 3: Using 'email' instead of 'username'\n";
$credentialsEmail = $credentials;
$credentialsEmail['email'] = $credentialsEmail['username'];
unset($credentialsEmail['username']);
try {
    $response = $httpClient->post($baseUrl, [
        'json' => $credentialsEmail,
    ]);
    echo "  Status: " . $response['status'] . "\n";
    echo "  Response: " . json_encode($response['data'], JSON_PRETTY_PRINT) . "\n";
    if ($response['status'] === 200 && isset($response['data']['access_token'])) {
        echo "  ✓ SUCCESS with email field!\n";
    }
} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}
