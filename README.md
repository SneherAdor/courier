# Desh Courier SDK

> **Unified multi-courier SDK for Bangladeshi courier services**

A modular, extensible PHP SDK that aggregates all Bangladeshi courier services into a single, clean interface. Works seamlessly with **plain PHP**, **Laravel**, and **WordPress**.

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)](https://php.net)

---

## 🎯 Features

- ✅ **Multi-Courier Support** - Unified interface for all Bangladeshi couriers
- ✅ **Framework Agnostic** - Works with plain PHP, Laravel, and WordPress
- ✅ **Zero Breaking Changes** - Add new couriers without modifying core code
- ✅ **Capability Detection** - Gracefully handles missing features
- ✅ **Status Normalization** - Consistent statuses across all couriers
- ✅ **Clean Architecture** - SOLID principles, adapter pattern, DTOs
- ✅ **Extensible** - Easy to add new courier drivers

---

## 📦 Installation

```bash
composer require millat/desh-courier
```

---

## 🚀 Quick Start

### Plain PHP

```php
<?php

require 'vendor/autoload.php';

use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\DTO\Shipment;

// Create a shipment
$shipment = new Shipment();
$shipment->recipientName = 'John Doe';
$shipment->recipientPhone = '01712345678';
$shipment->recipientAddress = 'House 123, Road 45';
$shipment->recipientCity = 'Dhaka';
$shipment->recipientZone = 'Gulshan';
$shipment->senderName = 'Your Store';
$shipment->senderPhone = '01787654321';
$shipment->senderAddress = 'Shop 456, Market Street';
$shipment->senderCity = 'Dhaka';
$shipment->weight = 1.5;
$shipment->codAmount = 1500;
$shipment->serviceType = 'next_day';

// Create shipment via Pathao
$result = DeshCourier::createShipment('pathao', $shipment);

echo "Tracking ID: " . $result->trackingId . "\n";

// Track shipment
$tracking = DeshCourier::track('pathao', $result->trackingId);
echo "Status: " . $tracking->status . "\n";
```

### Laravel

```php
<?php

// config/desh-courier.php
return [
    'pathao' => [
        'client_id' => env('PATHAO_CLIENT_ID'),
        'client_secret' => env('PATHAO_CLIENT_SECRET'),
        'username' => env('PATHAO_USERNAME'),
        'password' => env('PATHAO_PASSWORD'),
        'environment' => env('PATHAO_ENV', 'production'),
    ],
];

// Usage in Controller
use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\DTO\Shipment;

$shipment = Shipment::fromArray($request->validated());
$result = DeshCourier::createShipment('pathao', $shipment);
```

### WordPress

```php
<?php

// In functions.php or plugin
define('DESH_COURIER_CONFIG', [
    'pathao' => [
        'client_id' => get_option('pathao_client_id'),
        'client_secret' => get_option('pathao_client_secret'),
        'username' => get_option('pathao_username'),
        'password' => get_option('pathao_password'),
    ],
]);

// Usage
use Millat\DeshCourier\DeshCourier;

$shipment = new \Millat\DeshCourier\DTO\Shipment();
// ... set shipment data
$result = DeshCourier::createShipment('pathao', $shipment);
```

---

## 📚 Core Concepts

### 1. Courier Drivers

Each courier (Pathao, Steadfast, RedX, etc.) is implemented as a **driver** that implements relevant interfaces.

### 2. Interfaces

- `CourierInterface` - Base interface (required)
- `ShipmentInterface` - Shipment operations
- `TrackingInterface` - Tracking operations
- `RateInterface` - Rate estimation
- `CodInterface` - COD operations
- `WebhookInterface` - Webhook support
- `MetadataInterface` - Area/city metadata

### 3. DTOs (Data Transfer Objects)

All data is normalized through DTOs:
- `Shipment` - Shipment information
- `Tracking` - Tracking information
- `Rate` - Rate estimation
- `Cod` - COD information

### 4. Status Normalization

All couriers map to canonical statuses:
- `CREATED` - Shipment created
- `PICKED` - Picked up
- `IN_TRANSIT` - In transit
- `OUT_FOR_DELIVERY` - Out for delivery
- `DELIVERED` - Delivered
- `FAILED` - Delivery failed
- `RETURNED` - Returned
- `CANCELLED` - Cancelled

---

## 🔌 Usage Examples

### Create Shipment

```php
use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\DTO\Shipment;

$shipment = new Shipment();
$shipment->recipientName = 'John Doe';
$shipment->recipientPhone = '01712345678';
$shipment->recipientAddress = 'House 123, Road 45';
$shipment->recipientCity = 'Dhaka';
$shipment->recipientZone = 'Gulshan';
$shipment->senderName = 'Your Store';
$shipment->senderPhone = '01787654321';
$shipment->senderAddress = 'Shop 456';
$shipment->senderCity = 'Dhaka';
$shipment->weight = 1.5;
$shipment->codAmount = 1500;
$shipment->serviceType = 'next_day';
$shipment->externalOrderId = 'ORDER-12345';

$result = DeshCourier::createShipment('pathao', $shipment);
echo $result->trackingId;
```

### Track Shipment

```php
$tracking = DeshCourier::track('pathao', 'TRACK123');
echo $tracking->status; // DELIVERED
echo $tracking->currentLocation;
echo $tracking->deliveredAt->format('Y-m-d H:i:s');
```

### Estimate Rate

```php
use Millat\DeshCourier\DTO\Rate;

$rate = new Rate();
$rate->fromCity = 'Dhaka';
$rate->toCity = 'Chittagong';
$rate->weight = 2.0;
$rate->codAmount = 2000;
$rate->serviceType = 'standard';

$estimate = DeshCourier::estimateRate('pathao', $rate);
echo "Delivery Charge: " . $estimate->deliveryCharge;
echo "COD Charge: " . $estimate->codCharge;
echo "Total: " . $estimate->totalCharge;
```

### Get COD Details

```php
$courier = DeshCourier::use('pathao');
if ($courier instanceof \Millat\DeshCourier\Contracts\CodInterface) {
    $cod = $courier->getCodDetails('TRACK123');
    echo "COD Amount: " . $cod->codAmount;
    echo "Collected: " . $cod->codCollected;
    echo "Pending: " . $cod->codPending;
}
```

### Check Capabilities

```php
$courier = DeshCourier::use('pathao');
$capabilities = $courier->capabilities();

if ($courier->supports('shipment.create')) {
    // Create shipment
}

if ($courier->supports('tracking.webhook')) {
    // Register webhook
}
```

---

## 🔧 Configuration

### Environment Variables (.env)

```env
DESH_COURIER_PATHAO_CLIENT_ID=your_client_id
DESH_COURIER_PATHAO_CLIENT_SECRET=your_client_secret
DESH_COURIER_PATHAO_USERNAME=your_username
DESH_COURIER_PATHAO_PASSWORD=your_password
DESH_COURIER_PATHAO_ENVIRONMENT=production
```

### Laravel Config

```php
// config/desh-courier.php
return [
    'pathao' => [
        'client_id' => env('PATHAO_CLIENT_ID'),
        'client_secret' => env('PATHAO_CLIENT_SECRET'),
        'username' => env('PATHAO_USERNAME'),
        'password' => env('PATHAO_PASSWORD'),
        'environment' => env('PATHAO_ENV', 'production'),
    ],
];
```

### WordPress Constants

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

## 🏗️ Architecture

### Directory Structure

```
src/
├── Contracts/          # Interfaces
├── Core/              # Core management classes
├── DTO/               # Data Transfer Objects
├── Drivers/           # Courier driver implementations
│   ├── Pathao/
│   ├── Steadfast/
│   └── ...
├── Exceptions/        # Exception classes
├── Support/           # Helper classes
└── DeshCourier.php    # Main facade
```

### Design Patterns

- **Adapter Pattern** - Each courier is an adapter
- **Strategy Pattern** - Different couriers for different strategies
- **Factory Pattern** - Lazy loading of courier instances
- **DTO Pattern** - Data normalization

---

## ➕ Adding a New Courier

To add a new courier driver:

1. **Create driver directory**: `src/Drivers/YourCourier/`

2. **Create courier class** implementing interfaces:

```php
<?php

namespace Millat\DeshCourier\Drivers\YourCourier;

use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Contracts\ShipmentInterface;
// ... other interfaces

class YourCourier implements CourierInterface, ShipmentInterface
{
    public function getName(): string
    {
        return 'yourcourier';
    }
    
    public function capabilities(): array
    {
        return ['shipment.create', 'tracking.realtime'];
    }
    
    // Implement other methods...
}
```

3. **Create mapper class** for data transformation:

```php
class YourCourierMapper
{
    public function mapShipmentToApi(Shipment $shipment): array
    {
        // Transform to your courier's API format
    }
    
    public function mapApiToShipment(array $apiData): Shipment
    {
        // Transform from your courier's API format
    }
}
```

4. **Register the courier**:

```php
DeshCourier::registerFactory('yourcourier', function () {
    $config = new YourCourierConfig(/* ... */);
    return new YourCourier($config);
});
```

**That's it!** No core modification needed.

---

## 📊 Courier Capability Matrix

| Courier | Shipment | Tracking | Rate | COD | Webhook |
|---------|----------|----------|------|-----|---------|
| Pathao  | ✅       | ✅       | ✅   | ✅  | ✅      |
| Steadfast | 🔄     | 🔄       | 🔄   | 🔄  | 🔄      |
| RedX    | 🔄       | 🔄       | 🔄   | 🔄  | 🔄      |

✅ = Implemented  
🔄 = In Progress

---

## 🧪 Testing

```bash
composer test
```

---

## 📝 License

MIT License - see [LICENSE](LICENSE) file.

---

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/millat/desh-courier/issues)
- **Email**: dev@deshcourier.com

---

## 🎯 Roadmap

- [ ] Steadfast courier driver
- [ ] RedX courier driver
- [ ] Paperfly courier driver
- [ ] Event system for SaaS usage
- [ ] AI-powered courier selection
- [ ] Route optimization
- [ ] Courier performance scoring

---

**Built with ❤️ for the Bangladeshi e-commerce ecosystem**
