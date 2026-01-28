<?php

require __DIR__ . '/../vendor/autoload.php';

use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Rate;

echo "=== DTO Usage Example ===\n\n";

$shipment = new Shipment();
$shipment->recipientName = 'John Doe';
$shipment->recipientPhone = '01712345678';
$shipment->recipientAddress = 'House 123, Road 45, Gulshan-2';
$shipment->recipientCity = 'Dhaka';
$shipment->recipientZone = 'Gulshan';
$shipment->senderName = 'My Store';
$shipment->senderPhone = '01787654321';
$shipment->senderAddress = 'Shop 456, Market Street';
$shipment->senderCity = 'Dhaka';
$shipment->weight = 1.5;
$shipment->codAmount = 1500;
$shipment->serviceType = 'next_day';
$shipment->itemDescription = 'Electronics';

try {
    $result = DeshCourier::createShipment('pathao', $shipment);
    echo "✓ Shipment created with DTO\n";
    echo "  Tracking ID: " . $result->trackingId . "\n";
    echo "  Status: " . $result->status . "\n\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

$shipment2 = new Shipment([
    'recipientName' => 'Jane Smith',
    'recipientPhone' => '01711111111',
    'recipientAddress' => 'House 789, Road 12',
    'recipientCity' => 'Dhaka',
    'senderName' => 'Store',
    'senderPhone' => '01787654321',
    'senderAddress' => 'Shop 456',
    'senderCity' => 'Dhaka',
    'weight' => 2.0,
    'codAmount' => 2000,
]);

echo "✓ DTO created with constructor mass assignment\n";
echo "  Recipient: " . $shipment2->recipientName . "\n";
echo "  Service Type (default): " . $shipment2->serviceType . "\n\n";

$rate = new Rate();
$rate->fromCity = 'Dhaka';
$rate->toCity = 'Sylhet';
$rate->weight = 1.5;
$rate->codAmount = 1500;
$rate->serviceType = 'next_day';

try {
    $result = DeshCourier::estimateRate('pathao', $rate);
    echo "✓ Rate estimated with DTO\n";
    echo "  Delivery Charge: BDT " . number_format($result->deliveryCharge, 2) . "\n";
    echo "  Total: BDT " . number_format($result->totalCharge, 2) . "\n\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== Benefits of DTO Usage ===\n";
echo "✓ IDE autocomplete for all properties\n";
echo "✓ Type safety and validation\n";
echo "✓ Better code documentation\n";
echo "✓ Refactoring support\n";
