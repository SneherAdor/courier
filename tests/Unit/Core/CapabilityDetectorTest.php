<?php

namespace Millat\DeshCourier\Tests\Unit\Core;

use Millat\DeshCourier\Core\CapabilityDetector;
use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Tests\TestCase;

class CapabilityDetectorTest extends TestCase
{
    /**
     * Test that all known capabilities are defined.
     */
    public function testCapabilitiesAreDefined(): void
    {
        $capabilities = CapabilityDetector::CAPABILITIES;
        
        $this->assertIsArray($capabilities);
        $this->assertContains('shipment.create', $capabilities);
        $this->assertContains('tracking.realtime', $capabilities);
        $this->assertContains('rate.estimation', $capabilities);
        $this->assertContains('cod.settlement', $capabilities);
    }
    
    /**
     * Test supports method.
     */
    public function testSupports(): void
    {
        $courier = $this->createMockCourier(['shipment.create', 'tracking.realtime']);
        
        $this->assertTrue(CapabilityDetector::supports($courier, 'shipment.create'));
        $this->assertTrue(CapabilityDetector::supports($courier, 'tracking.realtime'));
        $this->assertFalse(CapabilityDetector::supports($courier, 'rate.estimation'));
    }
    
    /**
     * Test getSupported method.
     */
    public function testGetSupported(): void
    {
        $capabilities = ['shipment.create', 'tracking.realtime'];
        $courier = $this->createMockCourier($capabilities);
        
        $supported = CapabilityDetector::getSupported($courier);
        
        $this->assertEquals($capabilities, $supported);
    }
    
    /**
     * Test getMissing method.
     */
    public function testGetMissing(): void
    {
        $courier = $this->createMockCourier(['shipment.create']);
        
        $missing = CapabilityDetector::getMissing($courier);
        
        $this->assertContains('tracking.realtime', $missing);
        $this->assertContains('rate.estimation', $missing);
        $this->assertNotContains('shipment.create', $missing);
    }
    
    /**
     * Test supportsAll method.
     */
    public function testSupportsAll(): void
    {
        $courier = $this->createMockCourier(['shipment.create', 'tracking.realtime']);
        
        $this->assertTrue(CapabilityDetector::supportsAll($courier, ['shipment.create']));
        $this->assertTrue(CapabilityDetector::supportsAll($courier, ['shipment.create', 'tracking.realtime']));
        $this->assertFalse(CapabilityDetector::supportsAll($courier, ['shipment.create', 'rate.estimation']));
    }
    
    /**
     * Test supportsAny method.
     */
    public function testSupportsAny(): void
    {
        $courier = $this->createMockCourier(['shipment.create']);
        
        $this->assertTrue(CapabilityDetector::supportsAny($courier, ['shipment.create', 'tracking.realtime']));
        $this->assertTrue(CapabilityDetector::supportsAny($courier, ['tracking.realtime', 'shipment.create']));
        $this->assertFalse(CapabilityDetector::supportsAny($courier, ['tracking.realtime', 'rate.estimation']));
    }
    
    /**
     * Create a mock courier for testing.
     */
    private function createMockCourier(array $capabilities): CourierInterface
    {
        $courier = $this->createMock(CourierInterface::class);
        $courier->method('capabilities')->willReturn($capabilities);
        $courier->method('supports')->willReturnCallback(function ($capability) use ($capabilities) {
            return in_array($capability, $capabilities);
        });
        
        return $courier;
    }
}
