# Architecture Documentation

## Overview

The Desh Courier SDK is built on **clean architecture principles** with a focus on **extensibility**, **maintainability**, and **zero breaking changes** when adding new couriers.

---

## Core Principles

### 1. Adapter Pattern

Each courier is implemented as an **adapter** that translates between:
- The SDK's unified interface (DTOs, normalized statuses)
- The courier's specific API format

This allows the SDK to work with any courier without knowing their internal implementation.

### 2. Interface Segregation

Interfaces are split by capability:
- `CourierInterface` - Base interface (required)
- `ShipmentInterface` - Shipment operations
- `TrackingInterface` - Tracking operations
- `RateInterface` - Rate estimation
- `CodInterface` - COD operations
- `WebhookInterface` - Webhook support
- `MetadataInterface` - Area/city metadata

Couriers only implement interfaces they support, allowing graceful degradation.

### 3. Dependency Inversion

The core SDK depends on abstractions (interfaces), not concrete implementations. This means:
- New couriers can be added without modifying core code
- Couriers can be swapped at runtime
- Testing is easier (mock interfaces)

### 4. Single Responsibility

Each class has a single, well-defined responsibility:
- `CourierManager` - Orchestrates courier operations
- `CourierRegistry` - Manages courier instances
- `CourierResolver` - Resolves courier selection
- `StatusMapper` - Normalizes statuses
- `CapabilityDetector` - Detects capabilities

---

## Architecture Layers

```
┌─────────────────────────────────────────┐
│         Application Layer               │
│  (Laravel, WordPress, Plain PHP)       │
└─────────────────┬───────────────────────┘
                  │
┌─────────────────▼───────────────────────┐
│         Facade Layer                    │
│         DeshCourier.php                 │
└─────────────────┬───────────────────────┘
                  │
┌─────────────────▼───────────────────────┐
│         Core Layer                      │
│  - CourierManager                       │
│  - CourierRegistry                      │
│  - CourierResolver                      │
│  - StatusMapper                         │
│  - CapabilityDetector                   │
└─────────────────┬───────────────────────┘
                  │
┌─────────────────▼───────────────────────┐
│         Contract Layer                  │
│  - CourierInterface                     │
│  - ShipmentInterface                    │
│  - TrackingInterface                    │
│  - RateInterface                        │
│  - CodInterface                         │
│  - WebhookInterface                     │
└─────────────────┬───────────────────────┘
                  │
┌─────────────────▼───────────────────────┐
│         Driver Layer                    │
│  - PathaoCourier                        │
│  - SteadfastCourier                    │
│  - RedXCourier                         │
│  - ...                                  │
└─────────────────┬───────────────────────┘
                  │
┌─────────────────▼───────────────────────┐
│         Support Layer                   │
│  - HttpClient                           │
│  - ConfigHelper                         │
│  - Logger                               │
└─────────────────────────────────────────┘
```

---

## Data Flow

### Creating a Shipment

```
1. Application calls: DeshCourier::createShipment('pathao', $shipmentDTO)
   │
2. DeshCourier facade delegates to CourierManager
   │
3. CourierManager resolves courier via CourierResolver
   │
4. CourierResolver gets courier from CourierRegistry
   │
5. CourierManager checks if courier implements ShipmentInterface
   │
6. PathaoCourier receives Shipment
   │
7. PathaoMapper transforms Shipment → Pathao API format
   │
8. HttpClient makes API request
   │
9. PathaoMapper transforms API response → Shipment
   │
10. Shipment returned to application
```

### Tracking a Shipment

```
1. Application calls: DeshCourier::track('pathao', 'TRACK123')
   │
2. PathaoCourier receives tracking request
   │
3. HttpClient fetches tracking data from API
   │
4. PathaoMapper transforms API response → Tracking
   │
5. StatusMapper normalizes status (Pathao status → Canonical status)
   │
6. Tracking returned to application
```

---

## Key Components

### CourierManager

**Responsibility**: Main orchestrator for courier operations.

**Key Methods**:
- `createShipment()` - Create shipment via courier
- `track()` - Track shipment
- `estimateRate()` - Estimate delivery rate
- `register()` - Register courier driver

**Design**: Uses CourierResolver to get courier instances, then delegates to appropriate interface methods.

### CourierRegistry

**Responsibility**: Manages courier driver instances and factories.

**Key Methods**:
- `register()` - Register courier instance
- `registerFactory()` - Register lazy-loading factory
- `get()` - Get courier by name
- `has()` - Check if courier is registered

**Design**: Supports both direct registration and lazy loading via factories.

### CourierResolver

**Responsibility**: Resolves courier selection based on name or capabilities.

**Key Methods**:
- `resolve()` - Get courier by name
- `findByCapabilities()` - Find couriers supporting specific capabilities
- `getDefault()` - Get default courier

**Design**: Can be extended for intelligent courier selection (pricing, SLA, etc.).

### StatusMapper

**Responsibility**: Normalizes courier-specific statuses to canonical statuses.

**Canonical Statuses**:
- `CREATED` - Shipment created
- `PICKED` - Picked up
- `IN_TRANSIT` - In transit
- `OUT_FOR_DELIVERY` - Out for delivery
- `DELIVERED` - Delivered
- `FAILED` - Delivery failed
- `RETURNED` - Returned
- `CANCELLED` - Cancelled

**Design**: Provides default mapping with support for custom mappings per courier.

### CapabilityDetector

**Responsibility**: Detects and validates courier capabilities.

**Key Methods**:
- `supports()` - Check if courier supports capability
- `getSupported()` - Get all supported capabilities
- `supportsAll()` - Check if courier supports all required capabilities

**Design**: Enables graceful degradation when features aren't available.

---

## Driver Structure

Each courier driver follows this structure:

```
Drivers/
└── YourCourier/
    ├── YourCourier.php      # Main driver class
    ├── YourCourierConfig.php # Configuration
    └── YourCourierMapper.php # Data transformation
```

### YourCourier.php

- Implements relevant interfaces
- Declares capabilities
- Handles authentication
- Delegates to mapper for data transformation

### YourCourierConfig.php

- Stores courier-specific configuration
- Handles environment detection
- Validates required fields

### YourCourierMapper.php

- Transforms DTOs → API format
- Transforms API responses → DTOs
- Maps courier statuses → canonical statuses

---

## Extension Points

### Adding a New Courier

1. Create driver directory
2. Implement interfaces
3. Create mapper
4. Register courier

**No core modification required!**

### Adding a New Capability

1. Create new interface (e.g., `ReturnInterface`)
2. Add capability constant to `CapabilityDetector`
3. Implement interface in drivers that support it
4. Update `CourierManager` with new methods

### Custom Courier Selection

Extend `CourierResolver` to implement intelligent selection:
- Price comparison
- SLA comparison
- Performance scoring
- Route optimization

---

## Error Handling

### Exception Hierarchy

```
CourierException (base)
├── ApiException (API errors)
├── InvalidConfigurationException (config errors)
└── UnsupportedCapabilityException (feature not supported)
```

### Error Flow

1. Driver throws `ApiException` with context
2. Exception bubbles up through manager
3. Application catches and handles appropriately

---

## Configuration System

### Multi-Environment Support

The SDK supports configuration from:
1. Environment variables (`.env`)
2. Laravel config files
3. WordPress constants
4. Runtime injection

### Configuration Priority

1. Runtime injection (highest)
2. Laravel config
3. WordPress constants
4. Environment variables (lowest)

---

## Testing Strategy

### Unit Tests

- Test each component in isolation
- Mock dependencies (HttpClient, etc.)
- Test data transformation (mappers)

### Integration Tests

- Test full flow (create → track)
- Test with real courier APIs (sandbox)
- Test error scenarios

### Driver Tests

- Test driver implementation
- Test capability detection
- Test status normalization

---

## Performance Considerations

### Lazy Loading

Couriers are loaded lazily via factories, reducing initial load time.

### Caching

- Configuration can be cached
- Rate estimates can be cached
- Tracking data can be cached (with TTL)

### Connection Pooling

HttpClient can be configured with connection pooling for better performance.

---

## Security Considerations

### API Keys

- Never log API keys
- Store in secure configuration
- Use environment variables in production

### Webhook Validation

- Validate webhook signatures
- Verify webhook source
- Handle replay attacks

### Data Sanitization

- Sanitize all user input
- Validate DTOs before API calls
- Escape output in templates

---

## Future Enhancements

### Event System

```php
DeshCourier::on('shipment.created', function ($shipment) {
    // Handle event
});
```

### AI-Powered Selection

```php
$bestCourier = DeshCourier::selectBestCourier($shipment, [
    'criteria' => ['price', 'sla', 'performance'],
]);
```

### Route Optimization

```php
$optimizedRoute = DeshCourier::optimizeRoute($shipments);
```

---

## Design Patterns Used

- **Adapter Pattern** - Courier drivers
- **Strategy Pattern** - Different couriers for different strategies
- **Factory Pattern** - Lazy loading of couriers
- **Repository Pattern** - CourierRegistry
- **DTO Pattern** - Data normalization
- **Facade Pattern** - DeshCourier facade

---

## SOLID Principles

- **S**ingle Responsibility - Each class has one job
- **O**pen/Closed - Open for extension, closed for modification
- **L**iskov Substitution - Interfaces can be swapped
- **I**nterface Segregation - Small, focused interfaces
- **D**ependency Inversion - Depend on abstractions

---

This architecture ensures the SDK is:
- ✅ **Extensible** - Easy to add new couriers
- ✅ **Maintainable** - Clear separation of concerns
- ✅ **Testable** - Easy to mock and test
- ✅ **Scalable** - Can handle many couriers
- ✅ **Reliable** - Graceful error handling
