# Guide: Adding a New Courier Driver

This guide explains how to add a new courier driver to the Desh Courier SDK **without modifying any core code**.

---

## Step-by-Step Guide

### Step 1: Create Driver Directory

Create a new directory for your courier driver:

```
src/Drivers/YourCourier/
```

### Step 2: Create Configuration Class

Create `src/Drivers/YourCourier/YourCourierConfig.php`:

```php
<?php

namespace Millat\DeshCourier\Drivers\YourCourier;

class YourCourierConfig
{
    private string $apiKey;
    private string $apiUrl;
    private ?string $environment = null;
    
    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiUrl = $config['api_url'] ?? 'https://api.yourcourier.com';
        $this->environment = $config['environment'] ?? 'production';
    }
    
    public function getApiKey(): string
    {
        return $this->apiKey;
    }
    
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }
}
```

### Step 3: Create Mapper Class

Create `src/Drivers/YourCourier/YourCourierMapper.php`:

```php
<?php

namespace Millat\DeshCourier\Drivers\YourCourier;

use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\DTO\Rate;
use Millat\DeshCourier\DTO\Cod;
use Millat\DeshCourier\Core\StatusMapper;

class YourCourierMapper
{
    /**
     * Map Shipment to your courier's API format.
     */
    public function mapShipmentToApi(Shipment $shipment): array
    {
        return [
            'customer_name' => $shipment->recipientName,
            'customer_phone' => $shipment->recipientPhone,
            'customer_address' => $shipment->recipientAddress,
            'customer_city' => $shipment->recipientCity,
            'weight' => $shipment->weight,
            'cod_amount' => $shipment->codAmount,
            // Map other fields as needed
        ];
    }
    
    /**
     * Map your courier's API response to Shipment.
     */
    public function mapApiToShipment(array $apiData, ?Shipment $existing = null): Shipment
    {
        $shipment = $existing ?? new Shipment();
        
        $shipment->trackingId = $apiData['tracking_number'] ?? null;
        $shipment->courierName = 'yourcourier';
        $shipment->status = StatusMapper::map($apiData['status'] ?? '', $this->getStatusMapping());
        $shipment->courierStatus = $apiData['status'] ?? null;
        
        return $shipment;
    }
    
    /**
     * Map API response to Tracking.
     */
    public function mapApiToTracking(array $apiData): Tracking
    {
        $tracking = new Tracking();
        
        $tracking->trackingId = $apiData['tracking_number'] ?? null;
        $tracking->courierName = 'yourcourier';
        $tracking->status = StatusMapper::map($apiData['status'] ?? '', $this->getStatusMapping());
        $tracking->courierStatus = $apiData['status'] ?? null;
        $tracking->currentLocation = $apiData['current_location'] ?? null;
        
        if (isset($apiData['delivered_at'])) {
            $tracking->deliveredAt = new \DateTimeImmutable($apiData['delivered_at']);
        }
        
        return $tracking;
    }
    
    /**
     * Map Rate to API format.
     */
    public function mapRateToApi(Rate $rate): array
    {
        return [
            'from_city' => $rate->fromCity,
            'to_city' => $rate->toCity,
            'weight' => $rate->weight,
            'cod_amount' => $rate->codAmount,
        ];
    }
    
    /**
     * Map API response to Rate.
     */
    public function mapApiToRate(array $apiData, Rate $existing): Rate
    {
        $rate = $existing;
        $rate->deliveryCharge = $apiData['delivery_charge'] ?? null;
        $rate->codCharge = $apiData['cod_charge'] ?? null;
        $rate->totalCharge = ($rate->deliveryCharge ?? 0) + ($rate->codCharge ?? 0);
        $rate->courierName = 'yourcourier';
        
        return $rate;
    }
    
    /**
     * Map API response to Cod.
     */
    public function mapApiToCod(array $apiData): Cod
    {
        $cod = new Cod();
        $cod->trackingId = $apiData['tracking_number'] ?? null;
        $cod->courierName = 'yourcourier';
        $cod->codAmount = $apiData['cod_amount'] ?? null;
        $cod->codCollected = $apiData['cod_collected'] ?? null;
        
        return $cod;
    }
    
    /**
     * Get courier-specific status mapping.
     */
    private function getStatusMapping(): array
    {
        return [
            'Pending' => StatusMapper::CREATED,
            'Picked' => StatusMapper::PICKED,
            'In Transit' => StatusMapper::IN_TRANSIT,
            'Out for Delivery' => StatusMapper::OUT_FOR_DELIVERY,
            'Delivered' => StatusMapper::DELIVERED,
            'Returned' => StatusMapper::RETURNED,
            'Cancelled' => StatusMapper::CANCELLED,
        ];
    }
}
```

### Step 4: Create Courier Driver Class

Create `src/Drivers/YourCourier/YourCourier.php`:

```php
<?php

namespace Millat\DeshCourier\Drivers\YourCourier;

use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Contracts\ShipmentInterface;
use Millat\DeshCourier\Contracts\TrackingInterface;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\Support\HttpClient;
use Millat\DeshCourier\Exceptions\ApiException;

class YourCourier implements CourierInterface, ShipmentInterface, TrackingInterface
{
    private YourCourierConfig $config;
    private HttpClient $httpClient;
    private YourCourierMapper $mapper;
    
    public function __construct(YourCourierConfig $config, ?HttpClient $httpClient = null)
    {
        $this->config = $config;
        $this->httpClient = $httpClient ?? new HttpClient();
        $this->mapper = new YourCourierMapper();
    }
    
    // ==================== CourierInterface ====================
    
    public function getName(): string
    {
        return 'yourcourier';
    }
    
    public function getDisplayName(): string
    {
        return 'Your Courier';
    }
    
    public function capabilities(): array
    {
        return [
            'shipment.create',
            'tracking.realtime',
            // Add capabilities you support
        ];
    }
    
    public function supports(string $capability): bool
    {
        return in_array($capability, $this->capabilities());
    }
    
    public function testConnection(): bool
    {
        try {
            // Test API connection
            $response = $this->httpClient->get(
                $this->config->getApiUrl() . '/health',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->config->getApiKey(),
                    ],
                ]
            );
            return $response['status'] === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    // ==================== ShipmentInterface ====================
    
    public function createShipment(Shipment $shipment): Shipment
    {
        $payload = $this->mapper->mapShipmentToApi($shipment);
        
        $response = $this->httpClient->post(
            $this->config->getApiUrl() . '/shipments',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config->getApiKey(),
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]
        );
        
        if ($response['status'] !== 200 && $response['status'] !== 201) {
            throw new ApiException(
                $response['data']['message'] ?? 'Failed to create shipment',
                $response['status'],
                null,
                'yourcourier',
                $response['status'],
                $response['data']
            );
        }
        
        return $this->mapper->mapApiToShipment($response['data'], $shipment);
    }
    
    public function updateShipment(string $trackingId, Shipment $shipment): Shipment
    {
        // Implement if supported
        throw new \RuntimeException('Update shipment not supported');
    }
    
    public function cancelShipment(string $trackingId, ?string $reason = null): bool
    {
        $response = $this->httpClient->delete(
            $this->config->getApiUrl() . '/shipments/' . $trackingId,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config->getApiKey(),
                ],
            ]
        );
        
        return $response['status'] === 200 || $response['status'] === 204;
    }
    
    public function createBulkShipments(array $shipments): array
    {
        // Implement if supported, or throw exception
        throw new \RuntimeException('Bulk shipments not supported');
    }
    
    public function generateLabel(string $trackingId, string $format = 'pdf'): string
    {
        // Implement if supported
        throw new \RuntimeException('Label generation not supported');
    }
    
    public function requestPickup(string $trackingId, array $pickupDetails = []): bool
    {
        // Implement if supported
        throw new \RuntimeException('Pickup request not supported');
    }
    
    // ==================== TrackingInterface ====================
    
    public function track(string $trackingId): Tracking
    {
        $response = $this->httpClient->get(
            $this->config->getApiUrl() . '/shipments/' . $trackingId . '/track',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config->getApiKey(),
                ],
            ]
        );
        
        if ($response['status'] !== 200) {
            throw new ApiException(
                'Failed to track shipment',
                $response['status'],
                null,
                'yourcourier',
                $response['status'],
                $response['data']
            );
        }
        
        return $this->mapper->mapApiToTracking($response['data']);
    }
    
    public function trackBulk(array $trackingIds): array
    {
        // Implement if supported
        throw new \RuntimeException('Bulk tracking not supported');
    }
    
    public function getStatus(string $trackingId): string
    {
        $tracking = $this->track($trackingId);
        return $tracking->status ?? StatusMapper::CREATED;
    }
}
```

### Step 5: Register Your Courier

In your application code or service provider:

```php
use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\Drivers\YourCourier\YourCourier;
use Millat\DeshCourier\Drivers\YourCourier\YourCourierConfig;

// Register factory
DeshCourier::registerFactory('yourcourier', function () {
    $config = new YourCourierConfig([
        'api_key' => env('YOURCOURIER_API_KEY'),
        'api_url' => env('YOURCOURIER_API_URL', 'https://api.yourcourier.com'),
    ]);
    return new YourCourier($config);
});
```

Or register directly:

```php
$config = new YourCourierConfig([/* ... */]);
$courier = new YourCourier($config);
DeshCourier::register($courier);
```

### Step 6: Use Your Courier

```php
use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\DTO\Shipment;

$shipment = new Shipment();
// ... set shipment data

$result = DeshCourier::createShipment('yourcourier', $shipment);
$tracking = DeshCourier::track('yourcourier', $result->trackingId);
```

---

## Best Practices

### 1. Implement Only What You Support

Only implement interfaces for features your courier actually supports. The SDK will gracefully handle missing capabilities.

### 2. Use StatusMapper

Always use `StatusMapper::map()` to normalize statuses. Provide a custom mapping array for courier-specific statuses.

### 3. Handle Errors Gracefully

Throw appropriate exceptions (`ApiException`, `UnsupportedCapabilityException`) with context.

### 4. Keep Mapper Separate

Keep all data transformation logic in the mapper class. This makes testing and maintenance easier.

### 5. Document Capabilities

Clearly document what your courier supports in the `capabilities()` method.

---

## Testing Your Driver

```php
use Millat\DeshCourier\DeshCourier;

// Test connection
$courier = DeshCourier::use('yourcourier');
if ($courier->testConnection()) {
    echo "Connection successful!\n";
}

// Test capabilities
$capabilities = $courier->capabilities();
print_r($capabilities);

// Test shipment creation
$shipment = new \Millat\DeshCourier\DTO\Shipment();
// ... set data
$result = DeshCourier::createShipment('yourcourier', $shipment);
echo "Tracking ID: " . $result->trackingId . "\n";
```

---

## Example: Minimal Driver

Here's a minimal example that only supports shipment creation:

```php
<?php

namespace Millat\DeshCourier\Drivers\MinimalCourier;

use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Contracts\ShipmentInterface;
use Millat\DeshCourier\DTO\Shipment;

class MinimalCourier implements CourierInterface, ShipmentInterface
{
    public function getName(): string { return 'minimal'; }
    public function getDisplayName(): string { return 'Minimal Courier'; }
    public function capabilities(): array { return ['shipment.create']; }
    public function supports(string $capability): bool { return $capability === 'shipment.create'; }
    public function testConnection(): bool { return true; }
    
    public function createShipment(Shipment $shipment): Shipment
    {
        // Your implementation
        $shipment->trackingId = 'TRACK' . time();
        return $shipment;
    }
    
    // Other methods throw exceptions or return defaults
}
```

---

## Contributing Your Driver

If you'd like to contribute your driver to the main repository:

1. Follow the structure above
2. Add comprehensive tests
3. Update documentation
4. Submit a pull request

---

**That's it!** You've added a new courier without modifying any core code. 🎉
