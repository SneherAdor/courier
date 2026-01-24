# Usage Guide

## Table of Contents

1. [Plain PHP Usage](#plain-php-usage)
2. [Laravel Usage](#laravel-usage)
3. [WordPress Usage](#wordpress-usage)
4. [Advanced Usage](#advanced-usage)
5. [Webhook Handling](#webhook-handling)

---

## Plain PHP Usage

### Basic Setup

```php
<?php

require 'vendor/autoload.php';

use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\DTO\Rate;
```

### Create Shipment

```php
// Create shipment DTO
$shipment = new Shipment();
$shipment->recipientName = 'John Doe';
$shipment->recipientPhone = '01712345678';
$shipment->recipientAddress = 'House 123, Road 45, Gulshan-2';
$shipment->recipientCity = 'Dhaka';
$shipment->recipientZone = 'Gulshan';
$shipment->recipientPostalCode = '1212';

$shipment->senderName = 'Your Store Name';
$shipment->senderPhone = '01787654321';
$shipment->senderAddress = 'Shop 456, Market Street';
$shipment->senderCity = 'Dhaka';
$shipment->senderZone = 'Dhanmondi';

$shipment->weight = 1.5; // kg
$shipment->quantity = 1;
$shipment->itemDescription = 'T-Shirt';
$shipment->itemValue = 1500;
$shipment->codAmount = 1500;
$shipment->serviceType = 'next_day'; // same_day, next_day, standard
$shipment->externalOrderId = 'ORDER-12345';
$shipment->orderSource = 'website'; // facebook, website, pos, api
$shipment->deliveryInstruction = 'Call before delivery';

// Create shipment
try {
    $result = DeshCourier::createShipment('pathao', $shipment);
    
    echo "Tracking ID: " . $result->trackingId . "\n";
    echo "Status: " . $result->status . "\n";
    echo "Label URL: " . $result->labelUrl . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Track Shipment

```php
try {
    $tracking = DeshCourier::track('pathao', 'TRACK123');
    
    echo "Status: " . $tracking->status . "\n";
    echo "Current Location: " . $tracking->currentLocation . "\n";
    
    if ($tracking->isDelivered()) {
        echo "Delivered to: " . $tracking->deliveredTo . "\n";
        echo "Delivered at: " . $tracking->deliveredAt->format('Y-m-d H:i:s') . "\n";
    }
    
    // View history
    if ($tracking->history) {
        foreach ($tracking->history as $event) {
            echo $event['timestamp']->format('Y-m-d H:i:s') . " - " . $event['status'] . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Estimate Rate

```php
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
    
    echo "Delivery Charge: BDT " . $estimate->deliveryCharge . "\n";
    echo "COD Charge: BDT " . $estimate->codCharge . "\n";
    echo "Total Charge: BDT " . $estimate->totalCharge . "\n";
    echo "Estimated Days: " . $estimate->estimatedDays . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Bulk Operations

```php
$courier = DeshCourier::use('pathao');

if ($courier instanceof \Millat\DeshCourier\Contracts\ShipmentInterface) {
    // Bulk create
    $shipments = [
        Shipment::fromArray([/* ... */]),
        Shipment::fromArray([/* ... */]),
    ];
    
    $results = $courier->createBulkShipments($shipments);
    
    // Bulk track
    if ($courier instanceof \Millat\DeshCourier\Contracts\TrackingInterface) {
        $trackings = $courier->trackBulk(['TRACK1', 'TRACK2', 'TRACK3']);
        foreach ($trackings as $trackingId => $tracking) {
            echo "$trackingId: " . $tracking->status . "\n";
        }
    }
}
```

---

## Laravel Usage

### Installation

```bash
composer require millat/desh-courier
```

### Configuration

Create `config/desh-courier.php`:

```php
<?php

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

Add to `.env`:

```env
PATHAO_CLIENT_ID=your_client_id
PATHAO_CLIENT_SECRET=your_client_secret
PATHAO_USERNAME=your_username
PATHAO_PASSWORD=your_password
PATHAO_ENV=production
```

### Service Provider (Optional)

Create `app/Providers/DeshCourierServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Millat\DeshCourier\DeshCourier;

class DeshCourierServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register couriers
    }
    
    public function boot()
    {
        // Boot logic
    }
}
```

### Controller Example

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\DTO\Shipment;

class ShipmentController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'recipient_name' => 'required|string',
            'recipient_phone' => 'required|string',
            'recipient_address' => 'required|string',
            'recipient_city' => 'required|string',
            'weight' => 'required|numeric',
            'cod_amount' => 'nullable|numeric',
        ]);
        
        $shipment = Shipment::fromArray($validated);
        $shipment->senderName = config('app.name');
        $shipment->senderPhone = config('app.phone');
        $shipment->serviceType = 'next_day';
        
        try {
            $result = DeshCourier::createShipment('pathao', $shipment);
            
            return response()->json([
                'success' => true,
                'tracking_id' => $result->trackingId,
                'status' => $result->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
    
    public function track($trackingId)
    {
        try {
            $tracking = DeshCourier::track('pathao', $trackingId);
            
            return response()->json([
                'success' => true,
                'tracking' => $tracking->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
```

### Model Integration

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Millat\DeshCourier\DeshCourier;

class Order extends Model
{
    public function createShipment()
    {
        $shipment = new \Millat\DeshCourier\DTO\Shipment();
        $shipment->recipientName = $this->customer_name;
        $shipment->recipientPhone = $this->customer_phone;
        $shipment->recipientAddress = $this->shipping_address;
        $shipment->recipientCity = $this->shipping_city;
        $shipment->weight = $this->weight;
        $shipment->codAmount = $this->total;
        $shipment->externalOrderId = $this->id;
        
        $result = DeshCourier::createShipment('pathao', $shipment);
        
        $this->update([
            'tracking_id' => $result->trackingId,
            'courier_name' => 'pathao',
        ]);
        
        return $result;
    }
    
    public function trackShipment()
    {
        if (!$this->tracking_id) {
            return null;
        }
        
        return DeshCourier::track($this->courier_name, $this->tracking_id);
    }
}
```

---

## WordPress Usage

### Plugin Setup

```php
<?php
/**
 * Plugin Name: Desh Courier Integration
 * Description: Integrate Desh Courier SDK with WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load Composer autoloader
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// Configure
define('DESH_COURIER_CONFIG', [
    'pathao' => [
        'client_id' => get_option('pathao_client_id'),
        'client_secret' => get_option('pathao_client_secret'),
        'username' => get_option('pathao_username'),
        'password' => get_option('pathao_password'),
    ],
]);

// Admin settings page
add_action('admin_menu', function () {
    add_options_page(
        'Desh Courier Settings',
        'Desh Courier',
        'manage_options',
        'desh-courier',
        'desh_courier_settings_page'
    );
});

function desh_courier_settings_page() {
    // Settings form
    ?>
    <div class="wrap">
        <h1>Desh Courier Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('desh_courier_settings'); ?>
            <table class="form-table">
                <tr>
                    <th>Pathao Client ID</th>
                    <td><input type="text" name="pathao_client_id" value="<?php echo esc_attr(get_option('pathao_client_id')); ?>" /></td>
                </tr>
                <tr>
                    <th>Pathao Client Secret</th>
                    <td><input type="password" name="pathao_client_secret" value="<?php echo esc_attr(get_option('pathao_client_secret')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
```

### WooCommerce Integration

```php
// Add tracking to order
add_action('woocommerce_order_details_after_order_table', function ($order) {
    $trackingId = get_post_meta($order->get_id(), '_tracking_id', true);
    
    if ($trackingId) {
        try {
            $tracking = \Millat\DeshCourier\DeshCourier::track('pathao', $trackingId);
            
            echo '<h3>Shipment Tracking</h3>';
            echo '<p>Status: ' . esc_html($tracking->status) . '</p>';
            echo '<p>Current Location: ' . esc_html($tracking->currentLocation) . '</p>';
        } catch (\Exception $e) {
            echo '<p>Error tracking shipment: ' . esc_html($e->getMessage()) . '</p>';
        }
    }
});

// Create shipment on order completion
add_action('woocommerce_order_status_processing', function ($order_id) {
    $order = wc_get_order($order_id);
    
    $shipment = new \Millat\DeshCourier\DTO\Shipment();
    $shipment->recipientName = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
    $shipment->recipientPhone = $order->get_billing_phone();
    $shipment->recipientAddress = $order->get_shipping_address_1();
    $shipment->recipientCity = $order->get_shipping_city();
    $shipment->senderName = get_bloginfo('name');
    $shipment->weight = 1.0; // Calculate from order items
    $shipment->codAmount = $order->get_total();
    $shipment->externalOrderId = (string) $order_id;
    
    try {
        $result = \Millat\DeshCourier\DeshCourier::createShipment('pathao', $shipment);
        update_post_meta($order_id, '_tracking_id', $result->trackingId);
        update_post_meta($order_id, '_courier_name', 'pathao');
    } catch (\Exception $e) {
        error_log('Failed to create shipment: ' . $e->getMessage());
    }
});
```

---

## Advanced Usage

### Custom Courier Registration

```php
use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\Drivers\CustomCourier\CustomCourier;
use Millat\DeshCourier\Drivers\CustomCourier\CustomCourierConfig;

// Register custom courier
DeshCourier::registerFactory('custom', function () {
    $config = new CustomCourierConfig([
        'api_key' => 'your_api_key',
        'api_url' => 'https://api.customcourier.com',
    ]);
    return new CustomCourier($config);
});

// Use it
$result = DeshCourier::createShipment('custom', $shipment);
```

### Capability Checking

```php
$courier = DeshCourier::use('pathao');

// Check if courier supports a feature
if ($courier->supports('shipment.create')) {
    // Create shipment
}

if ($courier->supports('tracking.webhook')) {
    // Register webhook
}

// Get all capabilities
$capabilities = $courier->capabilities();
```

### Find Couriers by Capability

```php
$manager = DeshCourier::manager();
$couriers = $manager->findCouriersByCapabilities([
    'shipment.create',
    'cod.settlement',
]);

foreach ($couriers as $name => $courier) {
    echo "Courier: $name\n";
}
```

---

## Webhook Handling

### Laravel Webhook Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Millat\DeshCourier\DeshCourier;

class WebhookController extends Controller
{
    public function handle(Request $request, $courierName)
    {
        $courier = DeshCourier::use($courierName);
        
        if (!$courier instanceof \Millat\DeshCourier\Contracts\WebhookInterface) {
            return response()->json(['error' => 'Webhooks not supported'], 400);
        }
        
        $payload = $request->all();
        $tracking = $courier->parseWebhook($payload);
        
        if ($tracking) {
            // Update your database
            \App\Models\Order::where('tracking_id', $tracking->trackingId)
                ->update([
                    'status' => $tracking->status,
                    'tracking_data' => json_encode($tracking->toArray()),
                ]);
            
            // Fire event
            event(new \App\Events\ShipmentStatusUpdated($tracking));
        }
        
        return response()->json(['success' => true]);
    }
}
```

### WordPress Webhook Handler

```php
// Register webhook endpoint
add_action('rest_api_init', function () {
    register_rest_route('desh-courier/v1', '/webhook/(?P<courier>[a-zA-Z0-9-]+)', [
        'methods' => 'POST',
        'callback' => 'desh_courier_webhook_handler',
        'permission_callback' => '__return_true',
    ]);
});

function desh_courier_webhook_handler($request) {
    $courierName = $request->get_param('courier');
    $payload = $request->get_json_params();
    
    $courier = \Millat\DeshCourier\DeshCourier::use($courierName);
    
    if ($courier instanceof \Millat\DeshCourier\Contracts\WebhookInterface) {
        $tracking = $courier->parseWebhook($payload);
        
        if ($tracking) {
            // Update order
            $orders = get_posts([
                'post_type' => 'shop_order',
                'meta_key' => '_tracking_id',
                'meta_value' => $tracking->trackingId,
            ]);
            
            foreach ($orders as $order) {
                update_post_meta($order->ID, '_tracking_status', $tracking->status);
            }
        }
    }
    
    return new \WP_REST_Response(['success' => true], 200);
}
```

---

## Error Handling

```php
use Millat\DeshCourier\Exceptions\ApiException;
use Millat\DeshCourier\Exceptions\UnsupportedCapabilityException;

try {
    $result = DeshCourier::createShipment('pathao', $shipment);
} catch (ApiException $e) {
    // API error
    echo "API Error: " . $e->getMessage();
    echo "Status Code: " . $e->getStatusCode();
    echo "Response: " . json_encode($e->getApiResponse());
} catch (UnsupportedCapabilityException $e) {
    // Feature not supported
    echo "Feature not supported: " . $e->getMessage();
} catch (\Exception $e) {
    // Other errors
    echo "Error: " . $e->getMessage();
}
```

---

For more examples, see the [examples/](examples/) directory.
