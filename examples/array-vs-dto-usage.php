<?php

require __DIR__ . '/../vendor/autoload.php';

use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Rate;

echo "=== Desh Courier SDK - Array vs DTO Usage Examples ===\n\n";

echo "1. Creating Shipment with DTO Object (IDE autocomplete supported)\n";

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

try {
    $result = DeshCourier::createShipment('pathao', $shipment);
    echo "✓ Shipment created with DTO object\n";
    echo "  Tracking ID: " . $result->trackingId . "\n\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "2. Creating Shipment with Array (Laravel-style, convenient)\n";

$shipmentData = [
    'recipientName' => 'Jane Smith',
    'recipientPhone' => '01711111111',
    'recipientAddress' => 'House 789, Road 12, Dhanmondi',
    'recipientCity' => 'Dhaka',
    'recipientZone' => 'Dhanmondi',
    'senderName' => 'My Store',
    'senderPhone' => '01787654321',
    'senderAddress' => 'Shop 456, Market Street',
    'senderCity' => 'Dhaka',
    'weight' => 2.0,
    'codAmount' => 2000,
    'serviceType' => 'standard',
    'itemDescription' => 'Electronics',
];

try {
    $result = DeshCourier::createShipment('pathao', $shipmentData);
    echo "✓ Shipment created with array\n";
    echo "  Tracking ID: " . $result->trackingId . "\n\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "3. Creating DTO with Constructor Mass Assignment\n";

$shipment = new Shipment([
    'recipientName' => 'Bob Johnson',
    'recipientPhone' => '01722222222',
    'recipientAddress' => 'House 321, Road 8, Mirpur',
    'recipientCity' => 'Dhaka',
    'senderName' => 'My Store',
    'senderPhone' => '01787654321',
    'senderAddress' => 'Shop 456',
    'senderCity' => 'Dhaka',
    'weight' => 1.0,
    'codAmount' => 1000,
]);

echo "✓ DTO created with mass assignment\n";
echo "  Recipient: " . $shipment->recipientName . "\n";
echo "  Weight: " . $shipment->weight . " kg\n";
echo "  Service Type: " . ($shipment->serviceType ?? 'default') . "\n\n";

echo "4. Estimating Rate with Array\n";

$rateData = [
    'fromCity' => 'Dhaka',
    'fromZone' => 'Dhanmondi',
    'toCity' => 'Chittagong',
    'toZone' => 'Agrabad',
    'weight' => 2.0,
    'codAmount' => 2000,
    'serviceType' => 'standard',
];

try {
    $rate = DeshCourier::estimateRate('pathao', $rateData);
    echo "✓ Rate estimated with array\n";
    echo "  Delivery Charge: BDT " . number_format($rate->deliveryCharge, 2) . "\n";
    echo "  Total Charge: BDT " . number_format($rate->totalCharge, 2) . "\n\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "5. Estimating Rate with DTO Object\n";

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
    echo "  Currency: " . $result->currency . "\n\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "6. Laravel Request Integration Example\n";
echo "// In your Laravel controller:\n";
echo "public function createShipment(Request \$request)\n";
echo "{\n";
echo "    \$validated = \$request->validate([\n";
echo "        'recipientName' => 'required|string',\n";
echo "        'recipientPhone' => 'required|string',\n";
echo "    ]);\n\n";
echo "    \$result = DeshCourier::createShipment('pathao', \$validated);\n\n";
echo "    return response()->json([\n";
echo "        'tracking_id' => \$result->trackingId,\n";
echo "        'status' => \$result->status,\n";
echo "    ]);\n";
echo "}\n\n";

echo "7. Using fill() method for updates\n";

$shipment = new Shipment();
$shipment->recipientName = 'Initial Name';

$shipment->fill([
    'recipientName' => 'Updated Name',
    'recipientPhone' => '01799999999',
    'weight' => 3.0,
]);

echo "✓ Properties updated using fill()\n";
echo "  Recipient: " . $shipment->recipientName . "\n";
echo "  Phone: " . $shipment->recipientPhone . "\n";
echo "  Weight: " . $shipment->weight . " kg\n\n";

echo "8. Default Values in DTOs\n";

$shipment = new Shipment([
    'recipientName' => 'Test User',
    'recipientPhone' => '01712345678',
    'recipientAddress' => 'Test Address',
    'recipientCity' => 'Dhaka',
    'senderName' => 'Store',
    'senderPhone' => '01787654321',
    'senderAddress' => 'Store Address',
    'senderCity' => 'Dhaka',
]);

echo "✓ Default values applied automatically\n";
echo "  Service Type: " . ($shipment->serviceType ?? 'not set') . "\n";
echo "  Quantity: " . ($shipment->quantity ?? 'not set') . "\n\n";

echo "=== Examples Complete ===\n";
