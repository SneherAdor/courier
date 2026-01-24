<?php

namespace Millat\DeshCourier\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case for all tests.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Tear down the test environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
    
    /**
     * Get a fixture file path.
     */
    protected function getFixturePath(string $filename): string
    {
        return __DIR__ . '/Fixtures/' . $filename;
    }
    
    /**
     * Load a JSON fixture file.
     */
    protected function loadJsonFixture(string $filename): array
    {
        $path = $this->getFixturePath($filename);
        
        if (!file_exists($path)) {
            $this->fail("Fixture file not found: {$path}");
        }
        
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail("Invalid JSON in fixture: {$filename}. Error: " . json_last_error_msg());
        }
        
        return $data;
    }
    
    /**
     * Assert that an array has the required keys.
     */
    protected function assertArrayHasKeys(array $keys, array $array, string $message = ''): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, $message ?: "Array should have key '{$key}'");
        }
    }
    
    /**
     * Assert that a DTO has required properties.
     */
    protected function assertDtoHasProperties(array $properties, object $dto, string $message = ''): void
    {
        foreach ($properties as $property) {
            $this->assertTrue(
                property_exists($dto, $property),
                $message ?: "DTO should have property '{$property}'"
            );
        }
    }
}
