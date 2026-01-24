<?php

/**
 * Test script to verify Pathao connection with provided credentials
 */

require __DIR__ . '/vendor/autoload.php';

use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\Drivers\Pathao\PathaoConfig;
use Millat\DeshCourier\Drivers\Pathao\PathaoCourier;

echo "=== Testing Pathao Connection ===\n\n";

// Official Pathao sandbox credentials (for testing)
// Replace with your own credentials for production
$pathaoConfig = [
    'client_id' => '7N1aMJQbWm',
    'client_secret' => 'wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39',
    'username' => 'test@pathao.com',
    'password' => 'lovePathao',
    'environment' => 'sandbox',
    'api_url' => 'https://courier-api-sandbox.pathao.com',
    'auth_url' => 'https://courier-api-sandbox.pathao.com',
];

try {
    // Create Pathao config
    $config = new PathaoConfig($pathaoConfig);
    
    echo "✓ Configuration created\n";
    echo "  API URL: " . $config->getApiUrl() . "\n";
    echo "  Auth URL: " . $config->getAuthUrl() . "\n";
    echo "  Client ID: " . $config->getClientId() . "\n";
    echo "  Username: " . $config->getUsername() . "\n\n";
    
    // Create courier instance
    $courier = new PathaoCourier($config);
    
    echo "✓ Pathao courier instance created\n";
    echo "  Name: " . $courier->getName() . "\n";
    echo "  Display Name: " . $courier->getDisplayName() . "\n";
    echo "  Capabilities: " . count($courier->capabilities()) . " features\n\n";
    
    // Test connection (authentication) with detailed error reporting
    echo "Testing authentication...\n";
    
    try {
        // Use reflection to call authenticate directly for better error reporting
        $reflection = new \ReflectionClass($courier);
        $authenticateMethod = $reflection->getMethod('authenticate');
        $authenticateMethod->setAccessible(true);
        $authenticateMethod->invoke($courier);
        
        echo "✓ Connection successful! Authentication works.\n\n";
    } catch (\Exception $e) {
        echo "✗ Connection failed. Authentication error.\n\n";
        echo "Error: " . $e->getMessage() . "\n";
        
        if ($e instanceof \Millat\DeshCourier\Exceptions\ApiException) {
            echo "Status Code: " . ($e->getStatusCode() ?? 'N/A') . "\n";
            if ($e->getApiResponse()) {
                echo "API Response:\n";
                echo json_encode($e->getApiResponse(), JSON_PRETTY_PRINT) . "\n";
            }
        }
        
        // Also test the HTTP call directly to see raw response
        echo "\nTesting direct HTTP call to Pathao API...\n";
        $httpClient = new \Millat\DeshCourier\Support\HttpClient();
        try {
            $response = $httpClient->post(
                'https://courier-api-sandbox.pathao.com/issue-token',
                [
                    'json' => [
                        'client_id' => $pathaoConfig['client_id'],
                        'client_secret' => $pathaoConfig['client_secret'],
                        'username' => $pathaoConfig['username'],
                        'password' => $pathaoConfig['password'],
                        'grant_type' => 'password',
                    ],
                ]
            );
            
            echo "HTTP Status: " . $response['status'] . "\n";
            echo "Response Data:\n";
            echo json_encode($response['data'], JSON_PRETTY_PRINT) . "\n";
        } catch (\Exception $httpError) {
            echo "HTTP Error: " . $httpError->getMessage() . "\n";
        }
        
        exit(1);
    }
    
    // Register with DeshCourier
    DeshCourier::register($courier);
    echo "✓ Pathao registered with DeshCourier\n\n";
    
    // Test using facade
    echo "Testing via DeshCourier facade...\n";
    $courierViaFacade = DeshCourier::use('pathao');
    echo "✓ Retrieved courier via facade: " . $courierViaFacade->getDisplayName() . "\n\n";
    
    echo "=== All Tests Passed! ===\n";
    echo "\nYou can now use Pathao courier:\n";
    echo "  DeshCourier::use('pathao')->createShipment(\$shipment);\n";
    echo "  DeshCourier::track('pathao', 'TRACK123');\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if ($e instanceof \Millat\DeshCourier\Exceptions\ApiException) {
        echo "  Status Code: " . ($e->getStatusCode() ?? 'N/A') . "\n";
        if ($e->getApiResponse()) {
            echo "  API Response: " . json_encode($e->getApiResponse(), JSON_PRETTY_PRINT) . "\n";
        }
    }
    
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
