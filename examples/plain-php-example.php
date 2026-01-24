<?php

/**
 * Plain PHP Usage Example
 * 
 * This example demonstrates how to use Desh Courier SDK in plain PHP.
 */

require __DIR__ . '/../vendor/autoload.php';

use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Rate;

echo "=== Desh Courier SDK - Plain PHP Example ===\n\n";

// Example 1: Create a Shipment
echo "1. Creating Shipment...\n";

$shipment = new Shipment();
$shipment->recipientName = 'John Doe';
$shipment->recipientPhone = '01712345678';
$shipment->recipientAddress = 'House 123, Road 45, Gulshan-2';
$shipment->recipientCity = 'Dhaka';
$shipment->recipientZone = 'Gulshan';
$shipment->recipientPostalCode = '1212';

$shipment->senderName = 'My Store';
$shipment->senderPhone = '01787654321';
$shipment->senderAddress = 'Shop 456, Market Street';
$shipment->senderCity = 'Dhaka';
$shipment->senderZone = 'Dhanmondi';

$shipment->weight = 1.5; // kg
$shipment->quantity = 1;
$shipment->itemDescription = 'T-Shirt';
$shipment->itemValue = 1500;
$shipment->codAmount = 1500;
$shipment->serviceType = 'next_day';
$shipment->externalOrderId = 'ORDER-' . time();
$shipment->orderSource = 'website';
$shipment->deliveryInstruction = 'Call before delivery';

try {
    $result = DeshCourier::createShipment('pathao', $shipment);
    
    echo "✓ Shipment created successfully!\n";
    echo "  Tracking ID: " . $result->trackingId . "\n";
    echo "  Status: " . $result->status . "\n";
    if ($result->labelUrl) {
        echo "  Label URL: " . $result->labelUrl . "\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "✗ Error creating shipment: " . $e->getMessage() . "\n\n";
}

// Example 2: Track a Shipment
echo "2. Tracking Shipment...\n";

try {
    // Use the tracking ID from above, or use a test ID
    $trackingId = $result->trackingId ?? 'TRACK123';
    $tracking = DeshCourier::track('pathao', $trackingId);
    
    echo "✓ Tracking information retrieved!\n";
    echo "  Status: " . $tracking->status . "\n";
    echo "  Current Location: " . ($tracking->currentLocation ?? 'N/A') . "\n";
    
    if ($tracking->isDelivered()) {
        echo "  ✓ Delivered!\n";
        echo "  Delivered to: " . ($tracking->deliveredTo ?? 'N/A') . "\n";
        if ($tracking->deliveredAt) {
            echo "  Delivered at: " . $tracking->deliveredAt->format('Y-m-d H:i:s') . "\n";
        }
    } elseif ($tracking->isInTransit()) {
        echo "  → In Transit\n";
    }
    
    echo "\n";
} catch (\Exception $e) {
    echo "✗ Error tracking shipment: " . $e->getMessage() . "\n\n";
}

// Example 3: Estimate Rate
echo "3. Estimating Delivery Rate...\n";

$rate = new Rate();
$rate->fromCity = 'Dhaka';
$rate->fromZone = 'Dhanmondi';
$rate->toCity = 'Chittagong';
$rate->toZone = 'Agrabad';
$rate->weight = 2.0;
$rate->codAmount = 2000;
$rate->serviceType = 'standard';

try {
    $estimate = DeshCourier::estimateRate('pathao', $rate);
    
    echo "✓ Rate estimated!\n";
    echo "  Delivery Charge: BDT " . number_format($estimate->deliveryCharge, 2) . "\n";
    echo "  COD Charge: BDT " . number_format($estimate->codCharge, 2) . "\n";
    echo "  Total Charge: BDT " . number_format($estimate->totalCharge, 2) . "\n";
    if ($estimate->estimatedDays) {
        echo "  Estimated Days: " . $estimate->estimatedDays . "\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "✗ Error estimating rate: " . $e->getMessage() . "\n\n";
}

// Example 4: Check Capabilities
echo "4. Checking Courier Capabilities...\n";

$courier = DeshCourier::use('pathao');
$capabilities = $courier->capabilities();

echo "✓ Available capabilities:\n";
foreach ($capabilities as $capability) {
    echo "  - $capability\n";
}
echo "\n";

// Example 5: Get COD Details
echo "5. Getting COD Details...\n";

if ($courier instanceof \Millat\DeshCourier\Contracts\CodInterface) {
    try {
        $trackingId = $result->trackingId ?? 'TRACK123';
        $cod = $courier->getCodDetails($trackingId);
        
        echo "✓ COD information retrieved!\n";
        echo "  COD Amount: BDT " . number_format($cod->codAmount, 2) . "\n";
        echo "  Collected: BDT " . number_format($cod->codCollected, 2) . "\n";
        echo "  Pending: BDT " . number_format($cod->codPending, 2) . "\n";
        echo "  Status: " . $cod->status . "\n";
        echo "\n";
    } catch (\Exception $e) {
        echo "✗ Error getting COD details: " . $e->getMessage() . "\n\n";
    }
} else {
    echo "  COD interface not supported\n\n";
}

echo "=== Example Complete ===\n";
