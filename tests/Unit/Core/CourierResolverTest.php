<?php

namespace Millat\DeshCourier\Tests\Unit\Core;

use Millat\DeshCourier\Core\CourierResolver;
use Millat\DeshCourier\Core\CourierRegistry;
use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Tests\TestCase;

class CourierResolverTest extends TestCase
{
    private CourierResolver $resolver;
    private CourierRegistry $registry;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new CourierRegistry();
        $this->resolver = new CourierResolver($this->registry);
    }
    
    /**
     * Test resolving courier by name.
     */
    public function testResolve(): void
    {
        $courier = $this->createMockCourier('test-courier');
        $this->registry->register($courier);
        
        $resolved = $this->resolver->resolve('test-courier');
        
        $this->assertEquals($courier, $resolved);
    }
    
    /**
     * Test finding couriers by capabilities.
     */
    public function testFindByCapabilities(): void
    {
        $courier1 = $this->createMockCourierWithCapabilities('courier1', ['shipment.create', 'tracking.realtime']);
        $courier2 = $this->createMockCourierWithCapabilities('courier2', ['tracking.realtime']);
        $courier3 = $this->createMockCourierWithCapabilities('courier3', ['rate.estimation']);
        
        $this->registry->register($courier1);
        $this->registry->register($courier2);
        $this->registry->register($courier3);
        
        $matches = $this->resolver->findByCapabilities(['shipment.create', 'tracking.realtime']);
        
        $this->assertCount(1, $matches);
        $this->assertArrayHasKey('courier1', $matches);
    }
    
    /**
     * Test getting default courier.
     */
    public function testGetDefault(): void
    {
        $courier1 = $this->createMockCourier('courier1');
        $courier2 = $this->createMockCourier('courier2');
        
        $this->registry->register($courier1);
        $this->registry->register($courier2);
        
        $default = $this->resolver->getDefault();
        
        $this->assertNotNull($default);
        $this->assertEquals('courier1', $default->getName());
    }
    
    /**
     * Test getting default when no couriers registered.
     */
    public function testGetDefaultWhenEmpty(): void
    {
        $default = $this->resolver->getDefault();
        
        $this->assertNull($default);
    }
    
    /**
     * Test getting all couriers.
     */
    public function testGetAll(): void
    {
        $courier1 = $this->createMockCourier('courier1');
        $courier2 = $this->createMockCourier('courier2');
        
        $this->registry->register($courier1);
        $this->registry->register($courier2);
        
        $all = $this->resolver->getAll();
        
        $this->assertCount(2, $all);
        $this->assertArrayHasKey('courier1', $all);
        $this->assertArrayHasKey('courier2', $all);
    }
    
    /**
     * Create a mock courier.
     */
    private function createMockCourier(string $name): CourierInterface
    {
        $courier = $this->createMock(CourierInterface::class);
        $courier->method('getName')->willReturn($name);
        $courier->method('getDisplayName')->willReturn(ucfirst($name));
        $courier->method('capabilities')->willReturn([]);
        $courier->method('supports')->willReturn(false);
        $courier->method('testConnection')->willReturn(true);
        
        return $courier;
    }
    
    /**
     * Create a mock courier with specific capabilities.
     */
    private function createMockCourierWithCapabilities(string $name, array $capabilities): CourierInterface
    {
        $courier = $this->createMock(CourierInterface::class);
        $courier->method('getName')->willReturn($name);
        $courier->method('getDisplayName')->willReturn(ucfirst($name));
        $courier->method('capabilities')->willReturn($capabilities);
        $courier->method('supports')->willReturnCallback(function ($capability) use ($capabilities) {
            return in_array($capability, $capabilities);
        });
        $courier->method('testConnection')->willReturn(true);
        
        return $courier;
    }
}
