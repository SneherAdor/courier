# Test Suite

This directory contains the test suite for Desh Courier SDK.

## Structure

```
tests/
├── Unit/              # Unit tests for individual components
│   ├── Core/         # Core classes (StatusMapper, CourierRegistry, etc.)
│   ├── DTO/          # Data Transfer Object tests
│   └── Drivers/      # Driver-specific unit tests
├── Integration/      # Integration tests (require API access)
│   └── Pathao/       # Pathao integration tests
├── Feature/          # Feature tests (end-to-end scenarios)
└── Fixtures/         # Test data fixtures
```

## Running Tests

### Run all tests
```bash
composer test
```

### Run specific test suite
```bash
composer test-unit          # Unit tests only
composer test-integration   # Integration tests only
composer test-feature       # Feature tests only
```

### Run specific test file
```bash
vendor/bin/phpunit tests/Unit/Core/StatusMapperTest.php
```

### Run with coverage
```bash
composer test-coverage
```

## Writing Tests

### Unit Tests

Unit tests should:
- Test individual components in isolation
- Use mocks for dependencies
- Be fast and deterministic
- Not require external services

Example:
```php
public function testStatusMapping(): void
{
    $result = StatusMapper::map('delivered');
    $this->assertEquals(StatusMapper::DELIVERED, $result);
}
```

### Integration Tests

Integration tests should:
- Test real API interactions
- Use sandbox/test credentials
- Be marked as skipped if credentials unavailable
- Test actual API responses

Example:
```php
public function testCreateShipment(): void
{
    if (!$this->hasCredentials()) {
        $this->markTestSkipped('Credentials not available');
    }
    
    $result = $this->courier->createShipment($shipment);
    $this->assertNotNull($result->trackingId);
}
```

### Feature Tests

Feature tests should:
- Test complete workflows
- Use the facade/manager
- Test real-world scenarios
- Verify end-to-end functionality

## Test Fixtures

Fixtures are stored in `tests/Fixtures/` and contain sample API responses for testing.

## Environment Variables

For integration tests, set these environment variables:

```bash
export PATHAO_CLIENT_ID=your_client_id
export PATHAO_CLIENT_SECRET=your_client_secret
export PATHAO_USERNAME=your_username
export PATHAO_PASSWORD=your_password
```

Or create a `.env.testing` file (not committed to git).

## Best Practices

1. **One assertion per test** (when possible)
2. **Descriptive test names** that explain what is being tested
3. **Arrange-Act-Assert** pattern
4. **Use fixtures** for complex data structures
5. **Mock external dependencies** in unit tests
6. **Skip integration tests** if credentials unavailable
7. **Test edge cases** and error conditions
8. **Keep tests fast** - unit tests should run in milliseconds

## Coverage Goals

- **Unit tests**: 80%+ coverage
- **Integration tests**: Cover all major API endpoints
- **Feature tests**: Cover main user workflows

## Continuous Integration

Tests run automatically on:
- Push to main/develop branches
- Pull requests
- Multiple PHP versions (8.1, 8.2, 8.3)

See `.github/workflows/tests.yml` for CI configuration.
