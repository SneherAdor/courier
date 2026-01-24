# Testing Guide

## Overview

Desh Courier SDK includes a comprehensive test suite following industry best practices, similar to professional SDKs and packages like Laravel, Guzzle, and Stripe.

## Test Structure

```
tests/
├── Unit/                    # Unit tests (45 tests)
│   ├── Core/               # Core component tests
│   │   ├── StatusMapperTest.php
│   │   ├── CourierRegistryTest.php
│   │   ├── CourierResolverTest.php
│   │   └── CapabilityDetectorTest.php
│   ├── DTO/                # Data Transfer Object tests
│   │   ├── ShipmentTest.php
│   │   └── TrackingTest.php
│   ├── Drivers/            # Driver-specific unit tests
│   │   └── Pathao/
│   │       └── PathaoMapperTest.php
│   └── Exceptions/         # Exception tests
│       └── ApiExceptionTest.php
├── Integration/            # Integration tests
│   └── Pathao/
│       └── PathaoCourierTest.php
├── Feature/                # Feature/end-to-end tests
│   ├── CourierManagerTest.php
│   └── DeshCourierFacadeTest.php
└── Fixtures/               # Test data fixtures
    ├── pathao-auth-response.json
    ├── pathao-order-response.json
    └── pathao-tracking-response.json
```

## Running Tests

### All Tests
```bash
composer test
```

### By Test Suite
```bash
composer test-unit          # Unit tests only (fast)
composer test-integration   # Integration tests (requires API credentials)
composer test-feature       # Feature tests
```

### Specific Test File
```bash
vendor/bin/phpunit tests/Unit/Core/StatusMapperTest.php
```

### With Coverage
```bash
composer test-coverage
# Open coverage/index.html in browser
```

## Test Coverage

### Current Coverage
- **Unit Tests**: 45 tests covering core functionality
- **Integration Tests**: Pathao API integration
- **Feature Tests**: End-to-end workflows

### Coverage Goals
- **Unit Tests**: 80%+ code coverage
- **Integration Tests**: All major API endpoints
- **Feature Tests**: Main user workflows

## Test Types

### Unit Tests
- **Purpose**: Test individual components in isolation
- **Speed**: Fast (< 1 second)
- **Dependencies**: Mocked
- **Examples**:
  - Status mapping
  - DTO validation
  - Registry operations
  - Capability detection

### Integration Tests
- **Purpose**: Test real API interactions
- **Speed**: Slower (requires network)
- **Dependencies**: Real API (sandbox)
- **Requirements**: API credentials
- **Examples**:
  - Pathao authentication
  - Shipment creation
  - Tracking retrieval

### Feature Tests
- **Purpose**: Test complete workflows
- **Speed**: Medium
- **Dependencies**: Mocked couriers
- **Examples**:
  - Courier registration
  - Shipment creation via facade
  - Capability-based courier selection

## Writing Tests

### Test Naming Convention
```php
public function testMethodNameWithExpectedBehavior(): void
{
    // Test implementation
}
```

### Arrange-Act-Assert Pattern
```php
public function testCreateShipment(): void
{
    // Arrange
    $courier = $this->createMockCourier();
    $shipment = new Shipment();
    
    // Act
    $result = $courier->createShipment($shipment);
    
    // Assert
    $this->assertNotNull($result->trackingId);
}
```

### Using Fixtures
```php
public function testWithFixture(): void
{
    $fixture = $this->loadJsonFixture('pathao-order-response.json');
    // Use fixture data
}
```

## Mocking

### Mocking Interfaces
```php
$courier = $this->createMock(CourierInterface::class);
$courier->method('getName')->willReturn('test-courier');
```

### Mocking Multiple Interfaces
```php
$courier = $this->createMock([
    CourierInterface::class,
    ShipmentInterface::class
]);
```

## Best Practices

1. **One assertion per test** (when possible)
2. **Descriptive test names** that explain what is tested
3. **Test edge cases** and error conditions
4. **Use fixtures** for complex data structures
5. **Mock external dependencies** in unit tests
6. **Skip integration tests** if credentials unavailable
7. **Keep tests fast** - unit tests should run in milliseconds
8. **Test behavior, not implementation**

## Continuous Integration

Tests run automatically on:
- Push to `main` or `develop` branches
- Pull requests
- Multiple PHP versions (8.1, 8.2, 8.3)

See `.github/workflows/tests.yml` for CI configuration.

## Environment Variables

For integration tests, set:
```bash
export PATHAO_CLIENT_ID=your_client_id
export PATHAO_CLIENT_SECRET=your_client_secret
export PATHAO_USERNAME=your_username
export PATHAO_PASSWORD=your_password
```

## Test Results

### Current Status
```
Tests: 45
Assertions: 165
Status: ✅ All passing
```

### Running Tests Locally
```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run with verbose output
vendor/bin/phpunit --verbose

# Run specific test
vendor/bin/phpunit tests/Unit/Core/StatusMapperTest.php
```

## Debugging Tests

### Verbose Output
```bash
vendor/bin/phpunit --verbose
```

### Stop on Failure
```bash
vendor/bin/phpunit --stop-on-failure
```

### Filter Tests
```bash
vendor/bin/phpunit --filter StatusMapper
```

## Test Maintenance

- **Update tests** when adding new features
- **Keep fixtures** up-to-date with API changes
- **Review test coverage** regularly
- **Refactor tests** when code changes
- **Document** complex test scenarios

## Examples

See existing tests for examples:
- `tests/Unit/Core/StatusMapperTest.php` - Status mapping tests
- `tests/Unit/DTO/ShipmentTest.php` - DTO validation tests
- `tests/Integration/Pathao/PathaoCourierTest.php` - API integration tests
- `tests/Feature/CourierManagerTest.php` - Feature workflow tests
