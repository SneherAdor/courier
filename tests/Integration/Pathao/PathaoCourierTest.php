<?php

namespace Millat\DeshCourier\Tests\Integration\Pathao;

use Millat\DeshCourier\Drivers\Pathao\PathaoCourier;
use Millat\DeshCourier\Drivers\Pathao\PathaoConfig;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\Tests\TestCase;

/**
 * Integration tests for Pathao courier driver.
 * 
 * These tests require valid Pathao sandbox credentials.
 * Set environment variables or update credentials in setUp().
 */
class PathaoCourierTest extends TestCase
{
    private ?PathaoCourier $courier = null;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip tests if credentials are not available
        if (!$this->hasCredentials()) {
            $this->markTestSkipped('Pathao credentials not available. Set PATHAO_CLIENT_ID, PATHAO_CLIENT_SECRET, PATHAO_USERNAME, PATHAO_PASSWORD environment variables.');
        }
        
        $config = new PathaoConfig([
            'client_id' => getenv('PATHAO_CLIENT_ID') ?: '7N1aMJQbWm',
            'client_secret' => getenv('PATHAO_CLIENT_SECRET') ?: 'wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39',
            'username' => getenv('PATHAO_USERNAME') ?: 'test@pathao.com',
            'password' => getenv('PATHAO_PASSWORD') ?: 'lovePathao',
            'environment' => 'sandbox',
        ]);
        
        $this->courier = new PathaoCourier($config);
    }
    
    /**
     * Test courier name and display name.
     */
    public function testCourierName(): void
    {
        $this->assertEquals('pathao', $this->courier->getName());
        $this->assertEquals('Pathao Courier', $this->courier->getDisplayName());
    }
    
    /**
     * Test courier capabilities.
     */
    public function testCapabilities(): void
    {
        $capabilities = $this->courier->capabilities();
        
        $this->assertIsArray($capabilities);
        $this->assertContains('shipment.create', $capabilities);
        $this->assertContains('tracking.realtime', $capabilities);
        $this->assertTrue($this->courier->supports('shipment.create'));
        $this->assertTrue($this->courier->supports('tracking.realtime'));
    }
    
    /**
     * Test connection/authentication.
     */
    public function testConnection(): void
    {
        $result = $this->courier->testConnection();
        
        $this->assertTrue($result, 'Pathao connection test should succeed');
    }
    
    /**
     * Test creating a shipment (requires valid store_id).
     */
    public function testCreateShipment(): void
    {
        $this->markTestSkipped('Requires valid store_id. Create a store first using Pathao API.');
        
        $shipment = new Shipment();
        $shipment->recipientName = 'Test Recipient';
        $shipment->recipientPhone = '01712345678';
        $shipment->recipientAddress = 'House 123, Road 45, Gulshan-2, Dhaka-1212';
        $shipment->recipientCity = 'Dhaka';
        $shipment->senderName = 'Test Store';
        $shipment->senderPhone = '01787654321';
        $shipment->senderAddress = 'Shop 456, Market Street';
        $shipment->senderCity = 'Dhaka';
        $shipment->weight = 1.0;
        $shipment->codAmount = 1000;
        $shipment->serviceType = 'standard';
        $shipment->courierData = ['store_id' => 12345]; // Replace with actual store_id
        
        $result = $this->courier->createShipment($shipment);
        
        $this->assertNotNull($result->trackingId);
        $this->assertEquals('pathao', $result->courierName);
    }
    
    /**
     * Check if credentials are available.
     */
    private function hasCredentials(): bool
    {
        return !empty(getenv('PATHAO_CLIENT_ID')) || !empty('7N1aMJQbWm');
    }
}
