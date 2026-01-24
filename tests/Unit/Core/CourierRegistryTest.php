<?php

namespace Millat\DeshCourier\Tests\Unit\Core;

use Millat\DeshCourier\Core\CourierRegistry;
use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Tests\TestCase;

class CourierRegistryTest extends TestCase
{
    private CourierRegistry $registry;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new CourierRegistry();
    }
    
    /**
     * Test registering a courier.
     */
    public function testRegisterCourier(): void
    {
        $courier = $this->createMockCourier('test-courier');
        
        $this->registry->register($courier);
        
        $this->assertTrue($this->registry->has('test-courier'));
        $this->assertEquals($courier, $this->registry->get('test-courier'));
    }
    
    /**
     * Test registering a courier factory.
     */
    public function testRegisterFactory(): void
    {
        $courier = $this->createMockCourier('factory-courier');
        
        $this->registry->registerFactory('factory-courier', function () use ($courier) {
            return $courier;
        });
        
        $this->assertTrue($this->registry->has('factory-courier'));
        $this->assertEquals($courier, $this->registry->get('factory-courier'));
    }
    
    /**
     * Test getting non-existent courier throws exception.
     */
    public function testGetNonExistentCourierThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Courier 'non-existent' is not registered.");
        
        $this->registry->get('non-existent');
    }
    
    /**
     * Test has method returns false for non-existent courier.
     */
    public function testHasReturnsFalseForNonExistentCourier(): void
    {
        $this->assertFalse($this->registry->has('non-existent'));
    }
    
    /**
     * Test getting all registered courier names.
     */
    public function testGetRegisteredNames(): void
    {
        $this->registry->register($this->createMockCourier('courier1'));
        $this->registry->registerFactory('courier2', fn() => $this->createMockCourier('courier2'));
        
        $names = $this->registry->getRegisteredNames();
        
        $this->assertContains('courier1', $names);
        $this->assertContains('courier2', $names);
        $this->assertCount(2, $names);
    }
    
    /**
     * Test getting all couriers instantiates factories.
     */
    public function testAllInstantiatesFactories(): void
    {
        $courier = $this->createMockCourier('lazy-courier');
        $called = false;
        
        $this->registry->registerFactory('lazy-courier', function () use ($courier, &$called) {
            $called = true;
            return $courier;
        });
        
        $this->assertFalse($called);
        
        $all = $this->registry->all();
        
        $this->assertTrue($called);
        $this->assertArrayHasKey('lazy-courier', $all);
        $this->assertEquals($courier, $all['lazy-courier']);
    }
    
    /**
     * Test unregistering a courier.
     */
    public function testUnregister(): void
    {
        $courier = $this->createMockCourier('unregister-test');
        
        $this->registry->register($courier);
        $this->assertTrue($this->registry->has('unregister-test'));
        
        $this->registry->unregister('unregister-test');
        $this->assertFalse($this->registry->has('unregister-test'));
    }
    
    /**
     * Test clearing all couriers.
     */
    public function testClear(): void
    {
        $this->registry->register($this->createMockCourier('courier1'));
        $this->registry->registerFactory('courier2', fn() => $this->createMockCourier('courier2'));
        
        $this->registry->clear();
        
        $this->assertFalse($this->registry->has('courier1'));
        $this->assertFalse($this->registry->has('courier2'));
        $this->assertEmpty($this->registry->getRegisteredNames());
    }
    
    /**
     * Create a mock courier for testing.
     */
    private function createMockCourier(string $name): CourierInterface
    {
        $courier = $this->createMock(CourierInterface::class);
        $courier->method('getName')->willReturn($name);
        $courier->method('getDisplayName')->willReturn(ucfirst($name));
        $courier->method('capabilities')->willReturn(['shipment.create']);
        $courier->method('supports')->willReturn(true);
        $courier->method('testConnection')->willReturn(true);
        
        return $courier;
    }
}
