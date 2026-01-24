<?php

/**
 * Example: Using Arrays (Laravel-style, convenient)
 */

require __DIR__ . '/../vendor/autoload.php';

use Millat\DeshCourier\DeshCourier;

echo "=== Array Usage Example ===\n\n";

// Create shipment using array (no DTO needed!)
$shipmentData = [
    'recipientName' => 'John Doe',
    'recipientPhone' => '01712345678',
    'recipientAddress' => 'House 123, Road 45, Gulshan-2',
    'recipientCity' => 'Dhaka',
    'recipientZone' => 'Gulshan',
    'senderName' => 'My Store',
    'senderPhone' => '01787654321',
    'senderAddress' => 'Shop 456, Market Street',
    'senderCity' => 'Dhaka',
    'weight' => 1.5,
    'codAmount' => 1500,
    'serviceType' => 'next_day',
    'itemDescription' => 'Electronics',
];

// Pass array directly - SDK normalizes internally
try {
    $result = DeshCourier::createShipment('pathao', $shipmentData);
    echo "✓ Shipment created with array\n";
    echo "  Tracking ID: " . $result->trackingId . "\n";
    echo "  Status: " . $result->status . "\n\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Estimate rate using array
$rateData = [
    'fromCity' => 'Dhaka',
    'toCity' => 'Chittagong',
    'weight' => 2.0,
    'codAmount' => 2000,
    'serviceType' => 'standard',
];

try {
    $rate = DeshCourier::estimateRate('pathao', $rateData);
    echo "✓ Rate estimated with array\n";
    echo "  Total: BDT " . number_format($rate->totalCharge, 2) . "\n\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== Perfect for Laravel! ===\n";
echo "// In Laravel controller:\n";
echo "\$result = DeshCourier::createShipment('pathao', \$request->validated());\n";
