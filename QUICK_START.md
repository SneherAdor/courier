# Quick Start Guide

Get up and running with Desh Courier SDK in 5 minutes!

---

## Installation

```bash
composer require millat/desh-courier
```

---

## Configuration

### Option 1: Environment Variables (.env)

```env
DESH_COURIER_PATHAO_CLIENT_ID=your_client_id
DESH_COURIER_PATHAO_CLIENT_SECRET=your_client_secret
DESH_COURIER_PATHAO_USERNAME=your_username
DESH_COURIER_PATHAO_PASSWORD=your_password
```

### Option 2: Laravel Config

```php
// config/desh-courier.php
return [
    'pathao' => [
        'client_id' => env('PATHAO_CLIENT_ID'),
        'client_secret' => env('PATHAO_CLIENT_SECRET'),
        'username' => env('PATHAO_USERNAME'),
        'password' => env('PATHAO_PASSWORD'),
    ],
];
```

### Option 3: WordPress Constants

```php
define('DESH_COURIER_CONFIG', [
    'pathao' => [
        'client_id' => '...',
        'client_secret' => '...',
        // ...
    ],
]);
```

---

## Basic Usage

### Create a Shipment

```php
use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\DTO\Shipment;

$shipment = new Shipment();
$shipment->recipientName = 'John Doe';
$shipment->recipientPhone = '01712345678';
$shipment->recipientAddress = 'House 123, Road 45';
$shipment->recipientCity = 'Dhaka';
$shipment->senderName = 'Your Store';
$shipment->senderPhone = '01787654321';
$shipment->senderAddress = 'Shop 456';
$shipment->senderCity = 'Dhaka';
$shipment->weight = 1.5;
$shipment->codAmount = 1500;

$result = DeshCourier::createShipment('pathao', $shipment);
echo "Tracking ID: " . $result->trackingId;
```

### Track a Shipment

```php
$tracking = DeshCourier::track('pathao', 'TRACK123');
echo "Status: " . $tracking->status;
```

### Estimate Rate

```php
use Millat\DeshCourier\DTO\Rate;

$rate = new Rate();
$rate->fromCity = 'Dhaka';
$rate->toCity = 'Chittagong';
$rate->weight = 2.0;
$rate->codAmount = 2000;

$estimate = DeshCourier::estimateRate('pathao', $rate);
echo "Total: BDT " . $estimate->totalCharge;
```

---

## Next Steps

- 📖 Read [README.md](README.md) for full documentation
- 💡 See [USAGE.md](USAGE.md) for detailed examples
- 🏗️ Check [ARCHITECTURE.md](ARCHITECTURE.md) to understand the design
- ➕ Learn how to [add new couriers](ADDING_COURIER.md)

---

**That's it!** You're ready to start using Desh Courier SDK. 🚀
