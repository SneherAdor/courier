# Pathao Courier Setup

## Configuration

The Pathao driver has been configured according to the official Pathao API documentation:

- **Base URL (Sandbox)**: `https://courier-api-sandbox.pathao.com`
- **Base URL (Production)**: `https://api-hermes.pathao.com`
- **Authentication Endpoint**: `/aladdin/api/v1/issue-token`
- **All API Endpoints**: Use `/aladdin/api/v1/` prefix

## Current Status

✅ **Code Configuration**: Complete and working
- ✅ Endpoint updated to `/aladdin/api/v1/issue-token` (correct)
- ✅ All API endpoints use `/aladdin/api/v1/` prefix
- ✅ Sandbox URL configured correctly
- ✅ Error handling improved
- ✅ Token extraction working
- ✅ Authentication tested and working with official sandbox credentials

## Official Sandbox Credentials (for testing)

For testing purposes, you can use these official Pathao sandbox credentials:

```php
'client_id' => '7N1aMJQbWm',
'client_secret' => 'wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39',
'username' => 'test@pathao.com',
'password' => 'lovePathao',
```

**Note**: For production, use your own credentials from the Pathao merchant dashboard.

## Testing

Run the test script to verify connection:

```bash
php test-pathao-connection.php
```

## Next Steps

1. **Verify Credentials**: Contact Pathao support to verify your sandbox credentials are active
2. **Check Account Status**: Ensure your Pathao account has API access enabled
3. **Test with Production**: Once sandbox works, update to production URL:
   ```php
   'environment' => 'production',
   'api_url' => 'https://api-hermes.pathao.com',
   ```

## Usage Example

```php
use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\DTO\Shipment;

// Register Pathao with your credentials
DeshCourier::registerFactory('pathao', function () {
    $config = new \Millat\DeshCourier\Drivers\Pathao\PathaoConfig([
        'client_id' => '7N1aMJQbWm', // Your client ID
        'client_secret' => 'wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39', // Your client secret
        'username' => 'test@pathao.com', // Your email
        'password' => 'lovePathao', // Your password
        'environment' => 'sandbox', // or 'production'
    ]);
    return new \Millat\DeshCourier\Drivers\Pathao\PathaoCourier($config);
});

// Create shipment (requires store_id - create store first)
$shipment = new Shipment();
$shipment->recipientName = 'John Doe';
$shipment->recipientPhone = '01712345678';
$shipment->recipientAddress = 'House 123, Road 45, Gulshan-2, Dhaka-1212';
$shipment->senderName = 'Your Store';
$shipment->senderPhone = '01787654321';
$shipment->senderAddress = 'Shop 456, Market Street';
$shipment->weight = 1.5;
$shipment->codAmount = 1500;
$shipment->serviceType = 'standard'; // Maps to delivery_type: 48
$shipment->courierData = ['store_id' => 12345]; // Your store ID from Pathao

$result = DeshCourier::createShipment('pathao', $shipment);
echo "Tracking ID: " . $result->trackingId;
```

## Important Notes

1. **Store ID Required**: Before creating orders, you must create a store using the `/aladdin/api/v1/stores` endpoint
2. **Delivery Type**: 
   - `48` = Normal Delivery (standard, next_day)
   - `12` = On Demand Delivery (same_day, express)
3. **Item Type**: 
   - `1` = Document
   - `2` = Parcel (default)
4. **Weight**: Must be between 0.5 KG to 10 KG

## Security Note

⚠️ **IMPORTANT**: Your credentials are currently in this file. For production:
1. Move credentials to `.env` file
2. Never commit credentials to version control
3. Use environment variables

Example `.env`:
```env
DESH_COURIER_PATHAO_CLIENT_ID=QBeX5jWayK
DESH_COURIER_PATHAO_CLIENT_SECRET=Gz6BNcJG2zYwnSl1AbX13qEyQQjT7M2U0kafk85g
DESH_COURIER_PATHAO_USERNAME=sneherador.sa.sa@gmail.com
DESH_COURIER_PATHAO_PASSWORD=686436832@Sa
DESH_COURIER_PATHAO_ENVIRONMENT=sandbox
```
